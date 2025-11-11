<?php
// app/Http/Controllers/POSController.php (Enhanced)

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PaymentMethod;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class POSController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view_pos');
        $this->auditService = $auditService;
    }

    public function index()
    {
        $paymentMethods = PaymentMethod::active()->get();
        
        return view('pos.index', compact('paymentMethods'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::active()
            ->where('quantity', '>', 0)
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('manufacturer', 'like', "%{$query}%");
            })
            ->with(['promotions' => function($q) {
                $q->active();
            }])
            ->limit(10)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'sku' => $product->sku,
                    'manufacturer' => $product->manufacturer,
                    'price' => $product->selling_price,
                    'available_quantity' => $product->quantity,
                    'unit' => $product->unit,
                    'has_promotion' => $product->hasActivePromotion(),
                    'promotion' => $product->getActivePromotions()->first(),
                ];
            });

        return response()->json($products);
    }

    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::byBarcode($request->barcode)
            ->active()
            ->where('quantity', '>', 0)
            ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                $q->forBranch(auth()->user()->branch_id);
            })
            ->with(['promotions' => function($q) {
                $q->active();
            }])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or out of stock'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->product_id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'sku' => $product->sku,
                'manufacturer' => $product->manufacturer,
                'price' => $product->selling_price,
                'available_quantity' => $product->quantity,
                'unit' => $product->unit,
                'has_promotion' => $product->hasActivePromotion(),
                'promotion' => $product->getActivePromotions()->first(),
            ]
        ]);
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;

        $basePrice = $product->selling_price * $quantity;
        $discount = 0;
        $promotion = null;

        // Check for active promotions
        $activePromotion = $product->getActivePromotions()->first();
        if ($activePromotion && $activePromotion->canBeUsed()) {
            $discount = $activePromotion->calculateDiscount($basePrice, $quantity);
            $promotion = [
                'id' => $activePromotion->id,
                'name' => $activePromotion->name,
                'type' => $activePromotion->getTypeLabel(),
                'discount' => $discount,
            ];
        }

        $finalPrice = $basePrice - $discount;

        return response()->json([
            'base_price' => $basePrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'promotion' => $promotion,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payment_methods.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $totalDiscount = 0;
            $saleItems = [];

            // Validate stock and calculate totals
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $unitPrice = $product->selling_price;
                $subtotal = $unitPrice * $item['quantity'];
                $discount = 0;
                $promotionId = null;

                // Apply promotion if available
                $promotion = $product->getActivePromotions()->first();
                if ($promotion && $promotion->canBeUsed()) {
                    $discount = $promotion->calculateDiscount($subtotal, $item['quantity']);
                    $promotionId = $promotion->id;
                    $promotion->incrementUsage();
                }

                $totalPrice = $subtotal - $discount;
                $totalAmount += $subtotal;
                $totalDiscount += $discount;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'total_price' => $totalPrice,
                    'promotion_id' => $promotionId,
                ];
            }

            // Calculate tax (12% VAT)
            $taxAmount = ($totalAmount - $totalDiscount) * 0.12;
            $finalAmount = $totalAmount - $totalDiscount + $taxAmount;

            // Validate payment amounts
            $totalPaid = collect($request->payment_methods)->sum('amount');
            if ($totalPaid < $finalAmount) {
                throw new \Exception("Insufficient payment. Total: ₱" . number_format($finalAmount, 2) . ", Paid: ₱" . number_format($totalPaid, 2));
            }

            $changeAmount = $totalPaid - $finalAmount;

            // Create sale
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'final_amount' => $finalAmount,
                'cash_received' => $totalPaid,
                'change_amount' => $changeAmount,
                'payment_status' => 'completed',
                'is_synced' => true,
            ]);

            // Create sale items and update inventory
            foreach ($saleItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['total_price'],
                    'promotion_id' => $item['promotion_id'],
                ]);

                // Update product inventory
                $item['product']->decrement('quantity', $item['quantity']);
                $item['product']->increment('sold_quantity', $item['quantity']);
            }

            // Create payment transactions
            foreach ($request->payment_methods as $payment) {
                \App\Models\PaymentTransaction::create([
                    'sale_id' => $sale->id,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'reference_number' => $payment['reference_number'] ?? null,
                    'status' => 'completed',
                ]);
            }

            // Log the transaction
            $this->auditService->log('CREATE', 'sales', $sale->id, null, $sale->toArray());

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'total_amount' => $totalAmount,
                'discount' => $totalDiscount,
                'tax' => $taxAmount,
                'final_amount' => $finalAmount,
                'change' => $changeAmount,
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

    public function printReceipt(Sale $sale)
    {
        // Check authorization
        if ($sale->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $sale->load(['saleItems.product', 'paymentTransactions.paymentMethod', 'user', 'branch']);

        return view('pos.receipt', compact('sale'));
    }
}