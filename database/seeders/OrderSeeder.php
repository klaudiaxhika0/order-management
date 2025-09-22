<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::factory()->count(30)->create();
        
        // Attach random products to each order
        foreach ($orders as $order) {
            $products = Product::inRandomOrder()->take(rand(1, 5))->get();
            $pivotData = [];

            /**
             * @var Product $product
             */
            foreach ($products as $product) {
                $quantity = rand(1, 5);
                $pivotData[$product->getKey()] = [
                    'quantity' => $quantity,
                    'unit_price' => $product->getPrice(),
                    'line_total' => $quantity * $product->getPrice(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $order->products()->attach($pivotData);
        }
    }
}
