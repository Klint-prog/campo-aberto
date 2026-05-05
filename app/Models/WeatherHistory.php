<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'observed_on',
        'source',
        'min_temperature_celsius',
        'max_temperature_celsius',
        'precipitation_mm',
        'relative_humidity_percent',
        'wind_speed_kmh',
        'raw_payload',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'farm_id' => 'integer',
        'observed_on' => 'date',
        'min_temperature_celsius' => 'decimal:2',
        'max_temperature_celsius' => 'decimal:2',
        'precipitation_mm' => 'decimal:2',
        'relative_humidity_percent' => 'decimal:2',
        'wind_speed_kmh' => 'decimal:2',
        'raw_payload' => 'array',
    ];
}
