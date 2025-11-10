<?php
// app/Http/Controllers/BranchController.php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_branches');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status'));
        }

        $branches = $query->withCount(['users', 'products', 'sales'])->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'markup_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $branch = Branch::create($request->all());

        $this->auditService->log('CREATE', 'branches', $branch->id, null, $branch->toArray());

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully');
    }

    public function show(Branch $branch)
    {
        $branch->load(['users.role', 'products', 'sales']);
        
        $stats = [
            'total_sales' => $branch->getTotalSales(),
            'monthly_sales' => $branch->getTotalSales(now()->startOfMonth(), now()->endOfMonth()),
            'active_products' => $branch->getActiveProductsCount(),
            'low_stock' => $branch->getLowStockCount(),
            'users_count' => $branch->users()->count(),
        ];

        return view('branches.show', compact('branch', 'stats'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $oldValues = $branch->toArray();
        $branch->update($request->all());

        $this->auditService->log('UPDATE', 'branches', $branch->id, $oldValues, $branch->toArray());

        return redirect()->route('branches.show', $branch)
            ->with('success', 'Branch updated successfully');
    }

    public function destroy(Branch $branch)
    {
        // Check if branch has users or products
        if ($branch->users()->exists()) {
            return redirect()->route('branches.index')
                ->with('error', 'Cannot delete branch with assigned users. Please reassign users first.');
        }

        if ($branch->products()->exists()) {
            return redirect()->route('branches.index')
                ->with('error', 'Cannot delete branch with products. Please reassign products first.');
        }

        $oldValues = $branch->toArray();
        $branch->delete();

        $this->auditService->log('DELETE', 'branches', $branch->id, $oldValues, null);

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully');
    }

    public function toggleStatus(Branch $branch)
    {
        $oldValues = $branch->toArray();
        $branch->update(['is_active' => !$branch->is_active]);

        $this->auditService->log(
            $branch->is_active ? 'ACTIVATE' : 'DEACTIVATE',
            'branches',
            $branch->id,
            $oldValues,
            $branch->toArray()
        );

        $status = $branch->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Branch {$status} successfully");
    }

    public function updateMarkup(Request $request, Branch $branch)
    {
        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $oldValues = $branch->toArray();
        $branch->update(['markup_percentage' => $request->markup_percentage]);

        $this->auditService->log('UPDATE_MARKUP', 'branches', $branch->id, $oldValues, $branch->toArray());

        return redirect()->back()
            ->with('success', 'Markup percentage updated successfully');
    }
}