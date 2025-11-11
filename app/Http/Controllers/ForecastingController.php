<?php
// app/Http/Controllers/ForecastingController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryForecast;
use App\Models\ReorderAlert;
use App\Services\ForecastingService;
use Illuminate\Http\Request;

class ForecastingController extends Controller
{
    protected $forecastingService;

    public function __construct(ForecastingService $forecastingService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view_forecasting');
        $this->forecastingService = $forecastingService;
    }

    public function index(Request $request)
    {
        $query = InventoryForecast::with(['product', 'branch']);

        // Filter by branch if user is not admin
        if (!auth()->user()->isAdmin() && auth()->user()->branch_id) {
            $query->forBranch(auth()->user()->branch_id);
        }

        if ($request->has('days')) {
            $query->upcoming($request->get('days'));
        } else {
            $query->upcoming(30); // Default 30 days
        }

        $forecasts = $query->paginate(20);

        return view('forecasting.index', compact('forecasts'));
    }

    public function show(Product $product)
    {
        $forecasts = $product->inventoryForecasts()
            ->upcoming(90)
            ->get();

        $historicalData = $this->forecastingService->getHistoricalSalesData($product, 90);

        return view('forecasting.show', compact('product', 'forecasts', 'historicalData'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,product_id',
            'days' => 'nullable|integer|min:7|max:365',
        ]);

        $days = $request->get('days', 30);

        if ($request->has('product_id')) {
            $product = Product::findOrFail($request->product_id);
            $this->forecastingService->generateForecast($product, $days);
        } else {
            // Generate for all active products
            $products = Product::active()
                ->when(!auth()->user()->isAdmin() && auth()->user()->branch_id, function($q) {
                    $q->forBranch(auth()->user()->branch_id);
                })
                ->get();

            foreach ($products as $product) {
                $this->forecastingService->generateForecast($product, $days);
            }
        }

        return redirect()->back()
            ->with('success', 'Forecast generated successfully');
    }

    public function reorderAlerts(Request $request)
    {
        $query = ReorderAlert::with(['product', 'branch'])
            ->pending();

        // Filter by branch if user is not admin
        if (!auth()->user()->isAdmin() && auth()->user()->branch_id) {
            $query->forBranch(auth()->user()->branch_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        $alerts = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('forecasting.reorder-alerts', compact('alerts'));
    }

    public function acknowledgeAlert(ReorderAlert $alert)
    {
        $alert->acknowledge(auth()->id());

        return redirect()->back()
            ->with('success', 'Alert acknowledged successfully');
    }

    public function markAlertAsOrdered(ReorderAlert $alert)
    {
        $alert->markAsOrdered();

        return redirect()->back()
            ->with('success', 'Alert marked as ordered');
    }

    public function resolveAlert(ReorderAlert $alert)
    {
        $alert->resolve();

        return redirect()->back()
            ->with('success', 'Alert resolved successfully');
    }

    public function generateReorderAlerts()
    {
        $this->forecastingService->generateReorderAlerts();

        return redirect()->back()
            ->with('success', 'Reorder alerts generated successfully');
    }
}