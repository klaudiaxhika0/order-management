<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create();
        $this->orderStatus = OrderStatus::factory()->create();
    }

    public function test_can_list_orders()
    {
        Order::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => ['id', 'customer_id', 'status_id', 'total', 'created_at']
                    ],
                    'pagination'
                ]);
    }

    public function test_can_create_order()
    {
        $orderData = [
            'customer_id' => $this->customer->getKey(),
            'status_id' => $this->orderStatus->getKey(),
            'total' => 199.99,
            'notes' => 'Test order',
            'products' => [
                [
                    'product_id' => $this->product->getKey(),
                    'quantity' => 2,
                    'price' => 99.99
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order created successfully'
                ])
                ->assertJsonStructure([
                    'data' => ['id', 'customer_id', 'status_id', 'total', 'products']
                ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->getKey(),
            'status_id' => $this->orderStatus->getKey(),
            'total' => 199.99
        ]);

        // Check pivot table
        $this->assertDatabaseHas('order_product', [
            'product_id' => $this->product->getKey(),
            'quantity' => 2,
            'unit_price' => 99.99
        ]);
    }

    public function test_can_show_order()
    {
        $order = Order::factory()->create();
        $order->products()->attach($this->product->getKey(), [
            'quantity' => 1,
            'unit_price' => 50.00,
            'line_total' => 50.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/orders/{$order->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $order->getKey(),
                        'customer_id' => $order->getCustomerId(),
                        'status_id' => $order->getStatusId()
                    ]
                ])
                ->assertJsonStructure([
                    'data' => ['products']
                ]);
    }

    public function test_can_update_order()
    {
        $order = Order::factory()->create();
        $newStatus = OrderStatus::factory()->create();

        $updateData = [
            'customer_id' => $this->customer->getKey(),
            'status_id' => $newStatus->getKey(),
            'total' => 299.99,
            'notes' => 'Updated order'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/orders/{$order->getKey()}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order updated successfully'
                ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'status_id' => $newStatus->getKey(),
            'total' => 299.99
        ]);
    }

    public function test_can_delete_order()
    {
        $order = Order::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/orders/{$order->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);

        $this->assertSoftDeleted('orders', [
            'id' => $order->getKey()
        ]);
    }

    public function test_order_validation_requires_customer_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', [
            'status_id' => $this->orderStatus->getKey(),
            'total' => 199.99
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_order_validation_requires_status_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', [
            'customer_id' => $this->customer->getKey(),
            'total' => 199.99
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status_id']);
    }

    public function test_order_validation_requires_total()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', [
            'customer_id' => $this->customer->getKey(),
            'status_id' => $this->orderStatus->getKey()
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['total']);
    }

    public function test_can_filter_orders_by_status()
    {
        $status1 = OrderStatus::factory()->create();
        $status2 = OrderStatus::factory()->create();
        
        Order::factory()->create(['status_id' => $status1->getKey()]);
        Order::factory()->create(['status_id' => $status2->getKey()]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/orders?status_id={$status1->getKey()}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($status1->getKey(), $data[0]['status_id']);
    }

    public function test_can_filter_orders_by_customer()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Order::factory()->create(['customer_id' => $customer1->getKey()]);
        Order::factory()->create(['customer_id' => $customer2->getKey()]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/orders?customer_id={$customer1->getKey()}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($customer1->getKey(), $data[0]['customer_id']);
    }

    public function test_can_sort_orders_by_total()
    {
        Order::factory()->create(['total' => 100.00]);
        Order::factory()->create(['total' => 200.00]);
        Order::factory()->create(['total' => 50.00]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/orders?sort_by=total&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(50.00, $data[0]['total']);
        $this->assertEquals(100.00, $data[1]['total']);
        $this->assertEquals(200.00, $data[2]['total']);
    }

    public function test_unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_can_update_order_status()
    {
        $order = Order::factory()->create();
        $newStatus = OrderStatus::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/orders/{$order->id}/status", [
            'status_id' => $newStatus->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_id' => $newStatus->id
        ]);
    }

    public function test_order_show_includes_detailed_information()
    {
        $order = Order::factory()->create();
        $order->products()->attach($this->product->id, [
            'quantity' => 2,
            'unit_price' => $this->product->getPrice(),
            'line_total' => 2 * $this->product->getPrice(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'customer',
                    'status',
                    'products',
                    'calculated_total',
                    'product_count',
                    'total_quantity'
                ]
            ]);
    }
}
