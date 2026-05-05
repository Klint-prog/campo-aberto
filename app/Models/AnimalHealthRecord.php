<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use App\Support\LivestockDomainEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalHealthRecord extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'animal_id', 'animal_lot_id', 'type', 'recorded_on', 'diagnosis', 'medicine_name', 'medicine_reference', 'dosage', 'dosage_unit', 'quantity_consumed', 'quantity_unit', 'withdrawal_period', 'responsible', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['recorded_on' => 'date', 'dosage' => 'decimal:4', 'quantity_consumed' => 'decimal:4', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::created(fn (self $record) => $record->recordDomainEvent(LivestockDomainEvent::ANIMAL_TREATMENT_RECORDED, $record->eventPayload()));
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function animal(): BelongsTo { return $this->belongsTo(Animal::class); }
    public function lot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'animal_lot_id'); }

    public function eventPayload(): array
    {
        return ['health_record_id' => $this->getKey(), 'animal_id' => $this->animal_id, 'animal_lot_id' => $this->animal_lot_id, 'farm_id' => $this->farm_id, 'type' => $this->type, 'medicine_name' => $this->medicine_name, 'medicine_reference' => $this->medicine_reference, 'quantity_consumed' => $this->quantity_consumed, 'quantity_unit' => $this->quantity_unit];
    }
}
