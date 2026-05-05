<?php

namespace App\Services\Weather;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenMeteoClient
{
    public function forecast(float $latitude, float $longitude, string $timezone = 'America/Sao_Paulo'): array
    {
        $config = config('weather.providers.open_meteo');

        try {
            $response = Http::timeout($config['timeout'] ?? 8)
                ->acceptJson()
                ->get($config['base_url'], [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'timezone' => $timezone,
                    'hourly' => implode(',', [
                        'temperature_2m',
                        'relative_humidity_2m',
                        'precipitation',
                        'wind_speed_10m',
                        'shortwave_radiation',
                    ]),
                    'daily' => implode(',', [
                        'temperature_2m_max',
                        'temperature_2m_min',
                        'precipitation_sum',
                        'wind_speed_10m_max',
                    ]),
                    'forecast_days' => 7,
                ]);

            $response->throw();

            return $response->json();
        } catch (ConnectionException|RequestException $exception) {
            throw new RuntimeException('Falha ao consultar a API climática externa.', previous: $exception);
        }
    }
}
