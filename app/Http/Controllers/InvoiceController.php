<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Raise an invoice against one of the customer's own orders.
     *
     * The amount is read from the order rather than the request, so a posted
     * total can never decide what gets billed.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($order->invoice) {
            return back()->with('error', 'An invoice has already been sent for this order.');
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'note.max' => 'Keep the note under 1000 characters.',
        ]);

        Invoice::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'amount' => $order->total_price,
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Invoice sent. You will see the status here once it is reviewed.');
    }
}
