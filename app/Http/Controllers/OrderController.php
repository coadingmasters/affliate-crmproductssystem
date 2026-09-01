<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\FormField;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show the public order form.
     */
    public function create(): View
    {
        return view('frontend.order', [
            'products' => Product::active()->orderBy('name')->get(),
            'fields' => FormField::visible()->get(),
        ]);
    }

    /**
     * Months covered by the earnings chart.
     */
    private const CHART_MONTHS = 6;

    /**
     * The signed in customer's own dashboard.
     *
     * Everything is scoped to the signed in account, and only that customer's
     * own commission is ever exposed - the admin's cut stays in the admin panel.
     */
    public function history(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['product', 'productPrice'])
            ->latest()
            ->paginate(10);

        $counts = $user->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalOrders = (int) $counts->sum();
        $earnedOrders = $this->sumStatuses($counts, Order::EARNING_STATUSES);

        $earned = (float) $user->orders()
            ->whereIn('status', Order::EARNING_STATUSES)
            ->sum('user_commission_total');

        $pending = (float) $user->orders()
            ->whereIn('status', Order::OPEN_STATUSES)
            ->sum('user_commission_total');

        $revenue = (float) $user->orders()
            ->whereIn('status', Order::EARNING_STATUSES)
            ->sum('total_price');

        return view('frontend.history', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'newOrders' => $this->sumStatuses($counts, Order::OPEN_STATUSES),
            'paidOrders' => $earnedOrders,
            'cancelledOrders' => $this->sumStatuses($counts, Order::LOST_STATUSES),

            'earned' => $earned,
            'pending' => $pending,
            'lifetime' => $earned + $pending,
            'revenue' => $revenue,
            'averageEarning' => $earnedOrders > 0 ? $earned / $earnedOrders : 0.0,
            'conversionRate' => $totalOrders > 0 ? $earnedOrders / $totalOrders * 100 : 0.0,

            'series' => $this->earningsByMonth($user),
            'statusBreakdown' => collect(Order::STATUS_META)
                ->map(fn ($meta, $key) => [
                    'label' => $meta['customer'],
                    'tone' => $meta['tone'],
                    'value' => (int) $counts->get($key, 0),
                ])
                ->filter(fn ($row) => $row['value'] > 0)
                ->sortByDesc('value')
                ->values(),
            'topProducts' => $this->topEarningProducts($user),
        ]);
    }

    /**
     * Commission earned per month across the recent window.
     *
     * Grouped in PHP rather than SQL so the query stays driver independent.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function earningsByMonth(User $user): Collection
    {
        $tz = config('app.display_timezone');
        $start = CarbonImmutable::now($tz)->startOfMonth()->subMonths(self::CHART_MONTHS - 1);

        $rows = $user->orders()
            ->whereIn('status', Order::EARNING_STATUSES)
            ->where('created_at', '>=', $start->utc())
            ->get(['created_at', 'user_commission_total'])
            ->groupBy(fn ($order) => $order->created_at->timezone($tz)->format('Y-m'));

        return collect(range(0, self::CHART_MONTHS - 1))->map(function (int $offset) use ($start, $rows) {
            $month = $start->addMonths($offset);
            $bucket = $rows->get($month->format('Y-m'));

            return [
                'label' => $month->format('M Y'),
                'short' => $month->format('M'),
                'value' => (float) ($bucket?->sum('user_commission_total') ?? 0),
                'orders' => (int) ($bucket?->count() ?? 0),
            ];
        });
    }

    /**
     * The products this customer earns the most from.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function topEarningProducts(User $user): Collection
    {
        return $user->orders()
            ->whereIn('status', Order::EARNING_STATUSES)
            ->selectRaw('product_id, COUNT(*) as orders, SUM(user_commission_total) as earned')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('earned')
            ->take(4)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->product?->name ?? 'Removed product',
                'orders' => (int) $row->orders,
                'earned' => (float) $row->earned,
            ]);
    }

    /**
     * Total the given statuses out of a status => count map.
     *
     * @param  array<int, string>  $statuses
     */
    private function sumStatuses(Collection $counts, array $statuses): int
    {
        return (int) collect($statuses)->sum(fn ($status) => (int) $counts->get($status, 0));
    }

    /**
     * Return a product's price options for the dependent dropdown.
     */
    public function prices(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        return response()->json(
            $product->prices()
                ->orderBy('price')
                ->get(['id', 'label', 'price'])
                ->map(fn ($price) => [
                    'id' => $price->id,
                    'label' => $price->label,
                    'price' => (float) $price->price,
                ]),
        );
    }

    /**
     * Store a submitted order.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        Order::create([
            // Tie the order to the signed in account.
            'user_id' => $request->user()->id,
            ...$request->columnAnswers(),
            ...$request->safe()->only(['product_id', 'product_price_id']),
            'quantity' => $request->quantity(),
            'form_data' => $request->customAnswers(),
            // Never trust the figures that came from the browser.
            'total_price' => $request->total(),
            'user_commission_total' => $request->userCommission(),
            'admin_commission_total' => $request->adminCommission(),
            'status' => 'new',
        ]);

        return redirect()
            ->route('order.create')
            ->with('status', 'Your order has been submitted successfully. We will contact you soon.');
    }
}
