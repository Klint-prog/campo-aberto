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

        DB::table('tenants')->updateOrInsert(['id' => '00000000-0000-0000-0000-000000000001'], [
            'name' => 'Campo Aberto Demonstração',
            'slug' => 'campo-aberto-demo',
            'document' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->updateOrInsert(['id' => '00000000-0000-0000-0000-000000000002'], [
            'tenant_id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Administrador Campo Aberto',
            'email' => 'admin@campoaberto.local',
            'email_verified_at' => $now,
            'password' => Hash::make('CampoAberto@2026'),
            'remember_token' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
