<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_receives_403(): void
    {
        $tenant = Tenant::query()->create([
            'id' => '30000000-0000-0000-0000-000000000001',
            'name' => 'Tenant',
            'slug' => 'tenant',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->get('/users')->assertForbidden();
    }
}
