<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBoilerplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration validation errors follow standard interceptor response structure.
     */
    public function test_registration_requires_validation_and_returns_standard_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'status_code' => 422,
                'message' => 'Error validation',
            ]);

        $response->assertJsonStructure([
            'status',
            'status_code',
            'message',
            'error_items',
        ]);

        // Ensure errors are returned as a flat array of strings
        $this->assertIsArray($response->json('error_items'));
        $this->assertNotEmpty($response->json('error_items'));
    }

    /**
     * Test successful registration returns JWT token and standard success structure.
     */
    public function test_registration_success_returns_jwt_token_and_standard_success_response(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'status_code' => 201,
                'message' => 'User registered successfully',
            ])
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'result' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /**
     * Test login failure returns standard unauthorized response.
     */
    public function test_login_failure_returns_standard_unauthorized_error(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized',
                'error_items' => ['Invalid email or password'],
            ]);
    }

    /**
     * Test login success returns JWT token.
     */
    public function test_login_success_returns_jwt_token(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'result' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    /**
     * Test that protected routes return 401 Unauthenticated when called without JWT.
     */
    public function test_protected_routes_require_jwt_auth(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthenticated.',
                'error_items' => [],
            ]);
    }

    /**
     * Test me endpoint fetches profile when authenticated.
     */
    public function test_me_endpoint_returns_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'User profile fetched successfully',
                'result' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ]);
    }

    /**
     * Test full Product CRUD operations with standard formats.
     */
    public function test_product_crud_endpoints_work_and_return_standard_response(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        // 1. Create Product (POST)
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/products', [
            'name' => 'Product A',
            'description' => 'Description of Product A',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'status_code' => 201,
                'message' => 'Successfully created data.',
                'result' => [
                    'name' => 'Product A',
                    'stock' => 10,
                ],
            ]);

        $productId = $response->json('result.id');

        // 2. Fetch All Products (GET)
        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Success.',
            ])
            ->assertJsonStructure([
                'result' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'first_page',
                    'data',
                ],
            ]);

        // 3. Fetch Product Details (GET)
        $response = $this->actingAs($user, 'api')->getJson("/api/v1/products/{$productId}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Success.',
                'result' => [
                    'id' => $productId,
                    'name' => 'Product A',
                ],
            ]);

        // 4. Update Product (PUT)
        $response = $this->actingAs($user, 'api')->putJson("/api/v1/products/{$productId}", [
            'name' => 'Updated Product A',
            'price' => 149.99,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Successfully updated data.',
                'result' => [
                    'id' => $productId,
                    'name' => 'Updated Product A',
                ],
            ]);

        // 5. Delete Product (DELETE)
        $response = $this->actingAs($user, 'api')->deleteJson("/api/v1/products/{$productId}");
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Successfully deleted data.',
                'result' => null,
            ]);

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    /**
     * Test accessing non-existent product returns standard 404 response.
     */
    public function test_product_not_found_returns_standard_404_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products/9999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'status_code' => 404,
                'message' => 'Resource not found',
                'error_items' => [],
            ]);
    }

    /**
     * Test auth routes are rate limited.
     */
    public function test_auth_routes_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'rate-limit@example.com',
                'password' => 'invalid-password',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => 'rate-limit@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'status' => false,
                'status_code' => 429,
                'message' => 'Too many requests',
            ]);
    }

    /**
     * Test products index returns paginated structure and validates per_page.
     */
    public function test_products_index_returns_pagination_contract_and_validates_per_page(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        Product::create([
            'user_id' => $user->id,
            'name' => 'Product 1',
            'description' => 'Desc 1',
            'price' => 10,
            'stock' => 1,
        ]);
        Product::create([
            'user_id' => $user->id,
            'name' => 'Product 2',
            'description' => 'Desc 2',
            'price' => 20,
            'stock' => 2,
        ]);
        Product::create([
            'user_id' => $user->id,
            'name' => 'Product 3',
            'description' => 'Desc 3',
            'price' => 30,
            'stock' => 3,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products?per_page=2');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
                'message' => 'Success.',
            ])
            ->assertJsonStructure([
                'result' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'first_page',
                    'data',
                ],
            ])
            ->assertJsonCount(2, 'result.data');

        $invalidResponse = $this->actingAs($user, 'api')->getJson('/api/v1/products?per_page=999');

        $invalidResponse->assertStatus(422)
            ->assertJson([
                'status' => false,
                'status_code' => 422,
                'message' => 'Error validation',
            ]);
    }

    /**
     * Test unauthorized matrix for protected auth/product endpoints.
     */
    public function test_protected_routes_unauthorized_matrix(): void
    {
        $endpoints = [
            ['method' => 'getJson', 'uri' => '/api/v1/me'],
            ['method' => 'postJson', 'uri' => '/api/v1/logout'],
            ['method' => 'postJson', 'uri' => '/api/v1/refresh'],
            ['method' => 'getJson', 'uri' => '/api/v1/products'],
            ['method' => 'postJson', 'uri' => '/api/v1/products', 'payload' => []],
        ];

        foreach ($endpoints as $endpoint) {
            $method = $endpoint['method'];
            $response = $this->{$method}($endpoint['uri'], $endpoint['payload'] ?? []);

            $response->assertStatus(401)
                ->assertJson([
                    'status' => false,
                    'status_code' => 401,
                    'message' => 'Unauthenticated.',
                    'error_items' => [],
                ]);
        }
    }

    /**
     * Test ownership and role policy for product endpoints.
     */
    public function test_product_policy_blocks_non_owner_and_allows_admin(): void
    {
        $owner = User::factory()->create(['role' => 'manager']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'user_id' => $owner->id,
            'name' => 'Owned Product',
            'description' => 'Owned',
            'price' => 100,
            'stock' => 5,
        ]);

        $forbidden = $this->actingAs($otherUser, 'api')->getJson("/api/v1/products/{$product->id}");
        $forbidden->assertStatus(403)
            ->assertJson([
                'status' => false,
                'status_code' => 403,
                'message' => 'Forbidden',
            ]);

        $allowed = $this->actingAs($admin, 'api')->getJson("/api/v1/products/{$product->id}");
        $allowed->assertStatus(200)
            ->assertJson([
                'status' => true,
                'status_code' => 200,
            ]);
    }

    /**
     * Test API responses include request id header for traceability.
     */
    public function test_api_response_contains_request_id_header(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'unknown@example.com',
            'password' => 'invalid',
        ]);

        $response->assertHeader('X-Request-Id');
    }
}
