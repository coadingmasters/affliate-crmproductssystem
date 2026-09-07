<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * The ranges a partner claims for. Weekly is the normal rhythm.
     */
    public const PERIODS = [
        'this_week' => 'This week',
        'last_week' => 'Last week',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'custom' => 'Custom range',
    ];

    /**
     * Every invoice this partner has raised.
     */
    public function index(Request $request): View
    {
        $invoices = $request->user()->invoices()
            ->with('order')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        $totals = $request->user()->invoices()
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('frontend.invoices.index', [
            'invoices' => $invoices,
            'totals' => collect(Invoice::STATUS_META)
                ->map(fn ($meta, $key) => [
                    'label' => $meta['label'],
                    'tone' => $meta['tone'],
                    'help' => $meta['help'],
                    'count' => (int) ($totals->get($key)->count ?? 0),
                    'amount' => (float) ($totals->get($key)->amount ?? 0),
                ])
                ->values(),
            'unbilled' => (float) $this->billable($request)->sum('user_commission_total'),
            'unbilledCount' => $this->billable($request)->count(),
        ]);
    }

    /**
     * Build a claim: pick a period, see what it covers, send it.
     */
    public function create(Request $request): View
    {
        [$period, $from, $to] = $this->period($request);
        [$start, $end] = DateRange::resolve($period, $from, $to);

        $orders = $this->billable($request)
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->where('created_at', '<=', $end))
            ->with(['product', 'productPrice'])
            ->oldest()
            ->get();

        return view('frontend.invoices.create', [
            'orders' => $orders,
            'periods' => self::PERIODS,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'rangeLabel' => DateRange::label($period, $from, $to),
            'start' => $start,
            'end' => $end,
            'earnings' => (float) $orders->sum('user_commission_total'),
            'orderValue' => (float) $orders->sum('total_price'),
        ]);
    }

    /**
     * Raise the claim for the orders the partner ticked.
     *
     * The ids are only ever a selection: every one is looked up again against
     * this partner's billable orders, so a posted id cannot reach someone
     * else's work, an order already claimed for, or one that never earned.
     */
    public function storePeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'orders' => ['required', 'array', 'min:1'],
            'orders.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'orders.required' => 'Tick at least one order to invoice.',
            'orders.min' => 'Tick at least one order to invoice.',
            'note.max' => 'Keep the note under 1000 characters.',
        ]);

        $orders = $this->billable($request)
            ->whereIn('id', $data['orders'])
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Those orders are no longer available to claim for.');
        }

        $invoice = DB::transaction(function () use ($request, $orders, $data) {
            // The period is read off the orders themselves, so the document
            // can never claim a span its lines do not cover.
            $invoice = Invoice::create([
                'user_id' => $request->user()->id,
                'period_start' => $orders->min('created_at')->toDateString(),
                'period_end' => $orders->max('created_at')->toDateString(),
                'amount' => $orders->sum('user_commission_total'),
                'status' => 'pending',
                'note' => $data['note'] ?? null,
            ]);

            $invoice->orders()->attach(
                $orders->mapWithKeys(fn (Order $order) => [
                    $order->id => [
                        'commission' => $order->user_commission_total,
                        'order_value' => $order->total_price,
                    ],
                ])->all()
            );

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Invoice '.$invoice->number.' is ready.');
    }

    /**
     * The invoice itself, laid out as a document.
     */
    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->user_id === $request->user()->id, 404);

        $invoice->load(['orders.product', 'order.product', 'user']);

        return view('frontend.invoices.show', ['invoice' => $invoice]);
    }

    /**
     * Raise an invoice against one of the customer's own orders.
     *
     * The amount is read from the order rather than the request, so a posted
     * total can never decide what gets billed.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($order->invoice || $order->invoices()->exists()) {
            return back()->with('error', 'An invoice has already been sent for this order.');
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'note.max' => 'Keep the note under 1000 characters.',
        ]);

        DB::transaction(function () use ($request, $order, $data) {
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'amount' => $order->total_price,
                'status' => 'pending',
                'note' => $data['note'] ?? null,
            ]);

            // Recorded on the pivot too, so one query answers whether an
            // order has been claimed for however the claim was raised.
            $invoice->orders()->attach($order->id, [
                'commission' => $order->user_commission_total,
                'order_value' => $order->total_price,
            ]);
        });

        return back()->with('status', 'Invoice sent. You will see the status here once it is reviewed.');
    }

    /**
     * This partner's orders that have earned and have not been claimed for.
     *
     * Stays a relation rather than a bare builder, so it is always scoped to
     * the signed in partner however it is chained onto.
     */
    private function billable(Request $request): HasMany
    {
        return $request->user()->orders()->billable();
    }

    /**
     * Read the chosen period back off the query string.
     *
     * @return array{0: string, 1: ?string, 2: ?string}
     */
    private function period(Request $request): array
    {
        $period = $request->query('period');

        return [
            array_key_exists((string) $period, self::PERIODS) ? $period : 'this_week',
            DateRange::parseDate($request->query('from')),
            DateRange::parseDate($request->query('to')),
        ];
    }
}
