<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_login_with_valid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token',
                    'token_type',
                    'expires_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_login_requires_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->getKey(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token',
                    'token_type',
                    'expires_at'
                ]
            ]);
    }

    public function test_token_has_expiration_time()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        
        $tokenData = $response->json('data');
        $this->assertNotNull($tokenData['expires_at']);
        $this->assertIsString($tokenData['expires_at']);
        
        // Verify the expiration time is in the future
        $expiresAt = \Carbon\Carbon::parse($tokenData['expires_at']);
        $this->assertTrue($expiresAt->isFuture());
    }

    public function test_expired_token_returns_unauthorized()
    {
        $user = User::factory()->create();
        
        // Create a token that expires in the past
        $token = $user->createToken('test-token', ['*'], now()->subMinute())->plainTextToken;
        
        // The token should be expired and return 401
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_invalid_token_returns_unauthorized()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-12345'
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_malformed_token_returns_unauthorized()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer malformed.token.12345'
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_token_without_bearer_prefix_returns_unauthorized()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeaders([
            'Authorization' => $token // Missing 'Bearer ' prefix
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_expired_token_cannot_access_protected_apis()
    {
        $user = User::factory()->create();
        
        // Create a token that expires in the past
        $token = $user->createToken('test-token', ['*'], now()->subMinute())->plainTextToken;
        
        // Test various protected endpoints
        $endpoints = [
            '/api/customers',
            '/api/products', 
            '/api/orders',
            '/api/dashboard/stats'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->getJson($endpoint);
            
            $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthorized. Please provide a valid authentication token.'
                ]);
        }
    }

    public function test_token_expiration_time_is_configurable()
    {
        // Test that the token expiration respects the Sanctum configuration
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        
        $tokenData = $response->json('data');
        $expiresAt = \Carbon\Carbon::parse($tokenData['expires_at']);
        $createdAt = \Carbon\Carbon::parse($tokenData['created_at'] ?? now());
        
        // The token should expire approximately 60 minutes after creation
        // (based on our SANCTUM_EXPIRATION=60 configuration)
        $expectedExpiration = $createdAt->addMinutes(60);
        $this->assertTrue($expiresAt->diffInMinutes($expectedExpiration) < 5); // Allow 5 minute tolerance
    }
}
