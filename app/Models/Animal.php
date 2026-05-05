<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use App\Support\LivestockDomainEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Animal extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SOLD = 'sold';
    public const STATUS_DEAD = 'dead';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'farm_id', 'animal_lot_id', 'internal_code', 'ear_tag', 'rfid', 'name', 'species', 'breed', 'sex', 'birth_date', 'status', 'birth_weight_kg', 'acquired_on', 'purchase_price', 'purchase_document', 'origin', 'sold_on', 'sale_price', 'sale_document', 'died_on', 'death_cause', 'metadata', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'birth_weight_kg' => 'decimal:3', 'acquired_on' => 'date', 'purchase_price' => 'decimal:2', 'sold_on' => 'date', 'sale_price' => 'decimal:2', 'died_on' => 'date', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function lot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'animal_lot_id'); }
    public function weightRecords(): HasMany { return $this->hasMany(AnimalWeightRecord::class); }
    public function healthRecords(): HasMany { return $this->hasMany(AnimalHealthRecord::class); }
    public function vaccinationRecords(): HasMany { return $this->hasMany(AnimalVaccinationRecord::class); }
    public function movements(): HasMany { return $this->hasMany(AnimalMovement::class); }
    public function feedConsumptions(): HasMany { return $this->hasMany(AnimalFeedConsumption::class); }

    public function sell(float|string $amount, ?string $document = null, ?string $counterparty = null, ?Carbon $soldOn = null): AnimalMovement
    {
        $soldOn ??= now();
        $this->forceFill(['status' => self::STATUS_SOLD, 'sold_on' => $soldOn->toDateString(), 'sale_price' => $amount, 'sale_document' => $document])->save();

        $movement = $this->movements()->create(['tenant_id' => $this->tenant_id, 'farm_id' => $this->farm_id, 'type' => AnimalMovement::TYPE_SALE, 'moved_on' => $soldOn->toDateString(), 'amount' => $amount, 'document' => $document, 'counterparty' => $counterparty]);
        $this->recordDomainEvent(LivestockDomainEvent::ANIMAL_SOLD, $this->eventPayload(['sale_price' => $amount, 'document' => $document, 'counterparty' => $counterparty]));

        return $movement;
    }

    public function die(?string $cause = null, ?Carbon $diedOn = null): void
    {
        $diedOn ??= now();
        $this->forceFill(['status' => self::STATUS_DEAD, 'died_on' => $diedOn->toDateString(), 'death_cause' => $cause])->save();
        $this->recordDomainEvent(LivestockDomainEvent::ANIMAL_DIED, $this->eventPayload(['death_cause' => $cause]));
    }

    public function eventPayload(array $extra = []): array
    {
        return array_merge(['animal_id' => $this->getKey(), 'farm_id' => $this->farm_id, 'animal_lot_id' => $this->animal_lot_id, 'internal_code' => $this->internal_code, 'ear_tag' => $this->ear_tag, 'rfid' => $this->rfid, 'species' => $this->species, 'status' => $this->status], $extra);
    }
}
