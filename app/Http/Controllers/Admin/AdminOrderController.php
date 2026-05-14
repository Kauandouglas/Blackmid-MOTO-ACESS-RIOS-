<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->query('status', ''));

        $orders = Order::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $this->statuses())],
            'paid' => ['nullable', 'boolean'],
        ]);

        $order->update([
            'status' => $data['status'],
            'paid' => $request->boolean('paid'),
        ]);

        return back()->with('success', 'Pedido atualizado com sucesso.');
    }

    private function statuses(): array
    {
        return [
            'received',
            'awaiting_payment',
            'paid',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'payment_cancelled',
        ];
    }
}
