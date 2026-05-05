<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $adminRoleId = '00000000-0000-0000-0000-000000000003';

        $permissions = [
            '00000000-0000-0000-0000-000000000007' => ['users.view', 'Visualizar usuários'],
            '00000000-0000-0000-0000-000000000008' => ['users.manage', 'Gerenciar usuários'],
            '00000000-0000-0000-0000-000000000009' => ['farms.view', 'Visualizar fazendas'],
            '00000000-0000-0000-0000-000000000010' => ['permissions.view', 'Visualizar permissões'],
        ];

        foreach ($permissions as $permissionId => [$slug, $name]) {
            DB::table('permissions')->updateOrInsert(['slug' => $slug], [
                'id' => $permissionId,
                'name' => $name,
                'description' => 'Permissão da fase 02.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('role_permissions')->updateOrInsert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
            ], [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', [
            'users.view', 'users.manage', 'farms.view', 'permissions.view',
        ])->delete();
    }
};
