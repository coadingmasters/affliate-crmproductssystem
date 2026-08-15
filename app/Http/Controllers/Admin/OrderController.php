<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * List every order, optionally filtered by status.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! in_array($status, Order::STATUSES, true)) {
            $status = 'all';
        }

        $orders = Order::with(['product', 'productPrice'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    /**
     * Show a single order.
     */
    public function show(Order $order): View
    {
        $order->load(['product', 'productPrice', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update an order's status and internal notes.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Order updated.');
    }
}
