<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use App\Support\LivestockDomainEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalVaccinationRecord extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'animal_id', 'animal_lot_id', 'vaccinated_on', 'vaccine_name', 'vaccine_reference', 'batch_number', 'dose', 'dose_unit', 'next_due_on', 'responsible', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['vaccinated_on' => 'date', 'next_due_on' => 'date', 'dose' => 'decimal:4', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::created(fn (self $record) => $record->recordDomainEvent(LivestockDomainEvent::ANIMAL_VACCINATION_RECORDED, $record->eventPayload()));
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function animal(): BelongsTo { return $this->belongsTo(Animal::class); }
    public function lot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'animal_lot_id'); }

    public function eventPayload(): array
    {
        return ['vaccination_record_id' => $this->getKey(), 'animal_id' => $this->animal_id, 'animal_lot_id' => $this->animal_lot_id, 'farm_id' => $this->farm_id, 'vaccinated_on' => $this->vaccinated_on?->toDateString(), 'vaccine_name' => $this->vaccine_name, 'vaccine_reference' => $this->vaccine_reference, 'dose' => $this->dose, 'dose_unit' => $this->dose_unit];
    }
}
