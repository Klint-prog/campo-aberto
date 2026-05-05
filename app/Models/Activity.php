<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'plot_id',
        'season_id',
        'crop_id',
        'crop_variety_id',
        'type',
        'status',
        'title',
        'description',
        'planned_start_on',
        'planned_end_on',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'planned_area_ha',
        'actual_area_ha',
        'estimated_productivity_kg_ha',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'planned_start_on' => 'date',
            'planned_end_on' => 'date',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'planned_area_ha' => 'decimal:4',
            'actual_area_ha' => 'decimal:4',
            'estimated_productivity_kg_ha' => 'decimal:2',
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

    public function inputs(): HasMany
    {
        return $this->hasMany(ActivityInput::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function markCompleted(?Carbon $completedAt = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => $completedAt ?? now(),
        ])->save();

        $this->recordDomainEvent('activity.completed', $this->eventPayload());
    }

    public function cancel(?string $reason = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ])->save();

        $this->recordDomainEvent('activity.cancelled', $this->eventPayload(['reason' => $reason]));
    }

    public function eventPayload(array $extra = []): array
    {
        return array_merge([
            'activity_id' => $this->getKey(),
            'type' => $this->type,
            'status' => $this->status,
            'farm_id' => $this->farm_id,
            'plot_id' => $this->plot_id,
            'season_id' => $this->season_id,
            'crop_id' => $this->crop_id,
            'crop_variety_id' => $this->crop_variety_id,
        ], $extra);
    }
}
