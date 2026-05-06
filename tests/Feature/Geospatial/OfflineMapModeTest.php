<?php

namespace Tests\Feature\Geospatial;

use App\Models\Farm;
use App\Models\OfflineMapPackage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineMapModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_map_shows_online_offline_selector_and_navigation(): void
    {
        [$user, $farm] = $this->authorizedUserAndFarm();

        $this->actingAs($user)
            ->get("/farms/{$farm->id}/map")
            ->assertOk()
            ->assertSee('Online')
            ->assertSee('Offline')
            ->assertSee('Fonte do mapa')
            ->assertSee('Status offline')
            ->assertSee('Dashboard')
            ->assertSee('Fazendas')
            ->assertSee('Mapas')
            ->assertSee('Voltar')
            ->assertSee('Use <strong>Online</strong>', false);
    }

    public function test_offline_status_returns_unavailable_when_package_does_not_exist(): void
    {
        [$user, $farm] = $this->authorizedUserAndFarm();

        $this->actingAs($user)
            ->getJson("/farms/{$farm->id}/map/offline/status")
            ->assertOk()
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('data.message', 'Mapa offline ainda não disponível para esta fazenda.');
    }

    public function test_offline_status_returns_ready_package_for_authorized_farm(): void
    {
        [$user, $farm] = $this->authorizedUserAndFarm();

        OfflineMapPackage::query()->create([
            'tenant_id' => $farm->tenant_id,
            'farm_id' => $farm->id,
            'name' => 'Pacote sede',
            'status' => 'ready',
            'tile_format' => 'png',
            'min_zoom' => 10,
            'max_zoom' => 18,
            'storage_path' => "offline-maps/{$farm->id}",
            'generated_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson("/farms/{$farm->id}/map/offline/status")
            ->assertOk()
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('data.package.name', 'Pacote sede')
            ->assertJsonPath('data.message', 'Mapa offline disponível para esta fazenda.');
    }

    public function test_offline_tile_is_served_from_local_storage(): void
    {
        Storage::fake('local');
        [$user, $farm] = $this->authorizedUserAndFarm();
        $path = "offline-maps/{$farm->id}";

        OfflineMapPackage::query()->create([
            'tenant_id' => $farm->tenant_id,
            'farm_id' => $farm->id,
            'name' => 'Pacote sede',
            'status' => 'ready',
            'tile_format' => 'png',
            'min_zoom' => 10,
            'max_zoom' => 18,
            'storage_path' => $path,
            'generated_at' => now(),
        ]);

        Storage::disk('local')->put("{$path}/10/123/456.png", 'fake-png-content');

        $this->actingAs($user)
            ->get("/farms/{$farm->id}/map/offline/tiles/10/123/456.png")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_offline_map_status_rejects_unauthorized_farm(): void
    {
        [$user] = $this->authorizedUserAndFarm();

        $otherTenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Offline Bloqueado',
            'slug' => 'tenant-offline-bloqueado',
            'is_active' => true,
        ]);

        $otherFarm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Mapa offline bloqueado',
        ]);

        $this->actingAs($user)
            ->getJson("/farms/{$otherFarm->id}/map/offline/status")
            ->assertForbidden();
    }

    private function authorizedUserAndFarm(): array
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Offline',
            'slug' => 'tenant-offline',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $farm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Offline',
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
