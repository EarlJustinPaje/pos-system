<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Ensure we have some data for display
        $stats = [
            'active_products' => Product::active()->count(),
            'todays_sales' => Sale::whereDate('created_at', today())->count(),
            'near_expiry' => Product::nearExpiring()->count(),
            'expired' => Product::expired()->count(),
        ];
        
        return view('home', compact('stats'));
    }
}