<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiConsultation extends Model
{
    protected $fillable = [
        'tenant_id',
        'farm_id',
        'user_id',
        'use_case',
        'question',
        'context',
        'status',
        'consulted_at',
    ];

    protected $casts = [
        'context' => 'array',
        'consulted_at' => 'datetime',
    ];

    public function recommendation(): HasOne
    {
        return $this->hasOne(AiRecommendation::class);
    }
}
