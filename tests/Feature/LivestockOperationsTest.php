<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalLot;
use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivestockOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_animal_in_authorized_farm(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals", [
                'internal_code' => 'BOV-001',
                'species' => 'bovine',
                'sex' => 'female',
                'breed' => 'nelore',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('animals', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'internal_code' => 'BOV-001',
            'status' => 'active',
        ]);
    }

    public function test_animal_purchase_dispatches_domain_event(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals", [
                'internal_code' => 'BOV-002',
                'species' => 'bovine',
                'sex' => 'male',
                'purchase_price' => 2500,
                'purchase_document' => 'NF-001',
                'origin' => 'Fornecedor Teste',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('domain_events', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'event_name' => 'animal.purchased',
        ]);
    }

    public function test_animal_sale_changes_status_and_dispatches_event(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $animal = $this->createAnimal($tenant, $farm, 'BOV-003');

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/sell", [
                'sale_price' => 3200,
                'sale_document' => 'NF-SALE-001',
                'counterparty' => 'Comprador Teste',
            ])
            ->assertOk();

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'sold']);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.sold', 'aggregate_id' => $animal->id]);
    }

    public function test_animal_death_changes_status_and_dispatches_event(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $animal = $this->createAnimal($tenant, $farm, 'BOV-004');

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/die", [
                'death_cause' => 'Acidente',
            ])
            ->assertOk();

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'dead', 'death_cause' => 'Acidente']);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.died', 'aggregate_id' => $animal->id]);
    }

    public function test_weight_record_dispatches_event(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $animal = $this->createAnimal($tenant, $farm, 'BOV-005');

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/weights", [
                'weighed_on' => '2026-05-05',
                'weight_kg' => 430.5,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('animal_weight_records', ['animal_id' => $animal->id, 'weight_kg' => 430.5]);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.weight_recorded']);
    }

    public function test_vaccination_treatment_and_feed_dispatch_events(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $lot = AnimalLot::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Lote 01', 'species' => 'bovine']);
        $animal = $this->createAnimal($tenant, $farm, 'BOV-006', $lot->id);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/vaccinations", [
                'vaccinated_on' => '2026-05-05',
                'vaccine_name' => 'Aftosa',
                'dose' => 5,
                'dose_unit' => 'ml',
            ])
            ->assertCreated();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/health-records", [
                'type' => 'treatment',
                'recorded_on' => '2026-05-05',
                'medicine_name' => 'Vermifugo',
                'quantity_consumed' => 1,
                'quantity_unit' => 'dose',
            ])
            ->assertCreated();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/animals/{$animal->id}/feed-consumptions", [
                'consumed_on' => '2026-05-05',
                'feed_name' => 'Racao proteica',
                'quantity' => 12,
                'unit' => 'kg',
                'unit_cost' => 2.5,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.vaccination_recorded']);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.treatment_recorded']);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'animal.feed_consumed']);
    }

    private function seedTenantUserAndFarm(): array
    {
        $tenant = Tenant::create(['name' => 'Tenant Teste', 'slug' => 'tenant-teste-'.uniqid(), 'is_active' => true]);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Administrador Campo Aberto', 'email' => 'admin@campoaberto.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $farm = Farm::create(['tenant_id' => $tenant->id, 'name' => 'Fazenda Teste', 'code' => 'FZ-'.uniqid(), 'state' => 'PE']);

        return [$tenant, $user, $farm];
    }

    private function createAnimal(Tenant $tenant, Farm $farm, string $code, ?string $lotId = null): Animal
    {
        return Animal::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'animal_lot_id' => $lotId, 'internal_code' => $code, 'species' => 'bovine', 'sex' => 'female', 'status' => 'active']);
    }
}
