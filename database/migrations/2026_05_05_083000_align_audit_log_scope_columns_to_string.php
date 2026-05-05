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

            $this->dropForeignKeysForColumn('audit_logs', $column);

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

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $constraints = DB::select(<<<SQL
            SELECT tc.constraint_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name
             AND tc.table_schema = kcu.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
              AND tc.table_name = ?
              AND kcu.column_name = ?
        SQL, [$table, $column]);

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s',
                $table,
                $constraint->constraint_name,
            ));
        }
    }
};
