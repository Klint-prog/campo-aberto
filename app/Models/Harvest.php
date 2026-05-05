<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Harvest extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'plot_id',
        'season_id',
        'crop_id',
        'crop_variety_id',
        'activity_id',
        'harvested_on',
        'harvested_area_ha',
        'total_weight_kg',
        'productivity_kg_ha',
        'quality_grade',
        'metadata',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::saving(function (Harvest $harvest): void {
            if ((float) $harvest->harvested_area_ha > 0) {
                $harvest->productivity_kg_ha = round((float) $harvest->total_weight_kg / (float) $harvest->harvested_area_ha, 4);
            }
        });

        static::created(function (Harvest $harvest): void {
            $harvest->recordDomainEvent('harvest.recorded', [
                'harvest_id' => $harvest->getKey(),
                'farm_id' => $harvest->farm_id,
                'plot_id' => $harvest->plot_id,
                'season_id' => $harvest->season_id,
                'crop_id' => $harvest->crop_id,
                'crop_variety_id' => $harvest->crop_variety_id,
                'activity_id' => $harvest->activity_id,
                'harvested_area_ha' => $harvest->harvested_area_ha,
                'total_weight_kg' => $harvest->total_weight_kg,
                'productivity_kg_ha' => $harvest->productivity_kg_ha,
            ]);
        });
    }

    protected function casts(): array
    {
        return [
            'harvested_on' => 'date',
            'harvested_area_ha' => 'decimal:4',
            'total_weight_kg' => 'decimal:4',
            'productivity_kg_ha' => 'decimal:4',
            'metadata' => 'array',
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

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function cropVariety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
