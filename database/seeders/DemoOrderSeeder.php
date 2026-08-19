<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Fills the dashboard with sample orders so the charts have something to show.
 *
 * This is demo data only. Remove it with:
 *   php artisan tinker --execute="App\Models\Order::truncate();"
 */
class DemoOrderSeeder extends Seeder
{
    /**
     * Seed sample orders spread across the last two weeks.
     */
    public function run(): void
    {
        $products = Product::with('prices')->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No products found — run ProductSeeder first.');

            return;
        }

        $names = [
            'Margaret Chen', 'Robert Alvarez', 'Susan Whitfield', 'James Okafor',
            'Linda Petrova', 'David Nakamura', 'Patricia Boyle', 'Michael Osei',
            'Barbara Lindqvist', 'Thomas Reyes', 'Helen Kowalski', 'George Mbeki',
            'Dorothy Ferrari', 'Charles Dubois', 'Nancy Rahman', 'Frank Sullivan',
        ];

        $cities = [
            '1234 MAIN ST APT 5, LOS ANGELES CA 90001',
            '88 WESTBROOK AVE, PHOENIX AZ 85001',
            '2140 OAK RIDGE DR, DALLAS TX 75201',
            '77 HARBOR LN UNIT 3, MIAMI FL 33101',
            '905 PINEHURST CT, DENVER CO 80202',
        ];

        $index = 0;

        // Walk backwards through the last 14 days, with a busier recent stretch.
        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $volume = $daysAgo > 6
                ? random_int(0, 2)   // quieter first week
                : random_int(1, 4);  // busier second week

            for ($n = 0; $n < $volume; $n++) {
                $product = $products->random();
                $price = $product->prices->random();
                $quantity = random_int(1, 4);

                $date = now()
                    ->subDays($daysAgo)
                    ->setTime(random_int(8, 20), random_int(0, 59));

                $name = $names[$index % count($names)];
                $index++;

                $order = new Order([
                    'full_name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                    'phone' => '(555) '.random_int(200, 999).' '.random_int(1000, 9999),
                    'address' => $cities[array_rand($cities)],
                    'product_id' => $product->id,
                    'product_price_id' => $price->id,
                    'quantity' => $quantity,
                    'total_price' => round((float) $price->price * $quantity, 2),
                    'commission_total' => round((float) $price->commission * $quantity, 2),
                    'status' => $this->status($daysAgo),
                ]);

                $order->created_at = $date;
                $order->updated_at = $date;
                $order->save();
            }
        }

        $this->command?->info('Created '.Order::count().' demo orders.');
    }

    /**
     * Older orders have usually been resolved; recent ones are often still new.
     */
    private function status(int $daysAgo): string
    {
        if ($daysAgo <= 2) {
            return random_int(1, 10) <= 6 ? 'new' : 'callback';
        }

        $roll = random_int(1, 20);

        return match (true) {
            $roll <= 6 => 'sale',
            $roll <= 8 => 'active_account',
            $roll <= 10 => 'awaiting_payment',
            $roll <= 12 => 'confirmation_department',
            $roll <= 13 => 'post_date',
            $roll <= 14 => 'callback',
            $roll <= 16 => 'cancelled',
            $roll <= 17 => 'card_declined',
            $roll <= 18 => 'confirmation_failure',
            $roll <= 19 => 'going_to_return',
            default => 'duplicate',
        };
    }
}
