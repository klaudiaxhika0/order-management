<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_dashboard_stats()
    {
        // Create test data
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $status = OrderStatus::factory()->create();
        
        Order::factory()->count(5)->create([
            'customer_id' => $customer->id,
            'status_id' => $status->id,
            'total' => 100.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    'orders' => [
                        'total',
                        'by_status',
                        'recent'
                    ],
                    'customers' => [
                        'total',
                        'new_this_month'
                    ],
                    'products' => [
                        'total',
                    ],
                    'revenue' => [
                        'total',
                        'this_month'
                    ]
                ]
            ]);

        $this->assertEquals(5, $response->json('data.orders.total'));
        $this->assertEquals(1, $response->json('data.customers.total'));
        $this->assertEquals(1, $response->json('data.products.total'));
    }

    public function test_can_get_order_status_summary()
    {
        $status1 = OrderStatus::factory()->create(['name' => 'Processing']);
        $status2 = OrderStatus::factory()->create(['name' => 'Shipped']);
        
        Order::factory()->count(3)->create(['status_id' => $status1->id]);
        Order::factory()->count(2)->create(['status_id' => $status2->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/order-status-summary');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'orders_count'
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $this->getJson('/api/dashboard/stats')
            ->assertStatus(401);

        $this->getJson('/api/dashboard/order-status-summary')
            ->assertStatus(401);
    }
}