<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalLot;
use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LivestockOperationalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_user_can_create_animal_lot_and_animal_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();

        $this->actingAs($user)
            ->post('/animal-lots', [
                'farm_id' => $farm->id,
                'name' => 'Lote Engorda 01',
                'code' => 'LEG-01',
                'species' => 'bovine',
                'purpose' => 'engorda',
                'status' => 'active',
                'started_on' => '2026-05-01',
            ])
            ->assertRedirect();

        $lot = AnimalLot::query()->firstOrFail();

        $this->actingAs($user)
            ->post('/animals', [
                'farm_id' => $farm->id,
                'animal_lot_id' => $lot->id,
                'internal_code' => 'AN-0001',
                'ear_tag' => 'BR-0001',
                'species' => 'bovine',
                'breed' => 'Nelore',
                'sex' => 'male',
                'acquired_on' => '2026-05-02',
                'purchase_price' => 2500,
                'purchase_document' => 'NF-1',
                'origin' => 'Fornecedor demo',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('animal_lots', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Lote Engorda 01']);
        $this->assertDatabaseHas('animals', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'internal_code' => 'AN-0001', 'status' => Animal::STATUS_ACTIVE]);
        $this->assertDatabaseHas('animal_movements', ['type' => 'purchase', 'amount' => 2500]);
        $this->assertDatabaseHas('domain_events', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'event_name' => 'animal.purchased']);
    }

    public function test_user_can_register_sell_death_weight_vaccination_health_and_feed_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $animal = $this->animal($tenant, $farm, $user);

        $this->actingAs($user)
            ->post("/animals/{$animal->id}/weights", ['weighed_on' => '2026-05-03', 'weight_kg' => 340.5])
            ->assertRedirect(route('animals.show', $animal));

        $this->actingAs($user)
            ->post("/animals/{$animal->id}/vaccinations", ['vaccinated_on' => '2026-05-04', 'vaccine_name' => 'Vacina demo', 'dose' => 2, 'dose_unit' => 'ml'])
            ->assertRedirect(route('animals.show', $animal));

        $this->actingAs($user)
            ->post("/animals/{$animal->id}/health-records", ['type' => 'treatment', 'recorded_on' => '2026-05-05', 'medicine_name' => 'Medicamento demo', 'quantity_consumed' => 1, 'quantity_unit' => 'frasco'])
            ->assertRedirect(route('animals.show', $animal));

        $this->actingAs($user)
            ->post("/animals/{$animal->id}/feed-consumptions", ['consumed_on' => '2026-05-06', 'feed_name' => 'Ração demo', 'quantity' => 10, 'unit' => 'kg', 'unit_cost' => 3.5])
            ->assertRedirect(route('animals.show', $animal));

        $this->actingAs($user)
            ->post("/animals/{$animal->id}/sell", ['sale_price' => 3200, 'sale_document' => 'NF-S', 'counterparty' => 'Comprador demo'])
            ->assertRedirect(route('animals.show', $animal));

        $deadAnimal = $this->animal($tenant, $farm, $user, 'AN-0003');
        $this->actingAs($user)
            ->post("/animals/{$deadAnimal->id}/die", ['death_cause' => 'Causa operacional'])
            ->assertRedirect(route('animals.show', $deadAnimal));

        $this->assertDatabaseHas('animal_weight_records', ['animal_id' => $animal->id, 'weight_kg' => 340.5]);
        $this->assertDatabaseHas('animal_vaccination_records', ['animal_id' => $animal->id, 'vaccine_name' => 'Vacina demo']);
        $this->assertDatabaseHas('animal_health_records', ['animal_id' => $animal->id, 'medicine_name' => 'Medicamento demo']);
        $this->assertDatabaseHas('animal_feed_consumptions', ['animal_id' => $animal->id, 'feed_name' => 'Ração demo', 'total_cost' => 35]);
        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => Animal::STATUS_SOLD]);
        $this->assertDatabaseHas('animals', ['id' => $deadAnimal->id, 'status' => Animal::STATUS_DEAD]);

        foreach (['animal.sold', 'animal.died', 'animal.weight_recorded', 'animal.vaccination_recorded', 'animal.treatment_recorded', 'animal.feed_consumed'] as $event) {
            $this->assertDatabaseHas('domain_events', ['tenant_id' => $tenant->id, 'event_name' => $event]);
        }
    }

    public function test_livestock_exports_and_report_are_available(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $this->animal($tenant, $farm, $user);

        $this->actingAs($user)->get('/animal-lots/export.csv')->assertOk();
        $this->actingAs($user)->get('/animals/export.csv')->assertOk();
        $this->actingAs($user)->get('/animal-weights/export.csv')->assertOk();
        $this->actingAs($user)->get('/animal-health/export.csv')->assertOk();
        $this->actingAs($user)->get('/reports/livestock')->assertOk()->assertSee('Relatórios pecuários básicos');
    }

    private function context(): array
    {
        $tenant = Tenant::create([
            'id' => '40000000-0000-0000-0000-000000000001',
            'name' => 'Tenant Pecuária',
            'slug' => 'tenant-pecuaria',
            'is_active' => true,
        ]);

        $user = User::create([
            'id' => '40000000-0000-0000-0000-000000000002',
            'tenant_id' => $tenant->id,
            'name' => 'Admin Campo Aberto',
            'email' => 'admin@campoaberto.local',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $farm = Farm::create([
            'id' => '40000000-0000-0000-0000-000000000003',
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Pecuária',
            'code' => 'PEC-01',
            'city' => 'Goiana',
            'state' => 'PE',
            'total_area_ha' => 100,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$tenant, $user, $farm];
    }

    private function animal(Tenant $tenant, Farm $farm, User $user, string $code = 'AN-0002'): Animal
    {
        $lot = AnimalLot::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'name' => 'Lote Teste '.$code,
            'code' => 'LT-'.$code,
            'species' => 'bovine',
            'status' => 'active',
        ]);

        return Animal::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'animal_lot_id' => $lot->id,
            'internal_code' => $code,
            'ear_tag' => 'BR-'.$code,
            'species' => 'bovine',
            'breed' => 'Nelore',
            'sex' => 'male',
            'status' => Animal::STATUS_ACTIVE,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
