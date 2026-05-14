<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $latestOrders = Order::query()->latest()->take(8)->get();

        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
                'revenue' => (float) Order::query()->where('paid', true)->sum('total'),
            ],
            'latestOrders' => $latestOrders,
        ]);
    }
}
