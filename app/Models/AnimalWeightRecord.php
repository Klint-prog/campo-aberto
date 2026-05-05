<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use App\Support\LivestockDomainEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalWeightRecord extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'animal_id', 'weighed_on', 'weight_kg', 'average_daily_gain_kg', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['weighed_on' => 'date', 'weight_kg' => 'decimal:3', 'average_daily_gain_kg' => 'decimal:4', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::created(fn (self $record) => $record->recordDomainEvent(LivestockDomainEvent::ANIMAL_WEIGHT_RECORDED, $record->eventPayload()));
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function animal(): BelongsTo { return $this->belongsTo(Animal::class); }

    public function eventPayload(): array
    {
        return ['weight_record_id' => $this->getKey(), 'animal_id' => $this->animal_id, 'farm_id' => $this->farm_id, 'weighed_on' => $this->weighed_on?->toDateString(), 'weight_kg' => $this->weight_kg, 'average_daily_gain_kg' => $this->average_daily_gain_kg];
    }
}
