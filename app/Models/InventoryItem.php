<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'name', 'type', 'sku', 'unit', 'current_quantity', 'minimum_quantity', 'unit_cost', 'supplier', 'batch_number', 'expires_on', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['current_quantity' => 'decimal:4', 'minimum_quantity' => 'decimal:4', 'unit_cost' => 'decimal:4', 'expires_on' => 'date', 'is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function movements(): HasMany { return $this->hasMany(StockMovement::class); }
}
