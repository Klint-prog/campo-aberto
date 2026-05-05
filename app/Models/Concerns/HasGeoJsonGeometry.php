<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait HasGeoJsonGeometry
{
    public function getGeoJsonAttribute(): ?array
    {
        if (! $this->exists) {
            return null;
        }

        $geojson = DB::table($this->getTable())
            ->where('id', $this->getKey())
            ->value(DB::raw('ST_AsGeoJSON(geom)::json'));

        return $geojson ? json_decode((string) $geojson, true) : null;
    }

    public function scopeWithinFarm($query, string $farmId)
    {
        return $query->where($this->getTable().'.farm_id', $farmId);
    }

    public function scopeWithinTenant($query, string $tenantId)
    {
        return $query->where($this->getTable().'.tenant_id', $tenantId);
    }
}
