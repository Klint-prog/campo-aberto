<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use App\Support\LivestockDomainEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalFeedConsumption extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'animal_id', 'animal_lot_id', 'feed_stock_id', 'consumed_on', 'feed_name', 'quantity', 'unit', 'unit_cost', 'total_cost', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['consumed_on' => 'date', 'quantity' => 'decimal:4', 'unit_cost' => 'decimal:4', 'total_cost' => 'decimal:4', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::created(fn (self $consumption) => $consumption->recordDomainEvent(LivestockDomainEvent::ANIMAL_FEED_CONSUMED, $consumption->eventPayload()));
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function animal(): BelongsTo { return $this->belongsTo(Animal::class); }
    public function lot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'animal_lot_id'); }

    public function eventPayload(): array
    {
        return ['feed_consumption_id' => $this->getKey(), 'animal_id' => $this->animal_id, 'animal_lot_id' => $this->animal_lot_id, 'feed_stock_id' => $this->feed_stock_id, 'farm_id' => $this->farm_id, 'feed_name' => $this->feed_name, 'quantity' => $this->quantity, 'unit' => $this->unit, 'total_cost' => $this->total_cost];
    }
}
