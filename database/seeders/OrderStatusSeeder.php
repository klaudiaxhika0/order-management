<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Processing',
                'slug' => 'processing',
                'description' => 'Order is being prepared and processed',
                'color' => '#F59E0B',
                'sort_order' => 1,
            ],
            [
                'name' => 'Shipped',
                'slug' => 'shipped',
                'description' => 'Order has been shipped and is in transit',
                'color' => '#3B82F6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Delivered',
                'slug' => 'delivered',
                'description' => 'Order has been successfully delivered',
                'color' => '#10B981',
                'sort_order' => 3,
            ],
            [
                'name' => 'Canceled',
                'slug' => 'canceled',
                'description' => 'Order has been canceled',
                'color' => '#EF4444',
                'sort_order' => 4,
            ],
        ];

        foreach ($statuses as $status) {
            \App\Models\OrderStatus::create($status);
        }
    }
}
