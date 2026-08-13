<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * How many days the activity chart covers.
     */
    private const TREND_DAYS = 14;

    /**
     * Show the admin dashboard.
     */
    public function index(): View
    {
        $counts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalOrders = (int) $counts->sum();
        $paidOrders = (int) $counts->get('paid', 0);

        $series = $this->dailySeries();
        $revenue = (float) Order::where('status', 'paid')->sum('total_price');
        $pipeline = (float) Order::where('status', 'new')->sum('total_price');

        return view('admin.dashboard', [
            'totalOrders' => $totalOrders,
            'newOrders' => (int) $counts->get('new', 0),
            'paidOrders' => $paidOrders,
            'cancelledOrders' => (int) $counts->get('cancelled', 0),

            'revenue' => $revenue,
            'pipeline' => $pipeline,
            'averageOrder' => $paidOrders > 0 ? $revenue / $paidOrders : 0.0,
            'conversionRate' => $totalOrders > 0 ? $paidOrders / $totalOrders * 100 : 0.0,

            'series' => $series,
            'trend' => $this->trendPercentage($series),
            'topProducts' => $this->topProducts(),

            'recentOrders' => Order::with(['product', 'productPrice'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * Orders and revenue per day, with empty days filled in.
     */
    private function dailySeries(): Collection
    {
        $from = now()->subDays(self::TREND_DAYS - 1)->startOfDay();

        $rows = Order::selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total_price) as revenue')
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return collect(range(0, self::TREND_DAYS - 1))->map(function (int $offset) use ($from, $rows) {
            $date = $from->copy()->addDays($offset);
            $row = $rows->get($date->toDateString());

            return [
                'label' => $date->format('M j'),
                'short' => $date->format('j'),
                'orders' => (int) ($row->orders ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
            ];
        });
    }

    /**
     * Percentage change between the first and second half of the trend window.
     */
    private function trendPercentage(Collection $series): float
    {
        $half = (int) floor($series->count() / 2);

        $previous = $series->take($half)->sum('orders');
        $current = $series->slice($half)->sum('orders');

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return ($current - $previous) / $previous * 100;
    }

    /**
     * The best selling products by revenue.
     */
    private function topProducts(): Collection
    {
        return Order::selectRaw('product_id, COUNT(*) as orders, SUM(total_price) as revenue')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->product?->name ?? 'Deleted product',
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ]);
    }
}
