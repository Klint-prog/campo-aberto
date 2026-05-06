<?php

return [
    'online_tiles' => [
        'url' => env('MAP_ONLINE_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('MAP_ONLINE_TILE_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
        'min_zoom' => (int) env('MAP_ONLINE_MIN_ZOOM', 0),
        'max_zoom' => (int) env('MAP_ONLINE_MAX_ZOOM', 19),
        'max_native_zoom' => (int) env('MAP_ONLINE_MAX_NATIVE_ZOOM', 19),
    ],

    'default_center' => [
        'latitude' => (float) env('MAP_DEFAULT_LATITUDE', -8.05),
        'longitude' => (float) env('MAP_DEFAULT_LONGITUDE', -34.9),
        'zoom' => (int) env('MAP_DEFAULT_ZOOM', 6),
    ],
];
