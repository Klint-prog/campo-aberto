<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = '00000000-0000-0000-0000-000000000001';
        $userId = '00000000-0000-0000-0000-000000000002';
        $roleId = '00000000-0000-0000-0000-000000000003';
        $farmId = '00000000-0000-0000-0000-000000000004';

        if (! DB::table('tenants')->where('id', $tenantId)->exists()) {
            return;
        }

        DB::table('roles')->updateOrInsert(['id' => $roleId], [
            'tenant_id' => $tenantId,
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Perfil administrativo inicial.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (DB::table('users')->where('id', $userId)->exists() && DB::table('farms')->where('id', $farmId)->exists()) {
            DB::table('farm_users')->updateOrInsert(['farm_id' => $farmId, 'user_id' => $userId], [
                'id' => '00000000-0000-0000-0000-000000000006',
                'tenant_id' => $tenantId,
                'role_id' => $roleId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('permissions')->pluck('id') as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(['role_id' => $roleId, 'permission_id' => $permissionId], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('role_id', '00000000-0000-0000-0000-000000000003')->delete();
        DB::table('farm_users')->where('id', '00000000-0000-0000-0000-000000000006')->delete();
    }
};
