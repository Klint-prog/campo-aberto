<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_tenant_user(): void
    {
        $tenantA = Tenant::query()->create(['id' => '31000000-0000-0000-0000-000000000001', 'name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $tenantB = Tenant::query()->create(['id' => '31000000-0000-0000-0000-000000000002', 'name' => 'B', 'slug' => 'b', 'is_active' => true]);
        $actor = User::factory()->create(['tenant_id' => $tenantA->id]);
        $target = User::factory()->create(['tenant_id' => $tenantB->id]);

        $this->actingAs($actor)->get('/users/'.$target->id)->assertForbidden();
    }
}
