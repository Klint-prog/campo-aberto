<?php

namespace App\Models;

use App\Models\Concerns\RecordsDomainEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityInput extends Model
{
    use HasFactory;
    use HasUuids;
    use RecordsDomainEvents;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'activity_id',
        'input_name',
        'input_type',
        'input_reference',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::created(function (ActivityInput $input): void {
            $input->recordDomainEvent('input.consumed_by_activity', [
                'activity_input_id' => $input->getKey(),
                'activity_id' => $input->activity_id,
                'input_name' => $input->input_name,
                'input_type' => $input->input_type,
                'input_reference' => $input->input_reference,
                'quantity' => $input->quantity,
                'unit' => $input->unit,
                'unit_cost' => $input->unit_cost,
                'total_cost' => $input->total_cost,
            ]);
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
