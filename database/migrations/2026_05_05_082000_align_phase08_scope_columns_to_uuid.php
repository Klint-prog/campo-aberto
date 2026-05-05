<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['report_exports', 'dashboard_snapshots', 'ai_consultations', 'ai_recommendations', 'ai_action_confirmations'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (['tenant_id', 'farm_id', 'user_id'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement(sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s TYPE varchar(64) USING %s::text',
                    $table,
                    $column,
                    $column,
                ));
            }
        }
    }

    public function down(): void
    {
        // Mantém colunas como texto para compatibilidade com UUIDs das fases anteriores.
    }
};
