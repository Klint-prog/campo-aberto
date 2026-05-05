<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSnapshot extends Model
{
    protected $fillable = [
        'tenant_id',
        'farm_id',
        'dashboard_key',
        'metrics',
        'calculated_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'calculated_at' => 'datetime',
    ];
}
