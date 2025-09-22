<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class TokenExpirationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_token_expiration_is_enforced()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Login to get a token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.access_token');
        $expiresAt = $loginResponse->json('data.expires_at');

        // Verify token has expiration time
        $this->assertNotNull($expiresAt);
        $this->assertTrue(\Carbon\Carbon::parse($expiresAt)->isFuture());

        // Verify token works initially
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');

        $response->assertStatus(200);
    }

    public function test_expired_token_cannot_access_any_protected_endpoint()
    {
        $user = User::factory()->create();
        
        // Create a token that expires in the past
        $token = $user->createToken('test-token', ['*'], now()->subMinute())->plainTextToken;
        
        // Test all protected endpoints
        $protectedEndpoints = [
            ['GET', '/api/me'],
            ['GET', '/api/customers'],
            ['POST', '/api/customers'],
            ['GET', '/api/products'],
            ['POST', '/api/products'],
            ['GET', '/api/orders'],
            ['POST', '/api/orders'],
            ['GET', '/api/dashboard/stats'],
            ['GET', '/api/dashboard/order-status-summary'],
            ['POST', '/api/logout'],
            ['POST', '/api/refresh']
        ];
        
        foreach ($protectedEndpoints as [$method, $endpoint]) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->json($method, $endpoint);
            
            $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthorized. Please provide a valid authentication token.'
                ]);
        }
    }

    public function test_token_expiration_time_matches_configuration()
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
        $expiresAt = \Carbon\Carbon::parse($tokenData['expires_at']);
        $createdAt = \Carbon\Carbon::parse($tokenData['created_at'] ?? now());
        
        // Calculate expected expiration (60 minutes from creation)
        $expectedExpiration = $createdAt->addMinutes(60);
        
        // Allow 5 minute tolerance for test execution time
        $this->assertTrue($expiresAt->diffInMinutes($expectedExpiration) < 5);
    }

    public function test_token_expiration_is_checked_on_every_request()
    {
        $user = User::factory()->create();
        
        // Create a token that expires in the past
        $token = $user->createToken('test-token', ['*'], now()->subMinute())->plainTextToken;
        
        // Request should fail immediately
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_valid_tokens_work_correctly()
    {
        $user = User::factory()->create();
        
        // Create a valid token
        $validToken = $user->createToken('valid-token', ['*'], now()->addHour())->plainTextToken;
        
        // Valid token should work
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $validToken
        ])->getJson('/api/me');
        
        $response->assertStatus(200);
    }

    public function test_token_expiration_handles_edge_cases()
    {
        $user = User::factory()->create();
        
        // Test token that expires exactly now
        $token = $user->createToken('test-token', ['*'], now())->plainTextToken;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_token_expiration_works_with_different_abilities()
    {
        $user = User::factory()->create();
        
        // Create token with specific abilities that expires in the past
        $token = $user->createToken('test-token', ['read', 'write'], now()->subMinute())->plainTextToken;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/me');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized. Please provide a valid authentication token.'
            ]);
    }

    public function test_multiple_expired_tokens_are_handled_correctly()
    {
        $user = User::factory()->create();
        
        // Create multiple expired tokens
        $tokens = [];
        for ($i = 0; $i < 5; $i++) {
            $tokens[] = $user->createToken("test-token-{$i}", ['*'], now()->subMinute())->plainTextToken;
        }
        
        // All expired tokens should return 401
        foreach ($tokens as $token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->getJson('/api/me');
            
            $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthorized. Please provide a valid authentication token.'
                ]);
        }
    }
}
