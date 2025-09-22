<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => ['id', 'name', 'description', 'price', 'sku']
                    ],
                    'pagination'
                ]);
    }

    public function test_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product description',
            'price' => 99.99,
            'sku' => 'TEST123',
            'status' => 'active'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product created successfully'
                ])
                ->assertJsonStructure([
                    'data' => ['id', 'name', 'description', 'price', 'sku']
                ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST123',
            'price' => 99.99
        ]);
    }

    public function test_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/products/{$product->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $product->getKey(),
                        'name' => $product->name,
                        'price' => $product->price
                    ]
                ]);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 149.99
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/products/{$product->getKey()}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product updated successfully'
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->getKey(),
            'name' => 'Updated Product',
            'price' => 149.99
        ]);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/products/{$product->getKey()}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->getKey()
        ]);
    }

    public function test_product_validation_requires_name()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'price' => 99.99
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_product_validation_requires_price()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'name' => 'Test Product'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }


    public function test_product_name_must_be_unique()
    {
        Product::factory()->create(['name' => 'Test Product']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_can_sort_products_by_price()
    {
        Product::factory()->create(['price' => 100.00]);
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 150.00]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/products?sort_by=price&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(50.00, $data[0]['price']);
        $this->assertEquals(100.00, $data[1]['price']);
        $this->assertEquals(150.00, $data[2]['price']);
    }

    public function test_unauthenticated_user_cannot_access_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }
}
