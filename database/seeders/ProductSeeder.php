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
                    ['label' => 'Single Unit', 'price' => 49.99, 'commission' => 8.00],
                    ['label' => 'Pack of 10', 'price' => 399.00, 'commission' => 60.00],
                    ['label' => 'Pack of 60', 'price' => 1999.00, 'commission' => 320.00],
                ],
            ],
            [
                'name' => 'Med Alert Wrist Band',
                'description' => 'Lightweight wrist band with fall detection, GPS tracking and a 5 day battery life.',
                'prices' => [
                    ['label' => 'Small', 'price' => 59.99, 'commission' => 10.00],
                    ['label' => 'Medium', 'price' => 69.99, 'commission' => 12.00],
                    ['label' => 'Large', 'price' => 79.99, 'commission' => 14.00],
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
                    ['price' => $price['price'], 'commission' => $price['commission']],
                );
            }
        }
    }
}
