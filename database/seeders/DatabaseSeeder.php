<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $tenantId = '00000000-0000-0000-0000-000000000001';
        $adminUserId = '00000000-0000-0000-0000-000000000002';
        $farmId = '00000000-0000-0000-0000-000000000004';
        $fieldId = '00000000-0000-0000-0000-000000000005';
        $pastureId = '00000000-0000-0000-0000-000000000006';
        $cropId = '00000000-0000-0000-0000-000000000007';
        $seasonId = '00000000-0000-0000-0000-000000000008';
        $animalLotId = '00000000-0000-0000-0000-000000000009';
        $accountId = '00000000-0000-0000-0000-000000000010';

        DB::table('tenants')->updateOrInsert(['id' => $tenantId], [
            'name' => 'Campo Aberto Demonstração',
            'slug' => 'campo-aberto-demo',
            'document' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->updateOrInsert(['id' => $adminUserId], [
            'tenant_id' => $tenantId,
            'name' => 'Administrador',
            'email' => 'admin@campoaberto.local',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'remember_token' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('farms', ['id' => $farmId], [
            'tenant_id' => $tenantId,
            'name' => 'Fazenda Campo Aberto Demo',
            'code' => 'DEMO-001',
            'city' => 'Goiana',
            'state' => 'PE',
            'total_area_ha' => 128.50,
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('fields', ['id' => $fieldId], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Talhão Norte',
            'code' => 'TN-01',
            'area_ha' => 18.7500,
            'perimeter_m' => 1840.00,
            'properties' => json_encode(['solo' => 'argiloso', 'demo' => true]),
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('pastures', ['id' => $pastureId], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Pasto Principal',
            'code' => 'PP-01',
            'area_ha' => 22.3000,
            'perimeter_m' => 2100.00,
            'properties' => json_encode(['capim' => 'braquiária', 'demo' => true]),
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('crops', ['id' => $cropId], [
            'tenant_id' => $tenantId,
            'name' => 'Milho',
            'scientific_name' => 'Zea mays',
            'cycle_type' => 'annual',
            'description' => 'Cultura demo para validação operacional.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('seasons', ['id' => $seasonId], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Safra 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'notes' => 'Safra demo criada pela Fase 11.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('activities', ['id' => '00000000-0000-0000-0000-000000000011'], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'season_id' => $seasonId,
            'crop_id' => $cropId,
            'type' => 'planting',
            'status' => 'planned',
            'title' => 'Plantio experimental',
            'description' => 'Atividade demo para validar dashboard e navegação.',
            'planned_start_on' => '2026-05-10',
            'planned_end_on' => '2026-05-12',
            'planned_area_ha' => 8.5000,
            'metadata' => json_encode(['demo' => true]),
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('animal_lots', ['id' => $animalLotId], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'pasture_id' => $pastureId,
            'name' => 'Lote Bovino Demo',
            'code' => 'BOV-DEMO',
            'species' => 'bovine',
            'purpose' => 'beef',
            'status' => 'active',
            'started_on' => '2026-01-15',
            'notes' => 'Lote demo da Fase 11.',
            'metadata' => json_encode(['demo' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('animals', ['id' => '00000000-0000-0000-0000-000000000012'], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'animal_lot_id' => $animalLotId,
            'internal_code' => 'AN-DEMO-001',
            'ear_tag' => 'BR-001',
            'name' => 'Bovino Demo',
            'species' => 'bovine',
            'breed' => 'nelore',
            'sex' => 'male',
            'status' => 'active',
            'acquired_on' => '2026-02-01',
            'purchase_price' => 2500.00,
            'metadata' => json_encode(['demo' => true]),
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('inventory_items', ['id' => '00000000-0000-0000-0000-000000000013'], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Estoque inicial demo',
            'type' => 'input',
            'sku' => 'INS-DEMO-001',
            'unit' => 'kg',
            'current_quantity' => 100.0000,
            'minimum_quantity' => 10.0000,
            'unit_cost' => 4.5000,
            'supplier' => 'Fornecedor Demo',
            'is_active' => true,
            'metadata' => json_encode(['demo' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('machines', ['id' => '00000000-0000-0000-0000-000000000014'], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Trator Demo',
            'code' => 'TR-DEMO-001',
            'type' => 'tractor',
            'brand' => 'Demo',
            'model' => 'Rural 100',
            'manufacture_year' => 2024,
            'hour_meter' => 120.00,
            'fuel_type' => 'diesel',
            'operational_status' => 'available',
            'metadata' => json_encode(['demo' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('financial_accounts', ['id' => $accountId], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'name' => 'Conta Demo',
            'type' => 'cash',
            'opening_balance' => 1000.00,
            'is_active' => true,
            'metadata' => json_encode(['demo' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedIfTableExists('financial_transactions', ['id' => '00000000-0000-0000-0000-000000000015'], [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'financial_account_id' => $accountId,
            'type' => 'expense',
            'status' => 'paid',
            'description' => 'Movimentação financeira simples demo',
            'amount' => 250.00,
            'due_on' => '2026-05-01',
            'paid_on' => '2026-05-01',
            'metadata' => json_encode(['demo' => true]),
            'created_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedIfTableExists(string $table, array $keys, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $payload = collect($values)
            ->filter(fn ($value, string $column): bool => Schema::hasColumn($table, $column))
            ->all();

        $lookup = collect($keys)
            ->filter(fn ($value, string $column): bool => Schema::hasColumn($table, $column))
            ->all();

        if ($lookup === []) {
            $lookup = ['id' => (string) Str::uuid()];
        }

        DB::table($table)->updateOrInsert($lookup, $payload);
    }
}
