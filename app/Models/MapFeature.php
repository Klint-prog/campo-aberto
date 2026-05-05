<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MapFeature extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'name',
        'type',
        'properties',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

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
}
