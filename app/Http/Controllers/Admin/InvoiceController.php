<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * The invoice as the partner sees it, with the controls to settle it.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['orders.product', 'order.product', 'user']);

        return view('admin.invoices.show', ['invoice' => $invoice]);
    }

    /**
     * The invoice as a file, for the team's own records.
     */
    public function download(Invoice $invoice): Response
    {
        $invoice->load(['orders.product', 'order.product', 'user']);

        return Pdf::loadView('frontend.invoices.pdf', ['invoice' => $invoice])
            ->setPaper('a4')
            // Embed only the glyphs used, rather than the whole font file.
            ->setOption('isFontSubsettingEnabled', true)
            ->download($invoice->number.'.pdf');
    }

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
