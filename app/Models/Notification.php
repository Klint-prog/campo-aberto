<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'user_id',
        'internal_alert_id',
        'channel',
        'status',
        'title',
        'message',
        'payload',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'farm_id' => 'integer',
        'user_id' => 'integer',
        'internal_alert_id' => 'integer',
        'payload' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(InternalAlert::class, 'internal_alert_id');
    }
}
