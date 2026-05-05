<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedStock extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'feed_stock';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'name', 'type', 'unit', 'current_quantity', 'unit_cost', 'metadata'];

    protected function casts(): array
    {
        return ['current_quantity' => 'decimal:4', 'unit_cost' => 'decimal:4', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
}
