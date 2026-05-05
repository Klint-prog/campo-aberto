<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $tenantId = '00000000-0000-0000-0000-000000000001';
        $adminUserId = '00000000-0000-0000-0000-000000000002';
        $farmId = '00000000-0000-0000-0000-000000000004';

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
            'name' => 'Administrador Campo Aberto',
            'email' => 'admin@campoaberto.local',
            'email_verified_at' => $now,
            'password' => Hash::make('CampoAberto@2026'),
            'remember_token' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('farms')->updateOrInsert(['id' => $farmId], [
            'tenant_id' => $tenantId,
            'name' => 'Fazenda Demonstração',
            'code' => 'DEMO-001',
            'city' => 'Goiana',
            'state' => 'PE',
            'total_area_ha' => 0,
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
