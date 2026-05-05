<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;
    use HasUuids;

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'inventory_item_id', 'source_event_id', 'direction', 'reason', 'quantity', 'unit', 'unit_cost', 'total_cost', 'moved_on', 'movable_type', 'movable_id', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4', 'unit_cost' => 'decimal:4', 'total_cost' => 'decimal:4', 'moved_on' => 'date', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function sourceEvent(): BelongsTo { return $this->belongsTo(DomainEvent::class, 'source_event_id'); }
    public function movable(): MorphTo { return $this->morphTo(); }
}
