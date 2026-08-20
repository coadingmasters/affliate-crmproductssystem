<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\FormField;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * The signed in customer's own order history.
     */
    public function history(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['product', 'productPrice'])
            ->latest()
            ->paginate(10);

        $counts = $request->user()->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('frontend.history', [
            'orders' => $orders,
            'totalOrders' => (int) $counts->sum(),
            'newOrders' => $this->sumStatuses($counts, Order::OPEN_STATUSES),
            'paidOrders' => $this->sumStatuses($counts, Order::EARNING_STATUSES),
            'cancelledOrders' => $this->sumStatuses($counts, Order::LOST_STATUSES),
            'totalSpent' => (float) $request->user()->orders()
                ->whereIn('status', Order::EARNING_STATUSES)
                ->sum('total_price'),

        ]);
    }

    /**
     * Total the given statuses out of a status => count map.
     *
     * @param  array<int, string>  $statuses
     */
    private function sumStatuses(\Illuminate\Support\Collection $counts, array $statuses): int
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
