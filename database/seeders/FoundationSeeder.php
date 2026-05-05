<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = '00000000-0000-0000-0000-000000000001';
        $adminUserId = '00000000-0000-0000-0000-000000000002';
        $adminRoleId = '00000000-0000-0000-0000-000000000003';
        $farmId = '00000000-0000-0000-0000-000000000004';

        DB::table('tenants')->updateOrInsert(
            ['id' => $tenantId],
            [
                'name' => 'Campo Aberto Demonstração',
                'slug' => 'campo-aberto-demo',
                'document' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['id' => $adminUserId],
            [
                'tenant_id' => $tenantId,
                'name' => 'Administrador Campo Aberto',
                'email' => 'admin@campoaberto.local',
                'email_verified_at' => now(),
                'password' => Hash::make('CampoAberto@2026'),
                'remember_token' => Str::random(10),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['id' => $adminRoleId],
            [
                'tenant_id' => $tenantId,
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Perfil administrativo inicial da fundação. Não representa autenticação completa.',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'foundation.manage'],
            [
                'id' => '00000000-0000-0000-0000-000000000005',
                'name' => 'Gerenciar fundação',
                'description' => 'Permissão inicial reservada para testes da fundação.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('role_permissions')->updateOrInsert(
            [
                'role_id' => $adminRoleId,
                'permission_id' => '00000000-0000-0000-0000-000000000005',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('farms')->updateOrInsert(
            ['id' => $farmId],
            [
                'tenant_id' => $tenantId,
                'name' => 'Fazenda Demonstração',
                'code' => 'DEMO-001',
                'city' => 'Goiana',
                'state' => 'PE',
                'total_area_ha' => 0,
                'created_by' => $adminUserId,
                'updated_by' => $adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('farm_users')->updateOrInsert(
            [
                'farm_id' => $farmId,
                'user_id' => $adminUserId,
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000006',
                'tenant_id' => $tenantId,
                'role_id' => $adminRoleId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
