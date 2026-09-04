<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Move an invoice to paid, pending or rejected.
     *
     * Answers JSON so the status can be changed without leaving the page.
     */
    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Invoice::statuses())],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? $invoice->admin_note,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Invoice marked as '.$invoice->statusLabel().'.',
                'status' => $invoice->status,
                'label' => $invoice->statusLabel(),
                'classes' => $invoice->statusClasses(),
                'changed' => $invoice->statusChangedAtLabel(),
            ]);
        }

        return back()->with('status', 'Invoice marked as '.$invoice->statusLabel().'.');
    }
}
