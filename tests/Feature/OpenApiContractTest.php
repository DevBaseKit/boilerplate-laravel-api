<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenApiContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure OpenAPI file exists and declares critical API paths.
     */
    public function test_openapi_spec_declares_critical_paths(): void
    {
        $specPath = base_path('openapi/openapi.json');
        $this->assertFileExists($specPath);

        $spec = json_decode((string) file_get_contents($specPath), true);

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('openapi', $spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/api/v1/login', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/products', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/products/{product}', $spec['paths']);
    }

    /**
     * Ensure runtime response matches documented wrapper + pagination contract.
     */
    public function test_products_index_response_matches_openapi_contract_shape(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Contract Product',
            'description' => 'Contract',
            'price' => 10,
            'stock' => 1,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products?limit=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'result' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'first_page',
                    'data',
                ],
            ]);
    }
}
