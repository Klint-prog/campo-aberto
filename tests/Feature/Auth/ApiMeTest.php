<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_me_returns_authenticated_user(): void
    {
        $tenant = Tenant::query()->create([
            'id' => '20000000-0000-0000-0000-000000000001',
            'name' => 'Tenant API',
            'slug' => 'tenant-api',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->getJson('/api/internal/v1/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email);
    }
}
