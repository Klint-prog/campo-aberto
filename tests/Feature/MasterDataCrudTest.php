<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::query()->where('email', 'admin@campoaberto.local')->firstOrFail();
        $this->farm = Farm::query()->where('tenant_id', $this->user->tenant_id)->firstOrFail();
    }

    public function test_master_data_indexes_are_protected_and_render_for_authenticated_user(): void
    {
        $this->get(route('farms.index'))->assertRedirect(route('login'));

        foreach (['farms.index', 'fields.index', 'pastures.index', 'crops.index', 'crop-varieties.index', 'seasons.index'] as $route) {
            $this->actingAs($this->user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('Exportar CSV');
        }
    }

    public function test_user_can_create_edit_filter_and_export_farm(): void
    {
        $this->actingAs($this->user)
            ->post(route('farms.store'), [
                'name' => 'Fazenda Teste CRUD',
                'code' => 'CRUD-001',
                'city' => 'Goiana',
                'state' => 'PE',
                'total_area_ha' => '45.80',
            ])
            ->assertRedirect();

        $farm = Farm::query()->where('code', 'CRUD-001')->firstOrFail();

        $this->actingAs($this->user)
            ->put(route('farms.update', $farm), [
                'name' => 'Fazenda Teste CRUD Atualizada',
                'code' => 'CRUD-001',
                'city' => 'Goiana',
                'state' => 'PE',
                'total_area_ha' => '47.10',
            ])
            ->assertRedirect(route('farms.show', $farm));

        $this->actingAs($this->user)
            ->get(route('farms.index', ['q' => 'Atualizada']))
            ->assertOk()
            ->assertSee('Fazenda Teste CRUD Atualizada');

        $this->actingAs($this->user)
            ->get(route('farms.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_create_field_pasture_crop_variety_and_season(): void
    {
        $this->actingAs($this->user)
            ->post(route('fields.store'), [
                'farm_id' => $this->farm->id,
                'name' => 'Talhão CRUD',
                'code' => 'T-CRUD',
                'area_ha' => '10.5000',
                'perimeter_m' => '980.25',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fields', [
            'tenant_id' => $this->user->tenant_id,
            'farm_id' => $this->farm->id,
            'name' => 'Talhão CRUD',
        ]);

        $this->actingAs($this->user)
            ->post(route('pastures.store'), [
                'farm_id' => $this->farm->id,
                'name' => 'Pastagem CRUD',
                'code' => 'P-CRUD',
                'area_ha' => '8.2500',
                'perimeter_m' => '720.15',
            ])
            ->assertRedirect();

        $this->actingAs($this->user)
            ->post(route('crops.store'), [
                'name' => 'Soja CRUD',
                'scientific_name' => 'Glycine max',
                'cycle_type' => 'annual',
                'description' => 'Cultura de teste.',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $crop = Crop::query()->where('name', 'Soja CRUD')->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('crop-varieties.store'), [
                'crop_id' => $crop->id,
                'name' => 'Variedade CRUD',
                'cultivar_code' => 'VC-01',
                'cycle_days' => '110',
                'expected_yield_kg_ha' => '3200.00',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($this->user)
            ->post(route('seasons.store'), [
                'farm_id' => $this->farm->id,
                'name' => 'Safra CRUD',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'planned',
                'notes' => 'Safra de teste.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pastures', ['name' => 'Pastagem CRUD', 'tenant_id' => $this->user->tenant_id]);
        $this->assertDatabaseHas('crop_varieties', ['name' => 'Variedade CRUD', 'tenant_id' => $this->user->tenant_id]);
        $this->assertDatabaseHas('seasons', ['name' => 'Safra CRUD', 'tenant_id' => $this->user->tenant_id]);
    }

    public function test_validation_messages_are_returned_in_portuguese(): void
    {
        $this->actingAs($this->user)
            ->from(route('farms.create'))
            ->post(route('farms.store'), ['state' => 'PERNAMBUCO'])
            ->assertRedirect(route('farms.create'))
            ->assertSessionHasErrors(['name', 'state']);
    }
}
