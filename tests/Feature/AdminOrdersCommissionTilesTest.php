<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin Orders list summed User Commission and Admin Commission across
 * every order matching the filter, including ones still in the pipeline —
 * New, Post Date, Confirmation Failure and the rest — that had never earned
 * anything. Only Sale, Active Account and Paid should count.
 */
class AdminOrdersCommissionTilesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private ProductPrice $price;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Ada Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret1234'),
            'role' => 'admin',
        ]);

        $this->customer = User::create([
            'name' => 'Casey Customer',
            'email' => 'casey@example.com',
            'password' => bcrypt('secret1234'),
            'role' => 'user',
        ]);

        $this->price = ProductPrice::create([
            'product_id' => Product::create(['name' => 'Basic Pendant', 'description' => 'x', 'is_active' => true])->id,
            'label' => 'MMR 1',
            'price' => 44.95,
            'user_commission' => 150,
            'admin_commission' => 100,
        ]);
    }

    private function makeOrder(string $status, string $name): Order
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
            'total_price' => 44.95,
            'user_commission_total' => 150,
            'admin_commission_total' => 100,
            'status' => $status,
        ]);
    }

    public function test_pipeline_orders_are_excluded_from_the_commission_tiles(): void
    {
        $this->makeOrder('new', 'Bryan K Gower');
        $this->makeOrder('post_date', 'Alice J Williams');
        $this->makeOrder('confirmation_failure', 'Janice Todd');
        $this->makeOrder('sale', 'William T Cogburn');
        $this->makeOrder('paid', 'Real Paid Lead');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertViewHas('totalOrders', 5)
            ->assertViewHas('totalRevenue', 224.75)
            ->assertViewHas('totalUserCommission', 300.0)
            ->assertViewHas('totalAdminCommission', 200.0);
    }

    public function test_the_tiles_read_zero_when_nothing_has_converted(): void
    {
        $this->makeOrder('new', 'Bryan K Gower');
        $this->makeOrder('callback', 'Janice Todd');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertViewHas('totalOrders', 2)
            ->assertViewHas('totalRevenue', 89.9)
            ->assertViewHas('totalUserCommission', 0.0)
            ->assertViewHas('totalAdminCommission', 0.0);
    }

    public function test_a_chargeback_does_not_still_count_toward_commission(): void
    {
        $this->makeOrder('going_to_return', 'Reversed Lead');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertViewHas('totalUserCommission', 0.0)
            ->assertViewHas('totalAdminCommission', 0.0);
    }

    public function test_the_tiles_still_follow_the_active_filters(): void
    {
        $this->makeOrder('sale', 'William T Cogburn')->update(['full_name' => 'Match Me']);
        $this->makeOrder('sale', 'No Match');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index', ['q' => 'Match Me']))
            ->assertOk()
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('totalUserCommission', 150.0);
    }
}
