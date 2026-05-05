<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('role_permissions', 'id')) {
            return;
        }

        DB::statement('ALTER TABLE role_permissions ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('role_permissions', 'id')) {
            return;
        }

        DB::statement('ALTER TABLE role_permissions ALTER COLUMN id DROP DEFAULT');
    }
};
