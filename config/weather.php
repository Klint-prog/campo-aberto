<?php

return [
    'default' => env('WEATHER_PROVIDER', 'open_meteo'),

    'cache_ttl_seconds' => (int) env('WEATHER_CACHE_TTL_SECONDS', 1800),

    'providers' => [
        'open_meteo' => [
            'base_url' => env('OPEN_METEO_BASE_URL', 'https://api.open-meteo.com/v1/forecast'),
            'timeout' => (int) env('OPEN_METEO_TIMEOUT', 8),
        ],

        'inmet' => [
            'enabled' => (bool) env('INMET_ENABLED', false),
            'base_url' => env('INMET_BASE_URL'),
        ],

        'openweathermap' => [
            'enabled' => (bool) env('OPENWEATHERMAP_ENABLED', false),
            'api_key' => env('OPENWEATHERMAP_API_KEY'),
        ],

        'weatherapi' => [
            'enabled' => (bool) env('WEATHERAPI_ENABLED', false),
            'api_key' => env('WEATHERAPI_API_KEY'),
        ],
    ],

    'alert_thresholds' => [
        'heavy_rain_mm_day' => (float) env('WEATHER_HEAVY_RAIN_MM_DAY', 50),
        'strong_wind_kmh' => (float) env('WEATHER_STRONG_WIND_KMH', 45),
        'heat_index_celsius' => (float) env('WEATHER_HEAT_INDEX_CELSIUS', 38),
    ],
];
