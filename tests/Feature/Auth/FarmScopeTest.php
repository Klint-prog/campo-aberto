<?php

namespace Tests\Feature\Auth;

use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_unlinked_farm(): void
    {
        $tenant = Tenant::query()->create(['id' => '32000000-0000-0000-0000-000000000001', 'name' => 'T', 'slug' => 't', 'is_active' => true]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $farm = Farm::query()->create(['id' => '32000000-0000-0000-0000-000000000002', 'tenant_id' => $tenant->id, 'name' => 'Fazenda']);

        $this->actingAs($user)->getJson('/api/internal/v1/farms?farm_id='.$farm->id)->assertForbidden();
    }
}
