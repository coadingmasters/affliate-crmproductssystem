<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Date to be Charged" (post_date) and "Sale completion" (sale_date) are two
 * separate database columns that, once set, are never cleared — an order
 * that was Sale and is now back to Post Date still carries both dates in the
 * database. Showing both at once reads as if the order were in two states
 * at the same time, so each column must only show its date while the order
 * is actually in that status.
 */
class OrdersListStatusDateColumnsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private ProductPrice $price;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Casey Customer',
            'email' => 'casey@example.com',
            'password' => bcrypt('secret1234'),
            'role' => 'user',
        ]);

        $this->price = ProductPrice::create([
            'product_id' => Product::create(['name' => 'Basic Pendant', 'description' => 'x', 'is_active' => true])->id,
            'label' => 'MMR 1',
            'price' => 34.95,
            'user_commission' => 10,
            'admin_commission' => 5,
        ]);
    }

    private function makeOrder(string $status): Order
    {
        return Order::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->price->product_id,
            'product_price_id' => $this->price->id,
            'full_name' => 'Willie Jay',
            'email' => 'c@example.com',
            'phone' => '6783261832',
            'address' => '1 Test Street',
            'quantity' => 1,
            'total_price' => 34.95,
            'user_commission_total' => 10,
            'admin_commission_total' => 5,
            'status' => $status,
        ]);
    }

    public function test_a_stale_sale_date_does_not_show_once_the_order_reverts(): void
    {
        // Sale first (stamping sale_date), then moved back to Post Date
        // (stamping post_date) — both columns now hold a value.
        $order = $this->makeOrder('sale');
        $order->forceFill(['sale_date' => '2026-09-03'])->saveQuietly();
        $order->update(['status' => 'post_date']);
        $order->forceFill(['post_date' => '2026-10-01'])->saveQuietly();

        $html = $this->actingAs($this->customer)
            ->get(route('order.list'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('10/1/2026', $html);
        $this->assertStringNotContainsString('9/3/2026', $html);
    }

    public function test_a_stale_post_date_does_not_show_once_the_order_becomes_a_sale(): void
    {
        // The reverse: Post Date first, then moved on to Sale.
        $order = $this->makeOrder('post_date');
        $order->forceFill(['post_date' => '2026-10-01'])->saveQuietly();
        $order->update(['status' => 'sale']);
        $order->forceFill(['sale_date' => '2026-09-03'])->saveQuietly();

        $html = $this->actingAs($this->customer)
            ->get(route('order.list'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('9/3/2026', $html);
        $this->assertStringNotContainsString('10/1/2026', $html);
    }
}
