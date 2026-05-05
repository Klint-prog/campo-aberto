<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Farm extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'city',
        'state',
        'total_area_ha',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_area_ha' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'farm_users')
            ->withPivot(['id', 'tenant_id', 'role_id', 'is_active'])
            ->withTimestamps();
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    public function pastures(): HasMany
    {
        return $this->hasMany(Pasture::class);
    }

    public function mapFeatures(): HasMany
    {
        return $this->hasMany(MapFeature::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function domainEvents(): HasMany
    {
        return $this->hasMany(DomainEvent::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function getBoundaryGeoJsonAttribute(): ?array
    {
        if (! $this->exists) {
            return null;
        }

        $geojson = DB::table($this->getTable())
            ->where('id', $this->getKey())
            ->value(DB::raw('ST_AsGeoJSON(boundary)::json'));

        return $geojson ? json_decode((string) $geojson, true) : null;
    }
}
