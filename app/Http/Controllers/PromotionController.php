<?php
// app/Http/Controllers/PromotionController.php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_promotions');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Promotion::with('branch');

        // Filter by branch if user is not admin
        if (!auth()->user()->isAdmin() && auth()->user()->branch_id) {
            $query->forBranch(auth()->user()->branch_id);
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('status')) {
            if ($request->get('status') == 'active') {
                $query->active();
            } elseif ($request->get('status') == 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promotions = $query->orderBy('start_date', 'desc')->paginate(15);

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $branches = auth()->user()->isAdmin() 
            ? Branch::active()->get() 
            : collect([auth()->user()->branch]);
            
        $products = Product::active()
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->get();

        return view('promotions.create', compact('branches', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y,bundle',
            'discount_value' => 'required|numeric|min:0',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_usage' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,product_id',
        ]);

        $data = $request->except('products');
        
        // Set branch_id for non-admin users
        if (!auth()->user()->isAdmin()) {
            $data['branch_id'] = auth()->user()->branch_id;
        }

        $promotion = Promotion::create($data);

        // Attach products if provided
        if ($request->has('products')) {
            $promotion->products()->attach($request->products);
        }

        $this->auditService->log('CREATE', 'promotions', $promotion->id, null, $promotion->toArray());

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion created successfully');
    }

    public function show(Promotion $promotion)
    {
        $promotion->load(['branch', 'products']);

        return view('promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        $branches = auth()->user()->isAdmin() 
            ? Branch::active()->get() 
            : collect([auth()->user()->branch]);
            
        $products = Product::active()
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->get();

        $promotion->load('products');

        return view('promotions.edit', compact('promotion', 'branches', 'products'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y,bundle',
            'discount_value' => 'required|numeric|min:0',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_usage' => 'nullable|integer|min:1',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,product_id',
        ]);

        $oldValues = $promotion->toArray();
        $promotion->update($request->except('products'));

        // Sync products if provided
        if ($request->has('products')) {
            $promotion->products()->sync($request->products);
        }

        $this->auditService->log('UPDATE', 'promotions', $promotion->id, $oldValues, $promotion->toArray());

        return redirect()->route('promotions.show', $promotion)
            ->with('success', 'Promotion updated successfully');
    }

    public function destroy(Promotion $promotion)
    {
        $oldValues = $promotion->toArray();
        $promotion->delete();

        $this->auditService->log('DELETE', 'promotions', $promotion->id, $oldValues, null);

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion deleted successfully');
    }

    public function toggleStatus(Promotion $promotion)
    {
        $oldValues = $promotion->toArray();
        $promotion->update(['is_active' => !$promotion->is_active]);

        $this->auditService->log(
            $promotion->is_active ? 'ACTIVATE' : 'DEACTIVATE',
            'promotions',
            $promotion->id,
            $oldValues,
            $promotion->toArray()
        );

        $status = $promotion->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Promotion {$status} successfully");
    }
}