<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineMapPackage extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'name',
        'status',
        'tile_format',
        'min_zoom',
        'max_zoom',
        'north',
        'south',
        'east',
        'west',
        'size_bytes',
        'storage_path',
        'generated_at',
    ];

    protected $casts = [
        'min_zoom' => 'integer',
        'max_zoom' => 'integer',
        'north' => 'decimal:7',
        'south' => 'decimal:7',
        'east' => 'decimal:7',
        'west' => 'decimal:7',
        'size_bytes' => 'integer',
        'generated_at' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
