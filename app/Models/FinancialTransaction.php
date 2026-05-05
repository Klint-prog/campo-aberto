<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'financial_account_id', 'financial_category_id', 'source_event_id', 'type', 'status', 'description', 'amount', 'due_on', 'paid_on', 'transactionable_type', 'transactionable_id', 'season_id', 'plot_id', 'animal_lot_id', 'machine_id', 'activity_id', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_on' => 'date', 'paid_on' => 'date', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function account(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'financial_account_id'); }
    public function category(): BelongsTo { return $this->belongsTo(FinancialCategory::class, 'financial_category_id'); }
    public function sourceEvent(): BelongsTo { return $this->belongsTo(DomainEvent::class, 'source_event_id'); }
    public function transactionable(): MorphTo { return $this->morphTo(); }
}
