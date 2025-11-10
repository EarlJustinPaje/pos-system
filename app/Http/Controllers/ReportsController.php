<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $sales = Sale::with(['user', 'saleItems.product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = $sales->sum('total_amount');

        if ($request->has('download')) {
            $pdf = PDF::loadView('reports.sales-pdf', compact('sales', 'totalSales', 'startDate', 'endDate'));
            return $pdf->download('sales-report.pdf');
        }

        return view('reports.sales', compact('sales', 'totalSales', 'startDate', 'endDate'));
    }

    public function itemSalesReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d H:i:s'));

        $itemSales = SaleItem::with(['product', 'sale'])
            ->whereHas('sale', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->get();

        if ($request->has('download')) {
            $pdf = PDF::loadView('reports.item-sales-pdf', compact('itemSales', 'startDate', 'endDate'));
            return $pdf->download('item-sales-report.pdf');
        }

        return view('reports.item-sales', compact('itemSales', 'startDate', 'endDate'));
    }

    public function inventoryReport()
    {
        $fastMoving = Product::fastMoving()->active()->limit(20)->get();
        $expired = Product::expired()->get();
        $nearExpiring = Product::nearExpiring()->get();
        $allProducts = Product::active()->get();

        return view('reports.inventory', compact('fastMoving', 'expired', 'nearExpiring', 'allProducts'));
    }
}