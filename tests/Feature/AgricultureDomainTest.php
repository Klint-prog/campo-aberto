<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgricultureDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_can_only_be_created_for_authorized_farm(): void
    {
        $tenant = $this->tenant('10000000-0000-0000-0000-000000000001');
        $user = $this->user($tenant, '10000000-0000-0000-0000-000000000002', 'worker@campoaberto.local');
        $farm = $this->farm($tenant, '10000000-0000-0000-0000-000000000003');

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/activities", [
                'type' => 'planting',
                'title' => 'Plantio de milho',
            ])
            ->assertForbidden();
    }

    public function test_activity_completion_records_activity_completed_event(): void
    {
        [$tenant, $user, $farm] = $this->authorizedContext();

        $activity = Activity::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'type' => 'planting',
            'status' => Activity::STATUS_PLANNED,
            'title' => 'Plantio de milho',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/activities/{$activity->id}/complete", [
                'actual_area_ha' => 12.5,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Activity::STATUS_COMPLETED);

        $this->assertDatabaseHas('domain_events', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'event_name' => 'activity.completed',
            'aggregate_type' => Activity::class,
            'aggregate_id' => $activity->id,
        ]);
    }

    public function test_activity_input_records_consumption_event(): void
    {
        [$tenant, $user, $farm] = $this->authorizedContext();

        $activity = Activity::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'type' => 'fertilization',
            'status' => Activity::STATUS_PLANNED,
            'title' => 'Adubação de cobertura',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/activities/{$activity->id}/inputs", [
                'input_name' => 'NPK 20-05-20',
                'input_type' => 'fertilizer',
                'quantity' => 50,
                'unit' => 'kg',
                'unit_cost' => 4.25,
            ])
            ->assertCreated()
            ->assertJsonPath('data.total_cost', '212.5000');

        $this->assertDatabaseHas('domain_events', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'event_name' => 'input.consumed_by_activity',
        ]);
    }

    public function test_harvest_calculates_productivity_per_hectare(): void
    {
        [$tenant, $user, $farm] = $this->authorizedContext();

        $crop = Crop::create([
            'tenant_id' => $tenant->id,
            'name' => 'Milho',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/harvests", [
                'crop_id' => $crop->id,
                'harvested_on' => '2026-05-05',
                'harvested_area_ha' => 10,
                'total_weight_kg' => 85000,
            ])
            ->assertCreated()
            ->assertJsonPath('data.productivity_kg_ha', '8500.0000');

        $this->assertDatabaseHas('harvests', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'productivity_kg_ha' => 8500,
        ]);

        $this->assertDatabaseHas('domain_events', [
            'event_name' => 'harvest.recorded',
        ]);
    }

    private function authorizedContext(): array
    {
        $tenant = $this->tenant('20000000-0000-0000-0000-000000000001');
        $user = $this->user($tenant, '20000000-0000-0000-0000-000000000002', 'admin@campoaberto.local');
        $farm = $this->farm($tenant, '20000000-0000-0000-0000-000000000003', $user);

        return [$tenant, $user, $farm];
    }

    private function tenant(string $id): Tenant
    {
        return Tenant::create([
            'id' => $id,
            'name' => 'Tenant Teste '.$id,
            'slug' => 'tenant-'.substr($id, 0, 8),
            'is_active' => true,
        ]);
    }

    private function user(Tenant $tenant, string $id, string $email): User
    {
        return User::create([
            'id' => $id,
            'tenant_id' => $tenant->id,
            'name' => 'Usuário Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }

    private function farm(Tenant $tenant, string $id, ?User $user = null): Farm
    {
        return Farm::create([
            'id' => $id,
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Teste',
            'code' => 'TEST-'.substr($id, 0, 4),
            'city' => 'Goiana',
            'state' => 'PE',
            'total_area_ha' => 100,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);
    }
}
