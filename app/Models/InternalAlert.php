<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'type',
        'severity',
        'title',
        'message',
        'context',
        'due_at',
        'read_at',
        'resolved_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'farm_id' => 'integer',
        'context' => 'array',
        'due_at' => 'datetime',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
