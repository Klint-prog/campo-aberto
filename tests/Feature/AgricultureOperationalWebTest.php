<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgricultureOperationalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_user_can_plan_activity_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $crop = Crop::create(['tenant_id' => $tenant->id, 'name' => 'Milho', 'is_active' => true]);

        $this->actingAs($user)
            ->post('/activities', [
                'farm_id' => $farm->id,
                'crop_id' => $crop->id,
                'type' => 'planting',
                'title' => 'Plantio operacional',
                'planned_start_on' => '2026-05-10',
                'planned_end_on' => '2026-05-12',
                'planned_area_ha' => 8,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'title' => 'Plantio operacional',
            'status' => Activity::STATUS_PLANNED,
        ]);

        $this->assertDatabaseHas('domain_events', [
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'event_name' => 'activity.planned',
        ]);
    }

    public function test_user_can_complete_and_cancel_activity_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $complete = $this->activity($tenant, $farm, $user, 'Atividade para concluir');
        $cancel = $this->activity($tenant, $farm, $user, 'Atividade para cancelar');

        $this->actingAs($user)
            ->post("/activities/{$complete->id}/complete", ['actual_area_ha' => 9.5])
            ->assertRedirect(route('activities.show', $complete));

        $this->actingAs($user)
            ->post("/activities/{$cancel->id}/cancel", ['reason' => 'Chuva intensa'])
            ->assertRedirect(route('activities.show', $cancel));

        $this->assertDatabaseHas('activities', ['id' => $complete->id, 'status' => Activity::STATUS_COMPLETED]);
        $this->assertDatabaseHas('activities', ['id' => $cancel->id, 'status' => Activity::STATUS_CANCELLED]);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'activity.completed', 'aggregate_id' => $complete->id]);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'activity.cancelled', 'aggregate_id' => $cancel->id]);
    }

    public function test_user_can_register_activity_input_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $activity = $this->activity($tenant, $farm, $user, 'Adubação');

        $this->actingAs($user)
            ->post("/activities/{$activity->id}/inputs", [
                'input_name' => 'NPK 20-05-20',
                'input_type' => 'fertilizer',
                'quantity' => 20,
                'unit' => 'kg',
                'unit_cost' => 5,
            ])
            ->assertRedirect(route('activities.show', $activity));

        $this->assertDatabaseHas('activity_inputs', [
            'tenant_id' => $tenant->id,
            'activity_id' => $activity->id,
            'input_name' => 'NPK 20-05-20',
            'total_cost' => 100,
        ]);

        $this->assertDatabaseHas('domain_events', ['event_name' => 'input.consumed_by_activity']);
    }

    public function test_user_can_register_harvest_and_see_productivity_from_web_interface(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $crop = Crop::create(['tenant_id' => $tenant->id, 'name' => 'Soja', 'is_active' => true]);

        $this->actingAs($user)
            ->post('/harvests', [
                'farm_id' => $farm->id,
                'crop_id' => $crop->id,
                'harvested_on' => '2026-05-20',
                'harvested_area_ha' => 10,
                'total_weight_kg' => 36000,
            ])
            ->assertRedirect();

        $harvest = Harvest::query()->firstOrFail();

        $this->assertSame('3600.0000', (string) $harvest->productivity_kg_ha);
        $this->assertDatabaseHas('domain_events', ['event_name' => 'harvest.recorded']);

        $this->actingAs($user)
            ->get(route('harvests.show', $harvest))
            ->assertOk()
            ->assertSee('3600.0000');
    }

    public function test_csv_exports_and_agriculture_report_are_available(): void
    {
        [$tenant, $user, $farm] = $this->context();
        $crop = Crop::create(['tenant_id' => $tenant->id, 'name' => 'Feijão', 'is_active' => true]);
        $this->activity($tenant, $farm, $user, 'Capina');
        Harvest::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'harvested_on' => '2026-05-22',
            'harvested_area_ha' => 5,
            'total_weight_kg' => 9000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->get('/activities/export.csv')->assertOk();
        $this->actingAs($user)->get('/harvests/export.csv')->assertOk();
        $this->actingAs($user)->get('/reports/agriculture')->assertOk()->assertSee('Relatórios agrícolas básicos');
    }

    private function context(): array
    {
        $tenant = Tenant::create([
            'id' => '30000000-0000-0000-0000-000000000001',
            'name' => 'Tenant Operacional',
            'slug' => 'tenant-operacional',
            'is_active' => true,
        ]);

        $user = User::create([
            'id' => '30000000-0000-0000-0000-000000000002',
            'tenant_id' => $tenant->id,
            'name' => 'Admin Operacional',
            'email' => 'admin@campoaberto.local',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $farm = Farm::create([
            'id' => '30000000-0000-0000-0000-000000000003',
            'tenant_id' => $tenant->id,
            'name' => 'Fazenda Operacional',
            'code' => 'OP-01',
            'city' => 'Goiana',
            'state' => 'PE',
            'total_area_ha' => 100,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$tenant, $user, $farm];
    }

    private function activity(Tenant $tenant, Farm $farm, User $user, string $title): Activity
    {
        return Activity::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'type' => 'fertilization',
            'status' => Activity::STATUS_PLANNED,
            'title' => $title,
            'planned_end_on' => now()->addDay()->toDateString(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
