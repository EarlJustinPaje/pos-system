<?php
// app/Http/Controllers/ProductController.php (Enhanced)

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Branch;
use App\Services\AuditService;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $auditService;
    protected $barcodeService;

    public function __construct(AuditService $auditService, BarcodeService $barcodeService)
    {
        $this->middleware('auth');
        $this->auditService = $auditService;
        $this->barcodeService = $barcodeService;
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier', 'branch']);

        // Filter by branch if user is not admin
        if (!auth()->user()->isAdmin() && auth()->user()->branch_id) {
            $query->forBranch(auth()->user()->branch_id);
        }

        // Enhanced search
        if ($request->has('search')) {
            $query->search($request->get('search'));
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->get('category_id'));
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->bySupplier($request->get('supplier_id'));
        }

        // Filter by stock status
        if ($request->has('stock_status')) {
            switch ($request->get('stock_status')) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
            }
        }

        // Filter by expiry
        if ($request->has('expiry_filter')) {
            switch ($request->get('expiry_filter')) {
                case 'expired':
                    $query->expired();
                    break;
                case 'near_expiry':
                    $query->nearExpiring();
                    break;
            }
        }

        $products = $query->active()->paginate(20);

        // Get filter options
        $categories = Category::active()
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->get();
            
        $suppliers = Supplier::active()->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = Category::active()
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->get();
            
        $suppliers = Supplier::active()->get();
        
        $branches = auth()->user()->isAdmin() 
            ? Branch::active()->get() 
            : collect([auth()->user()->branch]);

        return view('products.create', compact('categories', 'suppliers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode',
            'sku' => 'nullable|string|unique:products,sku',
            'branch_id' => 'nullable|exists:branches,id',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'auto_reorder' => 'boolean',
            'unit' => 'required|string',
            'manufacturer' => 'required|string|max:255',
            'capital_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:100',
            'use_custom_markup' => 'boolean',
            'date_procured' => 'required|date',
            'expiration_date' => 'nullable|date|after:date_procured',
            'manufactured_date' => 'required|date',
        ]);

        $data = $request->all();
        
        // Set branch for non-admin users
        if (!auth()->user()->isAdmin()) {
            $data['branch_id'] = auth()->user()->branch_id;
        }

        // Calculate selling price
        if ($request->use_custom_markup && $request->markup_percentage) {
            $data['price'] = $data['capital_price'] * (1 + ($request->markup_percentage / 100));
        } else {
            $branch = Branch::find($data['branch_id']);
            $markup = $branch ? $branch->markup_percentage : 15.00;
            $data['price'] = $data['capital_price'] * (1 + ($markup / 100));
        }

        // Generate barcode if not provided
        if (empty($data['barcode'])) {
            $data['barcode'] = $this->barcodeService->generateUniqueBarcode();
        }

        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->barcodeService->generateUniqueSKU($data['name']);
        }

        $product = Product::create($data);

        // Generate barcode and QR code images
        $this->barcodeService->generateBarcodeImage($product);
        $this->barcodeService->generateQRCode($product);

        $this->auditService->log('CREATE', 'products', $product->product_id, null, $product->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'branch', 'promotions']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->get();
            
        $suppliers = Supplier::active()->get();
        
        $branches = auth()->user()->isAdmin() 
            ? Branch::active()->get() 
            : collect([auth()->user()->branch]);

        return view('products.edit', compact('product', 'categories', 'suppliers', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->product_id . ',product_id',
            'sku' => 'nullable|string|unique:products,sku,' . $product->product_id . ',product_id',
            'branch_id' => 'nullable|exists:branches,id',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'auto_reorder' => 'boolean',
            'unit' => 'required|string',
            'manufacturer' => 'required|string|max:255',
            'capital_price' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0|max:100',
            'use_custom_markup' => 'boolean',
            'date_procured' => 'required|date',
            'expiration_date' => 'nullable|date|after:date_procured',
            'manufactured_date' => 'required|date',
        ]);

        $oldValues = $product->toArray();
        $data = $request->all();

        // Calculate selling price
        if ($request->use_custom_markup && $request->markup_percentage) {
            $data['price'] = $data['capital_price'] * (1 + ($request->markup_percentage / 100));
        } else {
            $branch = Branch::find($data['branch_id']);
            $markup = $branch ? $branch->markup_percentage : 15.00;
            $data['price'] = $data['capital_price'] * (1 + ($markup / 100));
        }

        $product->update($data);

        // Regenerate barcode/QR if barcode changed
        if ($oldValues['barcode'] !== $product->barcode) {
            $this->barcodeService->generateBarcodeImage($product);
            $this->barcodeService->generateQRCode($product);
        }

        $this->auditService->log('UPDATE', 'products', $product->product_id, $oldValues, $product->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $oldValues = $product->toArray();
        
        // Delete barcode and QR code files
        $this->barcodeService->deleteBarcodeFiles($product);
        
        $product->delete();

        $this->auditService->log('DELETE', 'products', $product->product_id, $oldValues, null);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function deactivate(Product $product)
    {
        $oldValues = $product->toArray();
        $product->update(['is_active' => false]);

        $this->auditService->log('DEACTIVATE', 'products', $product->product_id, $oldValues, $product->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Product deactivated successfully');
    }

    public function printBarcode(Product $product)
    {
        return view('products.print-barcode', compact('product'));
    }

    public function printQRCode(Product $product)
    {
        return view('products.print-qrcode', compact('product'));
    }

    public function bulkPrintBarcodes(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,product_id',
        ]);

        $products = Product::whereIn('product_id', $request->product_ids)->get();

        return view('products.bulk-print-barcodes', compact('products'));
    }

    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::byBarcode($request->barcode)
            ->active()
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->product_id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->selling_price,
                'quantity' => $product->quantity,
                'unit' => $product->unit,
                'manufacturer' => $product->manufacturer,
            ]
        ]);
    }
}