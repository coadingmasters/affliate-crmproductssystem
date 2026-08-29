<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\FormField;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\DateRange;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Date range presets offered in the filter bar.
     */
    public const PERIODS = DateRange::PERIODS;

    /**
     * Sort options.
     */
    public const SORTS = [
        'newest' => 'Newest first',
        'oldest' => 'Oldest first',
        'total_desc' => 'Highest total',
        'total_asc' => 'Lowest total',
        'commission_desc' => 'Highest user commission',
    ];

    /**
     * Page sizes.
     */
    public const PER_PAGE = [15, 25, 50, 100];

    /**
     * List orders with search, status, date range, product and sort filters.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $query = Order::with(['product', 'productPrice', 'user'])
            ->tap(fn (Builder $q) => $this->applyFilters($q, $filters));

        // Totals reflect the current filter, not the whole table.
        $totals = (clone $query)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total_price), 0) as revenue, COALESCE(SUM(user_commission_total), 0) as user_commission, COALESCE(SUM(admin_commission_total), 0) as admin_commission')
            ->reorder()
            ->first();

        $orders = $query
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'customers' => User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'periods' => self::PERIODS,
            'sorts' => self::SORTS,
            'perPageOptions' => self::PER_PAGE,
            'statusMeta' => Order::STATUS_META,
            'totalOrders' => (int) ($totals->orders ?? 0),
            'totalRevenue' => (float) ($totals->revenue ?? 0),
            'totalUserCommission' => (float) ($totals->user_commission ?? 0),
            'totalAdminCommission' => (float) ($totals->admin_commission ?? 0),
            'activeFilterCount' => $this->activeFilterCount($filters),
        ]);
    }

    /**
     * Show a single order.
     */
    public function show(Order $order): View
    {
        $order->load(['product', 'productPrice', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
            // Keyed by field key so answers can be labelled properly.
            'customFields' => FormField::all()->keyBy('key'),
        ]);
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

    /**
     * Change an order's status from the list, without a page reload.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $rules = ['status' => ['required', Rule::in(Order::statuses())]];

        // The date-bearing statuses still demand their date here.
        foreach (Order::STATUS_DATES as $status => $meta) {
            $rules[$meta['column']] = [
                Rule::requiredIf(fn () => $request->input('status') === $status),
                'nullable',
                'date',
            ];
        }

        $data = $request->validate($rules, [
            'post_date.required' => 'Enter the date the customer will pay.',
            'sale_date.required' => 'Enter the date the sale was made.',
            'return_date.required' => 'Enter the date it is going back.',
        ]);

        $order->update($data);
        $order->refresh();

        return response()->json([
            'status' => $order->status,
            'label' => $order->statusLabel(),
            'classes' => $order->statusClasses(),
            'date_label' => $order->statusDateLabel(),
            'date_value' => $order->statusDateValue(),
            'changed_at' => $order->statusChangedAt()?->format('M j, g:i A'),
            'message' => 'Order #'.$order->id.' set to '.$order->statusLabel().'.',
        ]);
    }

    /**
     * Read and sanitise every filter off the request.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $status = $request->query('status');
        $period = $request->query('period');
        $sort = $request->query('sort');
        $perPage = (int) $request->query('per_page', 15);

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => in_array($status, Order::statuses(), true) ? $status : 'all',
            'period' => array_key_exists((string) $period, self::PERIODS) ? $period : 'all',
            'from' => DateRange::parseDate($request->query('from')),
            'to' => DateRange::parseDate($request->query('to')),
            'product_id' => $request->query('product_id') ? (int) $request->query('product_id') : null,
            // Several accounts can be inspected side by side.
            'user_ids' => collect((array) $request->query('user_ids', []))
                ->filter(fn ($id) => ctype_digit((string) $id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'sort' => array_key_exists((string) $sort, self::SORTS) ? $sort : 'newest',
            'per_page' => in_array($perPage, self::PER_PAGE, true) ? $perPage : 15,
        ];
    }

    /**
     * Apply the filters to the query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';

            $query->where(function (Builder $q) use ($term, $filters) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('address', 'like', $term);

                // Let a bare number match the order id too.
                if (ctype_digit($filters['q'])) {
                    $q->orWhere('id', (int) $filters['q']);
                }

                // Match the account that submitted it too.
                $q->orWhereHas('user', function (Builder $u) use ($term) {
                    $u->where('name', 'like', $term)->orWhere('email', 'like', $term);
                });
            });
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if ($filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['user_ids'])) {
            $query->whereIn('user_id', $filters['user_ids']);
        }

        [$from, $to] = $this->dateRange($filters);

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        match ($filters['sort']) {
            'oldest' => $query->oldest(),
            'total_desc' => $query->orderByDesc('total_price'),
            'total_asc' => $query->orderBy('total_price'),
            'commission_desc' => $query->orderByDesc('user_commission_total'),
            default => $query->latest(),
        };
    }

    /**
     * Turn the chosen period into a concrete UTC range.
     *
     * Boundaries are worked out in the display timezone so "today" means
     * today for whoever is reading the screen, then converted for the query.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function dateRange(array $filters): array
    {
        return DateRange::resolve($filters['period'], $filters['from'], $filters['to']);
    }

    /**
     * How many filters are currently narrowing the list.
     *
     * @param  array<string, mixed>  $filters
     */
    private function activeFilterCount(array $filters): int
    {
        return collect([
            $filters['q'] !== '',
            $filters['status'] !== 'all',
            $filters['period'] !== 'all',
            $filters['product_id'] !== null,
            ! empty($filters['user_ids']),
            $filters['sort'] !== 'newest',
        ])->filter()->count();
    }
}
