<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalReproductionRecord extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tenant_id', 'farm_id', 'female_animal_id', 'male_animal_id', 'type', 'event_on', 'expected_birth_on', 'status', 'pregnancy_confirmed', 'pregnancy_checked_on', 'offspring_animal_id', 'notes', 'metadata', 'created_by'];

    protected function casts(): array
    {
        return ['event_on' => 'date', 'expected_birth_on' => 'date', 'pregnancy_confirmed' => 'boolean', 'pregnancy_checked_on' => 'date', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function farm(): BelongsTo { return $this->belongsTo(Farm::class); }
    public function female(): BelongsTo { return $this->belongsTo(Animal::class, 'female_animal_id'); }
    public function male(): BelongsTo { return $this->belongsTo(Animal::class, 'male_animal_id'); }
    public function offspring(): BelongsTo { return $this->belongsTo(Animal::class, 'offspring_animal_id'); }
}
