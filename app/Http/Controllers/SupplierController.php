<?php
// app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_suppliers');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status'));
        }

        $suppliers = $query->withCount('products')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'required|in:cash,credit_7,credit_15,credit_30,credit_60',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($request->all());

        $this->auditService->log('CREATE', 'suppliers', $supplier->id, null, $supplier->toArray());

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('products');
        
        $stats = [
            'total_products' => $supplier->getProductsCount(),
            'active_products' => $supplier->getActiveProductsCount(),
            'total_supplied' => $supplier->getTotalSupplied(),
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'required|in:cash,credit_7,credit_15,credit_30,credit_60',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($request->all());

        $this->auditService->log('UPDATE', 'suppliers', $supplier->id, $oldValues, $supplier->toArray());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has products
        if ($supplier->products()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier with products. Please reassign products first.');
        }

        $oldValues = $supplier->toArray();
        $supplier->delete();

        $this->auditService->log('DELETE', 'suppliers', $supplier->id, $oldValues, null);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $oldValues = $supplier->toArray();
        $supplier->update(['is_active' => !$supplier->is_active]);

        $this->auditService->log(
            $supplier->is_active ? 'ACTIVATE' : 'DEACTIVATE',
            'suppliers',
            $supplier->id,
            $oldValues,
            $supplier->toArray()
        );

        $status = $supplier->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Supplier {$status} successfully");
    }
}