<?php

namespace Tests\Feature\Geospatial;

use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FarmMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_open_farm_map(): void
    {
        [$user, $farm] = $this->authorizedUserAndFarm();

        $this->actingAs($user)
            ->get("/farms/{$farm->id}/map")
            ->assertOk()
            ->assertSee('Leaflet + OpenStreetMap')
            ->assertSee("Mapa da fazenda: {$farm->name}");
    }

    public function test_map_rejects_unauthorized_farm(): void
    {
        [$user] = $this->authorizedUserAndFarm();

        $otherTenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant bloqueado',
            'slug' => 'tenant-bloqueado',
            'is_active' => true,
        ]);

        $otherFarm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Mapa bloqueado',
        ]);

        $this->actingAs($user)
            ->get("/farms/{$otherFarm->id}/map")
            ->assertForbidden();
    }

    private function authorizedUserAndFarm(): array
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Mapa',
            'slug' => 'tenant-mapa',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $farm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Mapa',
            'city' => 'Goiana',
            'state' => 'PE',
        ]);

        DB::table('farm_users')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'user_id' => $user->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $farm];
    }
}
