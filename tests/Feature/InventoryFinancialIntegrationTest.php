<?php

namespace Tests\Feature;

use App\Models\DomainEvent;
use App\Models\Farm;
use App\Models\InventoryItem;
use App\Models\Machine;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryFinancialIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_entry_increases_inventory_quantity(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $item = InventoryItem::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Semente milho', 'type' => 'input', 'unit' => 'kg', 'current_quantity' => 10]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/stock-movements", [
                'inventory_item_id' => $item->id,
                'direction' => 'in',
                'reason' => 'purchase',
                'quantity' => 25,
                'unit_cost' => 4.5,
                'moved_on' => '2026-05-05',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'current_quantity' => 35]);
        $this->assertDatabaseHas('stock_movements', ['inventory_item_id' => $item->id, 'direction' => StockMovement::DIRECTION_IN, 'quantity' => 25]);
    }

    public function test_stock_output_reduces_inventory_quantity(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $item = InventoryItem::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Adubo', 'type' => 'input', 'unit' => 'kg', 'current_quantity' => 100]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/stock-movements", [
                'inventory_item_id' => $item->id,
                'direction' => 'out',
                'reason' => 'manual_adjustment',
                'quantity' => 30,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'current_quantity' => 70]);
    }

    public function test_stock_output_without_balance_is_blocked(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $item = InventoryItem::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Vacina', 'type' => 'medicine', 'unit' => 'dose', 'current_quantity' => 1]);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/stock-movements", [
                'inventory_item_id' => $item->id,
                'direction' => 'out',
                'reason' => 'manual_adjustment',
                'quantity' => 2,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'current_quantity' => 1]);
    }

    public function test_activity_input_event_generates_stock_output(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $item = InventoryItem::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Calcario', 'sku' => 'CAL-01', 'type' => 'input', 'unit' => 'kg', 'current_quantity' => 50]);
        $event = $this->createEvent($tenant, $farm, 'input.consumed_by_activity', ['input_reference' => 'CAL-01', 'input_name' => 'Calcario', 'quantity' => 12, 'unit' => 'kg']);

        $this->actingAs($user)->postJson("/api/internal/v1/domain-events/{$event->id}/process-integrations")->assertOk()->assertJsonPath('processed', 1);

        $this->assertDatabaseHas('stock_movements', ['source_event_id' => $event->id, 'inventory_item_id' => $item->id, 'direction' => 'out', 'quantity' => 12]);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'current_quantity' => 38]);
    }

    public function test_animal_sale_event_generates_financial_revenue(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $event = $this->createEvent($tenant, $farm, 'animal.sold', ['animal_id' => '00000000-0000-0000-0000-000000000001', 'sale_price' => 3200, 'animal_lot_id' => null]);

        $this->actingAs($user)->postJson("/api/internal/v1/domain-events/{$event->id}/process-integrations")->assertOk()->assertJsonPath('processed', 1);

        $this->assertDatabaseHas('financial_transactions', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'source_event_id' => $event->id, 'type' => 'revenue', 'status' => 'paid', 'amount' => 3200]);
    }

    public function test_animal_purchase_event_generates_financial_expense(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $event = $this->createEvent($tenant, $farm, 'animal.purchased', ['animal_id' => '00000000-0000-0000-0000-000000000002', 'purchase_price' => 2500]);

        $this->actingAs($user)->postJson("/api/internal/v1/domain-events/{$event->id}/process-integrations")->assertOk()->assertJsonPath('processed', 1);

        $this->assertDatabaseHas('financial_transactions', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'source_event_id' => $event->id, 'type' => 'expense', 'status' => 'paid', 'amount' => 2500]);
    }

    public function test_maintenance_with_cost_generates_financial_expense(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();
        $machine = Machine::create(['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'name' => 'Trator 01', 'type' => 'machine', 'operational_status' => 'operational']);

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/machines/{$machine->id}/maintenance-records", [
                'type' => 'corrective',
                'description' => 'Troca de correia',
                'performed_on' => '2026-05-05',
                'cost' => 780,
                'supplier' => 'Oficina Teste',
            ])
            ->assertCreated();

        $event = DomainEvent::where('event_name', 'maintenance.performed')->latest()->firstOrFail();
        $this->actingAs($user)->postJson("/api/internal/v1/domain-events/{$event->id}/process-integrations")->assertOk()->assertJsonPath('processed', 1);

        $this->assertDatabaseHas('financial_transactions', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'source_event_id' => $event->id, 'type' => 'expense', 'amount' => 780, 'machine_id' => $machine->id]);
    }

    public function test_financial_transaction_respects_tenant_and_farm(): void
    {
        [$tenant, $user, $farm] = $this->seedTenantUserAndFarm();

        $this->actingAs($user)
            ->postJson("/api/internal/v1/farms/{$farm->id}/financial-transactions", [
                'type' => 'expense',
                'description' => 'Conta simples MVP',
                'amount' => 120,
                'due_on' => '2026-05-10',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('financial_transactions', ['tenant_id' => $tenant->id, 'farm_id' => $farm->id, 'type' => 'expense', 'amount' => 120]);
    }

    private function seedTenantUserAndFarm(): array
    {
        $tenant = Tenant::create(['name' => 'Tenant Teste', 'slug' => 'tenant-teste-'.uniqid(), 'is_active' => true]);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Administrador Campo Aberto', 'email' => 'admin'.uniqid().'@campoaberto.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $farm = Farm::create(['tenant_id' => $tenant->id, 'name' => 'Fazenda Teste', 'code' => 'FZ-'.uniqid(), 'state' => 'PE']);

        return [$tenant, $user, $farm];
    }

    private function createEvent(Tenant $tenant, Farm $farm, string $eventName, array $payload): DomainEvent
    {
        return DomainEvent::create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'event_name' => $eventName,
            'aggregate_type' => 'test',
            'aggregate_id' => '00000000-0000-0000-0000-000000000099',
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
