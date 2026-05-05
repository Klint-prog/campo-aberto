<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'machine_id', 'type', 'status', 'scheduled_on', 'performed_on', 'description', 'hour_meter', 'odometer_km', 'cost', 'supplier', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['scheduled_on' => 'date', 'performed_on' => 'date', 'hour_meter' => 'decimal:2', 'odometer_km' => 'decimal:2', 'cost' => 'decimal:2', 'metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::created(function (self $record): void {
            if ($record->cost !== null && (float) $record->cost > 0) {
                $record->recordDomainEvent('maintenance.performed', $record->eventPayload());
            }
        });
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function machine(): BelongsTo { return $this->belongsTo(Machine::class); }

    public function eventPayload(): array
    {
        return ['maintenance_record_id' => $this->getKey(), 'machine_id' => $this->machine_id, 'farm_id' => $this->farm_id, 'type' => $this->type, 'status' => $this->status, 'performed_on' => $this->performed_on?->toDateString(), 'description' => $this->description, 'cost' => $this->cost, 'supplier' => $this->supplier];
    }
}
