<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chartData()
    {
        return response()->json([
            'salesChart' => $this->getSalesChartData(),
            'nearExpiry' => $this->getNearExpiryData(),
            'fastMoving' => $this->getFastMovingData(),
            'lowStock' => $this->getLowStockData(),
        ]);
    }

    private function getSalesChartData()
    {
        $salesData = Sale::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->where('created_at', '>=', Carbon::now()->subMonths(5))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];
        
        // Generate last 5 months
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M');
            
            $monthData = $salesData->where('month', $date->month)
                                 ->where('year', $date->year)
                                 ->first();
            
            $data[] = $monthData ? (float) $monthData->total : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getNearExpiryData()
    {
        $nearExpiry = Product::whereNotNull('expiration_date')
            ->where('expiration_date', '>', Carbon::now())
            ->where('expiration_date', '<=', Carbon::now()->addDays(30))
            ->where('is_active', true)
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($nearExpiry as $product) {
            $labels[] = $product->name;
            $daysLeft = Carbon::now()->diffInDays($product->expiration_date);
            $data[] = $daysLeft;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getFastMovingData()
    {
        $fastMoving = Product::where('sold_quantity', '>', 0)
            ->where('is_active', true)
            ->orderBy('sold_quantity', 'desc')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($fastMoving as $product) {
            $labels[] = $product->name;
            $data[] = $product->sold_quantity;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getLowStockData()
    {
        $lowStock = Product::where('quantity', '<=', 20)
            ->where('quantity', '>', 0)
            ->where('is_active', true)
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($lowStock as $product) {
            $labels[] = $product->name;
            $data[] = $product->quantity;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}