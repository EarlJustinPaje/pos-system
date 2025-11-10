<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $products = Product::fastMoving()->active()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit' => 'required|string',
            'manufacturer' => 'required|string|max:255',
            'capital_price' => 'required|numeric|min:0',
            'date_procured' => 'required|date',
            'expiration_date' => 'nullable|date|after:date_procured',
            'manufactured_date' => 'required|date',
        ]);

        $data = $request->all();
        $data['price'] = $data['capital_price'] * 1.15; // Set selling price with 15% markup

        $product = Product::create($data);
        
        // Log audit
        $this->logAudit('CREATE', 'products', $product->product_id, null, $product->toArray());
        
        return redirect()->route('products.index')->with('success', 'Product added successfully');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit' => 'required|string',
            'manufacturer' => 'required|string|max:255',
            'capital_price' => 'required|numeric|min:0',
            'date_procured' => 'required|date',
            'expiration_date' => 'nullable|date|after:date_procured',
            'manufactured_date' => 'required|date',
        ]);

        $oldValues = $product->toArray();
        
        $data = $request->all();
        $data['price'] = $data['capital_price'] * 1.15; // Update selling price
        
        $product->update($data);
        
        // Log audit
        $this->logAudit('UPDATE', 'products', $product->product_id, $oldValues, $product->toArray());
        
        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $oldValues = $product->toArray();
        $product->delete();
        
        // Log audit
        $this->logAudit('DELETE', 'products', $product->product_id, $oldValues, null);
        
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    public function deactivate(Product $product)
    {
        $oldValues = $product->toArray();
        $product->update(['is_active' => false]);
        
        // Log audit
        $this->logAudit('DEACTIVATE', 'products', $product->product_id, $oldValues, $product->toArray());
        
        return redirect()->route('products.index')->with('success', 'Product deactivated successfully');
    }

    private function logAudit($action, $tableName, $recordId = null, $oldValues = null, $newValues = null)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}