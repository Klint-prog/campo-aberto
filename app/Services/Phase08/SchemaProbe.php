<?php

namespace App\Services\Phase08;

use Illuminate\Support\Facades\Schema;

class SchemaProbe
{
    public static function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
