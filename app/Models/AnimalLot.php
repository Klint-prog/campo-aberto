<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalLot extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id', 'farm_id', 'pasture_id', 'name', 'code', 'species', 'purpose', 'status', 'started_on', 'closed_on', 'notes', 'metadata',
    ];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'closed_on' => 'date', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function pasture(): BelongsTo { return $this->belongsTo(Pasture::class); }
    public function animals(): HasMany { return $this->hasMany(Animal::class); }
    public function healthRecords(): HasMany { return $this->hasMany(AnimalHealthRecord::class); }
    public function vaccinationRecords(): HasMany { return $this->hasMany(AnimalVaccinationRecord::class); }
    public function feedConsumptions(): HasMany { return $this->hasMany(AnimalFeedConsumption::class); }
}
