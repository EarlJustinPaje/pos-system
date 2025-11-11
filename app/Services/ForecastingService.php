<?php
// app/Services/ForecastingService.php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\InventoryForecast;
use App\Models\ReorderAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    // Generates a forecast for a single product
    public function generateForecast(Product $product, int $daysToForecast = 30): void
    {
        // 1. Get historical sales data (e.g., last 90 days)
        $historicalDays = 90;
        $salesData = $this->getHistoricalSalesData($product, $historicalDays);
        $totalSold = $salesData->sum('total_quantity');
        
        // 2. Basic Model: Calculate average daily sales
        // (A more advanced model would use linear regression, moving averages, etc.)
        $avgDailySales = $totalSold > 0 ? ($totalSold / $historicalDays) : 0;
        
        // 3. Predict demand for the next period
        $predictedDemand = (int) ceil($avgDailySales * $daysToForecast);

        // 4. Calculate recommended order quantity
        $stockDeficit = $predictedDemand - $product->quantity;
        $recommendedOrder = 0;
        if ($stockDeficit > 0) {
            // Recommend ordering to meet demand + reorder_quantity buffer
            $recommendedOrder = $stockDeficit + $product->reorder_quantity;
        }

        // 5. Save the forecast
        InventoryForecast::updateOrCreate(
            [
                'product_id' => $product->product_id,
                'branch_id' => $product->branch_id,
                'forecast_date' => today()->addDays($daysToForecast),
            ],
            [
                'predicted_demand' => $predictedDemand,
                'current_stock' => $product->quantity,
                'recommended_order_quantity' => $recommendedOrder,
                'confidence_score' => 30.00, // Low confidence for this basic model
                'historical_data' => $salesData->toArray(),
                'seasonality' => 'none',
                'notes' => 'Forecast based on ' . $historicalDays . '-day historical average.',
            ]
        );
    }

    // Generates reorder alerts for all products that need it
    public function generateReorderAlerts(): void
    {
        $lowStockProducts = Product::active()
            ->lowStock() // Uses the scope: whereColumn('quantity', '<=', 'reorder_point')
            ->get();

        foreach ($lowStockProducts as $product) {
            // Check if a 'pending' or 'acknowledged' alert already exists
            $exists = ReorderAlert::where('product_id', $product->product_id)
                ->where('branch_id', $product->branch_id)
                ->whereIn('status', ['pending', 'acknowledged'])
                ->exists();

            if (!$exists) {
                // Determine priority
                $priority = 'medium';
                if ($product->quantity <= 0) {
                    $priority = 'critical';
                } elseif ($product->quantity <= ($product->reorder_point / 2)) {
                    $priority = 'high';
                }

                ReorderAlert::create([
                    'product_id' => $product->product_id,
                    'branch_id' => $product->branch_id,
                    'current_quantity' => $product->quantity,
                    'reorder_point' => $product->reorder_point,
                    'recommended_quantity' => $product->reorder_quantity,
                    'priority' => $priority,
                    'status' => 'pending',
                ]);
            }
        }
    }

    // Helper to get historical sales
    public function getHistoricalSalesData(Product $product, int $days)
    {
        return SaleItem::where('product_id', $product->product_id)
            ->whereHas('sale', function($q) use ($product) {
                $q->where('branch_id', $product->branch_id);
            })
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'asc')
            ->get();
    }
}