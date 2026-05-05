<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualRainRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'measured_on',
        'amount_mm',
        'gauge_name',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'farm_id' => 'integer',
        'measured_on' => 'date',
        'amount_mm' => 'decimal:2',
        'recorded_by' => 'integer',
    ];
}
