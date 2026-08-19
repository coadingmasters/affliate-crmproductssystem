<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed a couple of sample products, each with three price options.
     */
    public function run(): void
    {
        $samples = [
            [
                'name' => 'Med Alert Pendant',
                'description' => 'Waterproof medical alert pendant with 24/7 monitoring and a one-touch emergency button.',
                'prices' => [
                    ['label' => 'Single Unit', 'price' => 49.99, 'user_commission' => 8.00, 'admin_commission' => 4.00],
                    ['label' => 'Pack of 10', 'price' => 399.00, 'user_commission' => 60.00, 'admin_commission' => 30.00],
                    ['label' => 'Pack of 60', 'price' => 1999.00, 'user_commission' => 320.00, 'admin_commission' => 160.00],
                ],
            ],
            [
                'name' => 'Med Alert Wrist Band',
                'description' => 'Lightweight wrist band with fall detection, GPS tracking and a 5 day battery life.',
                'prices' => [
                    ['label' => 'Small', 'price' => 59.99, 'user_commission' => 10.00, 'admin_commission' => 5.00],
                    ['label' => 'Medium', 'price' => 69.99, 'user_commission' => 12.00, 'admin_commission' => 6.00],
                    ['label' => 'Large', 'price' => 79.99, 'user_commission' => 14.00, 'admin_commission' => 7.00],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $product = Product::updateOrCreate(
                ['name' => $sample['name']],
                [
                    'description' => $sample['description'],
                    'is_active' => true,
                ],
            );

            foreach ($sample['prices'] as $price) {
                $product->prices()->updateOrCreate(
                    ['label' => $price['label']],
                    ['price' => $price['price'], 'user_commission' => $price['user_commission'], 'admin_commission' => $price['admin_commission']],
                );
            }
        }
    }
}
