<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class POSController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->auditService = $auditService;
    }

    public function index()
    {
        return view('pos.index');
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::active()
            ->where('quantity', '>', 0)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('manufacturer', 'like', "%{$query}%");
            })
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'manufacturer' => $product->manufacturer,
                    'price' => $product->selling_price,
                    'available_quantity' => $product->quantity,
                    'unit' => $product->unit,
                ];
            });

        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'cash_received' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $saleItems = [];

            // Validate stock and calculate total
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $unitPrice = $product->selling_price;
                $totalPrice = $unitPrice * $item['quantity'];
                $totalAmount += $totalPrice;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            if ($request->cash_received < $totalAmount) {
                throw new \Exception("Insufficient cash received");
            }

            // Create sale
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'cash_received' => $request->cash_received,
                'change_amount' => $request->cash_received - $totalAmount,
            ]);

            // Create sale items and update inventory
            foreach ($saleItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Update product inventory
                $item['product']->decrement('quantity', $item['quantity']);
                $item['product']->increment('sold_quantity', $item['quantity']);
            }

            // Log the transaction
            $this->auditService->log('CREATE', 'sales', $sale->id, null, $sale->toArray());

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'change' => $sale->change_amount,
                'message' => 'Transaction completed successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}