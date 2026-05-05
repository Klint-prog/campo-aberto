<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'name', 'code', 'type', 'brand', 'model', 'manufacture_year', 'hour_meter', 'odometer_km', 'fuel_type', 'operational_status', 'metadata'];

    protected function casts(): array
    {
        return ['manufacture_year' => 'integer', 'hour_meter' => 'decimal:2', 'odometer_km' => 'decimal:2', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function maintenanceRecords(): HasMany { return $this->hasMany(MaintenanceRecord::class); }
}
