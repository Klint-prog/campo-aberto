<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    protected $fillable = [
        'ai_consultation_id',
        'tenant_id',
        'farm_id',
        'user_id',
        'recommendation',
        'risk_level',
        'critical_action',
        'requires_confirmation',
        'safety_disclaimer',
        'recommended_at',
    ];

    protected $casts = [
        'critical_action' => 'boolean',
        'requires_confirmation' => 'boolean',
        'safety_disclaimer' => 'array',
        'recommended_at' => 'datetime',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(AiConsultation::class, 'ai_consultation_id');
    }
}
