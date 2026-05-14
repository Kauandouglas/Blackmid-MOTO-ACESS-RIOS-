<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAbandonedCartController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'abandoned');

        $query = AbandonedCart::query()
            ->whereNotNull('customer_email')
            ->latest();

        match ($filter) {
            'converted' => $query->converted(),
            'pending'   => $query->pending(),
            default     => $query->abandoned(),
        };

        $carts = $query->paginate(25)->withQueryString();

        $counts = [
            'abandoned' => AbandonedCart::abandoned()->count(),
            'pending'   => AbandonedCart::pending()->count(),
            'converted' => AbandonedCart::converted()->count(),
        ];

        return view('admin.abandoned-carts.index', [
            'carts'   => $carts,
            'filter'  => $filter,
            'counts'  => $counts,
        ]);
    }

    public function show(AbandonedCart $abandonedCart): View
    {
        return view('admin.abandoned-carts.show', [
            'cart' => $abandonedCart,
        ]);
    }
}
