<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        foreach (['tenant_id', 'farm_id', 'user_id'] as $column) {
            if (! Schema::hasColumn('audit_logs', $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE audit_logs ALTER COLUMN %s TYPE varchar(64) USING %s::text',
                $column,
                $column,
            ));
        }
    }

    public function down(): void
    {
        // Mantém texto para compatibilidade com UUIDs reais e IDs numéricos usados nos testes de escopo.
    }
};
