<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_customers()
    {
        Customer::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/customers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => ['id', 'first_name', 'last_name', 'email', 'phone']
                    ],
                    'pagination'
                ]);
    }

    public function test_can_create_customer()
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/customers', $customerData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Customer created successfully'
                ])
                ->assertJsonStructure([
                    'data' => ['id', 'first_name', 'last_name', 'email']
                ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_can_show_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/customers/{$customer->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $customer->getKey(),
                        'first_name' => $customer->getFirstName(),
                        'last_name' => $customer->getLastName(),
                        'email' => $customer->getEmail()
                    ]
                ]);
    }

    public function test_can_update_customer()
    {
        $customer = Customer::factory()->create();

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '+9876543210'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/customers/{$customer->getKey()}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Customer updated successfully'
                ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->getKey(),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);
    }

    public function test_can_delete_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/customers/{$customer->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Customer deleted successfully'
                ]);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->getKey()
        ]);
    }

    public function test_customer_validation_requires_first_name()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/customers', [
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name']);
    }

    public function test_customer_validation_requires_email()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/customers', [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_customer_email_must_be_unique()
    {
        Customer::factory()->create(['email' => 'john@example.com']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/customers', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_can_filter_customers_by_email()
    {
        Customer::factory()->create(['email' => 'john@example.com']);
        Customer::factory()->create(['email' => 'jane@example.com']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/customers?email=john@example.com');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('john@example.com', $data[0]['email']);
    }

    public function test_unauthenticated_user_cannot_access_customers()
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401);
    }
}
