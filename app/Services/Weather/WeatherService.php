<?php

namespace App\Services\Weather;

use App\Models\InternalAlert;
use App\Models\Notification;
use App\Models\WeatherForecast;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class WeatherService
{
    public function __construct(private readonly OpenMeteoClient $openMeteoClient)
    {
    }

    public function forecastForFarm(array $scope, bool $refresh = false): array
    {
        $tenantId = (int) $scope['tenant_id'];
        $farmId = (int) $scope['farm_id'];
        $latitude = (float) $scope['latitude'];
        $longitude = (float) $scope['longitude'];
        $timezone = $scope['timezone'] ?? 'America/Sao_Paulo';
        $provider = config('weather.default', 'open_meteo');
        $cacheKey = $this->cacheKey($tenantId, $farmId, $provider, $latitude, $longitude);
        $ttl = (int) config('weather.cache_ttl_seconds', 1800);

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($tenantId, $farmId, $latitude, $longitude, $timezone, $provider) {
            if ($provider !== 'open_meteo') {
                throw new RuntimeException('Provedor climático configurado ainda não implementado para execução direta.');
            }

            $payload = $this->openMeteoClient->forecast($latitude, $longitude, $timezone);
            $normalized = $this->normalizeOpenMeteo($payload, $tenantId, $farmId, $latitude, $longitude, $timezone, $provider);

            foreach ($normalized['daily'] as $item) {
                WeatherForecast::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'farm_id' => $farmId,
                        'provider' => $provider,
                        'forecast_at' => $item['forecast_at'],
                        'period' => 'daily',
                    ],
                    $item
                );
            }

            foreach ($normalized['hourly'] as $item) {
                WeatherForecast::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'farm_id' => $farmId,
                        'provider' => $provider,
                        'forecast_at' => $item['forecast_at'],
                        'period' => 'hourly',
                    ],
                    $item
                );
            }

            $alerts = $this->generateWeatherAlerts($tenantId, $farmId, $normalized['daily']);

            return [
                'provider' => $provider,
                'cache_key' => $this->cacheKey($tenantId, $farmId, $provider, $latitude, $longitude),
                'cached' => false,
                'daily' => $normalized['daily'],
                'hourly' => $normalized['hourly'],
                'alerts' => $alerts,
            ];
        }) + ['cached' => true];
    }

    private function normalizeOpenMeteo(array $payload, int $tenantId, int $farmId, float $latitude, float $longitude, string $timezone, string $provider): array
    {
        $daily = [];
        $dailyTimes = Arr::get($payload, 'daily.time', []);

        foreach ($dailyTimes as $index => $date) {
            $maxTemp = Arr::get($payload, "daily.temperature_2m_max.$index");
            $minTemp = Arr::get($payload, "daily.temperature_2m_min.$index");
            $temperature = $maxTemp !== null && $minTemp !== null ? round(((float) $maxTemp + (float) $minTemp) / 2, 2) : null;
            $wind = Arr::get($payload, "daily.wind_speed_10m_max.$index");

            $daily[] = [
                'tenant_id' => $tenantId,
                'farm_id' => $farmId,
                'provider' => $provider,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'forecast_at' => CarbonImmutable::parse($date, $timezone)->startOfDay(),
                'period' => 'daily',
                'temperature_celsius' => $temperature,
                'precipitation_mm' => Arr::get($payload, "daily.precipitation_sum.$index"),
                'relative_humidity_percent' => null,
                'wind_speed_kmh' => $wind,
                'solar_radiation_wm2' => null,
                'heat_index_celsius' => $temperature,
                'raw_payload' => ['daily_index' => $index],
                'fetched_at' => now(),
            ];
        }

        $hourly = [];
        $hourlyTimes = Arr::get($payload, 'hourly.time', []);

        foreach (array_slice($hourlyTimes, 0, 48) as $index => $time) {
            $temperature = Arr::get($payload, "hourly.temperature_2m.$index");
            $humidity = Arr::get($payload, "hourly.relative_humidity_2m.$index");

            $hourly[] = [
                'tenant_id' => $tenantId,
                'farm_id' => $farmId,
                'provider' => $provider,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'forecast_at' => CarbonImmutable::parse($time, $timezone),
                'period' => 'hourly',
                'temperature_celsius' => $temperature,
                'precipitation_mm' => Arr::get($payload, "hourly.precipitation.$index"),
                'relative_humidity_percent' => $humidity,
                'wind_speed_kmh' => Arr::get($payload, "hourly.wind_speed_10m.$index"),
                'solar_radiation_wm2' => Arr::get($payload, "hourly.shortwave_radiation.$index"),
                'heat_index_celsius' => $this->heatIndex($temperature, $humidity),
                'raw_payload' => ['hourly_index' => $index],
                'fetched_at' => now(),
            ];
        }

        return ['daily' => $daily, 'hourly' => $hourly];
    }

    private function generateWeatherAlerts(int $tenantId, int $farmId, array $dailyForecasts): array
    {
        $created = [];
        $heavyRain = (float) config('weather.alert_thresholds.heavy_rain_mm_day', 50);
        $strongWind = (float) config('weather.alert_thresholds.strong_wind_kmh', 45);

        foreach ($dailyForecasts as $forecast) {
            $messages = [];

            if (($forecast['precipitation_mm'] ?? 0) >= $heavyRain) {
                $messages[] = 'Chuva forte prevista para a fazenda.';
            }

            if (($forecast['wind_speed_kmh'] ?? 0) >= $strongWind) {
                $messages[] = 'Vento forte previsto para a fazenda.';
            }

            foreach ($messages as $message) {
                $alert = InternalAlert::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'farm_id' => $farmId,
                        'type' => 'weather',
                        'title' => 'Alerta climático',
                        'due_at' => $forecast['forecast_at'],
                    ],
                    [
                        'severity' => 'warning',
                        'message' => $message,
                        'context' => [
                            'precipitation_mm' => $forecast['precipitation_mm'] ?? null,
                            'wind_speed_kmh' => $forecast['wind_speed_kmh'] ?? null,
                        ],
                    ]
                );

                Notification::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'farm_id' => $farmId,
                        'internal_alert_id' => $alert->id,
                        'channel' => 'internal',
                    ],
                    [
                        'status' => 'pending',
                        'title' => $alert->title,
                        'message' => $alert->message,
                        'payload' => $alert->context,
                    ]
                );

                $created[] = $alert->fresh();
            }
        }

        return $created;
    }

    private function heatIndex(mixed $temperature, mixed $humidity): ?float
    {
        if ($temperature === null || $humidity === null) {
            return $temperature !== null ? (float) $temperature : null;
        }

        $temp = (float) $temperature;
        $hum = (float) $humidity;

        if ($temp < 27) {
            return round($temp, 2);
        }

        return round($temp + (0.05 * $hum), 2);
    }

    private function cacheKey(int $tenantId, int $farmId, string $provider, float $latitude, float $longitude): string
    {
        return sprintf('weather:%d:%d:%s:%0.4f:%0.4f', $tenantId, $farmId, $provider, $latitude, $longitude);
    }
}
