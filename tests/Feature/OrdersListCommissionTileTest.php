<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The All Orders page used to total total_price under "Value" — a number
 * that included orders which never earned anything, and read like money
 * owed when it was not. It now shows commission, using the same
 * confirmed-minus-reversed arithmetic as the dashboard.
 */
class OrdersListCommissionTileTest extends TestCase
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

    private function makeOrder(string $status, float $commission, string $name): Order
    {
        return Order::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->price->product_id,
            'product_price_id' => $this->price->id,
            'full_name' => $name,
            'email' => 'c@example.com',
            'phone' => '5551234',
            'address' => '1 Test Street',
            'quantity' => 1,
            'total_price' => 34.95,
            'user_commission_total' => $commission,
            'admin_commission_total' => 999.99,
            'status' => $status,
        ]);
    }

    public function test_orders_that_never_earned_show_zero_commission(): void
    {
        // Neither status ever converts, so the tile must not total their
        // order value the way it used to.
        $this->makeOrder('duplicate', 0, 'Linda Morgan');
        $this->makeOrder('callback', 0, 'James Carter');

        $this->actingAs($this->customer)
            ->get(route('order.list'))
            ->assertOk()
            ->assertViewHas('totalOrders', 2)
            ->assertViewHas('commission', 0.0)
            ->assertViewHas('confirmed', 0.0)
            ->assertViewHas('reversed', 0.0)
            ->assertSee('Commission')
            ->assertDontSee('uppercase tracking-wider text-muted">Value<', false);
    }

    public function test_a_returning_order_is_taken_back_off_the_tile(): void
    {
        $this->makeOrder('sale', 60, 'Beverly Fentress');
        $this->makeOrder('going_to_return', 60, 'Jonas Hardy');

        $this->actingAs($this->customer)
            ->get(route('order.list'))
            ->assertOk()
            ->assertViewHas('confirmed', 60.0)
            ->assertViewHas('reversed', 60.0)
            ->assertViewHas('commission', 0.0)
            ->assertViewHas('returningOrders', 1)
            ->assertSee('confirmed')
            ->assertSee('chargeback');
    }

    public function test_the_tile_can_go_negative_when_returns_outweigh_sales(): void
    {
        $this->makeOrder('sale', 60, 'Beverly Fentress');
        $this->makeOrder('going_to_return', 200, 'Jonas Hardy');

        $this->actingAs($this->customer)
            ->get(route('order.list'))
            ->assertOk()
            ->assertViewHas('commission', -140.0)
            ->assertSee('-$140.00');
    }

    public function test_the_tile_follows_the_active_filters(): void
    {
        $this->makeOrder('sale', 60, 'Beverly Fentress');
        $this->makeOrder('sale', 320, 'Mary Carroll');

        $this->actingAs($this->customer)
            ->get(route('order.list', ['q' => 'Beverly']))
            ->assertOk()
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('commission', 60.0);
    }
}
