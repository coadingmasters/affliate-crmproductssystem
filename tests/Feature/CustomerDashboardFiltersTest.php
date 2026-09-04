<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $other;

    private ProductPrice $pendant;

    private ProductPrice $bracelet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->makeCustomer('casey@example.com', 'Casey Customer');
        $this->other = $this->makeCustomer('dana@example.com', 'Dana Other');

        $this->pendant = $this->makePrice('Basic Pendant');
        $this->bracelet = $this->makePrice('Alert Bracelet');
    }

    private function makeCustomer(string $email, string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('secret1234'),
            'role' => 'user',
        ]);
    }

    private function makePrice(string $product): ProductPrice
    {
        return ProductPrice::create([
            'product_id' => Product::create(['name' => $product, 'description' => 'x', 'is_active' => true])->id,
            'label' => 'MMR 1',
            'price' => 100,
            'user_commission' => 10,
            'admin_commission' => 5,
        ]);
    }

    private function makeOrder(User $user, ProductPrice $price, string $status, float $commission, ?string $name = null): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'product_id' => $price->product_id,
            'product_price_id' => $price->id,
            'full_name' => $name ?? $user->name,
            'email' => $user->email,
            'phone' => '5551234',
            'address' => '1 Test Street',
            'quantity' => 1,
            'total_price' => 100,
            'user_commission_total' => $commission,
            'admin_commission_total' => 999.99,
            'status' => $status,
        ]);
    }

    private function dashboard(array $query = [])
    {
        return $this->actingAs($this->customer)->get(route('order.history', $query));
    }

    public function test_the_filter_bar_renders_without_an_account_filter(): void
    {
        $this->dashboard()
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('name="period"', false)
            ->assertSee('name="product_id"', false)
            ->assertSee('name="status"', false)
            // a customer has no other accounts to pick between
            ->assertDontSee('name="user_ids[]"', false)
            ->assertDontSee('Submitted by')
            ->assertDontSee('Dana Other');
    }

    public function test_the_status_filter_uses_customer_wording(): void
    {
        $this->dashboard()
            ->assertOk()
            ->assertSee('Confirmed')
            ->assertSee('Awaiting payment')
            // the admin's own labels stay in the admin panel
            ->assertDontSee('Confirmation Department')
            ->assertDontSee('Active Account');
    }

    public function test_the_money_follows_the_status_filter(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);
        $this->makeOrder($this->customer, $this->pendant, 'new', 90);

        $this->dashboard()
            ->assertOk()
            ->assertViewHas('earned', 150.0)
            ->assertViewHas('pending', 90.0);

        $this->dashboard(['status' => 'new'])
            ->assertOk()
            ->assertViewHas('earned', 0.0)
            ->assertViewHas('pending', 90.0)
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('activeFilterCount', 1);
    }

    public function test_it_filters_by_product(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);
        $this->makeOrder($this->customer, $this->bracelet, 'sale', 110);

        $this->dashboard(['product_id' => $this->bracelet->product_id])
            ->assertOk()
            ->assertViewHas('earned', 110.0)
            ->assertViewHas('totalOrders', 1);
    }

    public function test_it_filters_by_search_term(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150, 'Aunt Mabel');
        $this->makeOrder($this->customer, $this->pendant, 'sale', 110, 'Uncle Fred');

        $this->dashboard(['q' => 'Mabel'])
            ->assertOk()
            ->assertViewHas('earned', 150.0)
            ->assertViewHas('totalOrders', 1);
    }

    public function test_filters_stack(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);
        $this->makeOrder($this->customer, $this->bracelet, 'sale', 110);
        $this->makeOrder($this->customer, $this->pendant, 'new', 90);

        $this->dashboard(['product_id' => $this->pendant->product_id, 'status' => 'sale'])
            ->assertOk()
            ->assertViewHas('earned', 150.0)
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('activeFilterCount', 2);
    }

    public function test_a_forged_account_filter_cannot_widen_the_scope(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);
        $this->makeOrder($this->other, $this->pendant, 'sale', 777);

        $this->dashboard(['user_ids' => [$this->other->id]])
            ->assertOk()
            ->assertViewHas('earned', 150.0)
            ->assertViewHas('totalOrders', 1)
            ->assertDontSee('777.00');
    }

    public function test_the_list_and_the_dashboard_agree(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);
        $this->makeOrder($this->customer, $this->bracelet, 'cancelled', 110);

        $query = ['product_id' => $this->pendant->product_id];

        $dashboard = $this->dashboard($query)->assertOk();
        $list = $this->actingAs($this->customer)->get(route('order.list', $query))->assertOk();

        $this->assertSame(
            $dashboard->viewData('totalOrders'),
            $list->viewData('totalOrders'),
            'both customer screens must count the same orders for the same filters',
        );
    }

    public function test_the_admin_commission_is_still_hidden_under_filters(): void
    {
        $this->makeOrder($this->customer, $this->pendant, 'sale', 150);

        $this->dashboard(['status' => 'sale'])
            ->assertOk()
            ->assertSee('$150.00')
            ->assertDontSee('999.99');
    }
}
