<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'provider',
        'latitude',
        'longitude',
        'timezone',
        'forecast_at',
        'period',
        'temperature_celsius',
        'precipitation_mm',
        'relative_humidity_percent',
        'wind_speed_kmh',
        'solar_radiation_wm2',
        'heat_index_celsius',
        'raw_payload',
        'fetched_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'farm_id' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'forecast_at' => 'datetime',
        'temperature_celsius' => 'decimal:2',
        'precipitation_mm' => 'decimal:2',
        'relative_humidity_percent' => 'decimal:2',
        'wind_speed_kmh' => 'decimal:2',
        'solar_radiation_wm2' => 'decimal:2',
        'heat_index_celsius' => 'decimal:2',
        'raw_payload' => 'array',
        'fetched_at' => 'datetime',
    ];
}
