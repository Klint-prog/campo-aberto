<?php

namespace Tests\Feature\Geospatial;

use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FarmGeoJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_lists_only_authorized_farms(): void
    {
        [$tenant, $user, $authorizedFarm] = $this->tenantUserAndFarm();

        Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda sem vínculo',
        ]);

        $this->actingAs($user)
            ->getJson('/api/internal/v1/farms')
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.id', $authorizedFarm->id);
    }

    public function test_valid_geojson_is_accepted_and_imported_as_plot(): void
    {
        [, $user, $farm] = $this->tenantUserAndFarm();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/plots/import-geojson", [
                'geojson' => $this->validFeatureCollection(),
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.imported', 1);

        $this->assertDatabaseHas('plots', [
            'farm_id' => $farm->id,
            'tenant_id' => $farm->tenant_id,
            'name' => 'Talhão 01',
        ]);
    }

    public function test_invalid_geojson_is_rejected(): void
    {
        [, $user, $farm] = $this->tenantUserAndFarm();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/plots/import-geojson", [
                'geojson' => [
                    'type' => 'FeatureCollection',
                    'features' => [[
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [-35.0, -8.0]],
                        'properties' => ['name' => 'Ponto inválido'],
                    ]],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('geojson');
    }

    public function test_geojson_endpoint_returns_only_authorized_farm_areas(): void
    {
        [, $user, $authorizedFarm] = $this->tenantUserAndFarm();
        $otherTenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Outro tenant',
            'slug' => 'outro-tenant',
            'is_active' => true,
        ]);
        $otherFarm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Fazenda externa',
        ]);

        $this->insertPlot($authorizedFarm, 'Talhão autorizado');
        $this->insertPlot($otherFarm, 'Talhão externo');

        $this->actingAs($user)
            ->getJson("/api/internal/v1/farms/{$authorizedFarm->id}/geojson")
            ->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.name', 'Talhão autorizado');

        $this->actingAs($user)
            ->getJson("/api/internal/v1/farms/{$otherFarm->id}/geojson")
            ->assertForbidden();
    }

    private function tenantUserAndFarm(): array
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Agro',
            'slug' => 'tenant-agro-'.Str::random(6),
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $farm = Farm::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Autorizada',
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

        return [$tenant, $user, $farm];
    }

    private function insertPlot(Farm $farm, string $name): void
    {
        DB::insert(
            'INSERT INTO plots (id, tenant_id, farm_id, name, properties, created_at, updated_at, geom) VALUES (?, ?, ?, ?, ?::jsonb, ?, ?, ST_Multi(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)))',
            [
                (string) Str::uuid(),
                $farm->tenant_id,
                $farm->id,
                $name,
                json_encode(['name' => $name]),
                now(),
                now(),
                json_encode($this->validFeatureCollection()['features'][0]['geometry']),
            ]
        );
    }

    private function validFeatureCollection(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-35.0000, -8.0000],
                        [-35.0000, -8.0100],
                        [-34.9900, -8.0100],
                        [-34.9900, -8.0000],
                        [-35.0000, -8.0000],
                    ]],
                ],
                'properties' => [
                    'name' => 'Talhão 01',
                    'code' => 'T-01',
                ],
            ]],
        ];
    }
}
