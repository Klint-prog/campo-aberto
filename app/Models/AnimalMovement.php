<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalMovement extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SALE = 'sale';
    public const TYPE_TRANSFER = 'transfer';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'animal_id', 'from_animal_lot_id', 'to_animal_lot_id', 'from_pasture_id', 'to_pasture_id', 'type', 'moved_on', 'amount', 'document', 'counterparty', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['moved_on' => 'date', 'amount' => 'decimal:2', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function animal(): BelongsTo { return $this->belongsTo(Animal::class); }
    public function fromLot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'from_animal_lot_id'); }
    public function toLot(): BelongsTo { return $this->belongsTo(AnimalLot::class, 'to_animal_lot_id'); }
    public function fromPasture(): BelongsTo { return $this->belongsTo(Pasture::class, 'from_pasture_id'); }
    public function toPasture(): BelongsTo { return $this->belongsTo(Pasture::class, 'to_pasture_id'); }
}
