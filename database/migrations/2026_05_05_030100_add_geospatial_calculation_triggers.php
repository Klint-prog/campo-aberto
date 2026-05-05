<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION campo_aberto_update_area_perimeter()
            RETURNS trigger AS $$
            BEGIN
                IF NEW.geom IS NOT NULL THEN
                    NEW.geom := ST_Multi(ST_SetSRID(NEW.geom, 4326));
                    NEW.area_ha := ROUND((ST_Area(ST_Transform(NEW.geom, 5880)) / 10000.0)::numeric, 4);
                    NEW.perimeter_m := ROUND(ST_Perimeter(ST_Transform(NEW.geom, 5880))::numeric, 2);
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        foreach (['fields', 'plots', 'pastures'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_area_perimeter_trigger ON {$table}");
            DB::statement("CREATE TRIGGER {$table}_area_perimeter_trigger BEFORE INSERT OR UPDATE OF geom ON {$table} FOR EACH ROW EXECUTE FUNCTION campo_aberto_update_area_perimeter()");
        }
    }

    public function down(): void
    {
        foreach (['fields', 'plots', 'pastures'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_area_perimeter_trigger ON {$table}");
        }

        DB::statement('DROP FUNCTION IF EXISTS campo_aberto_update_area_perimeter()');
    }
};
