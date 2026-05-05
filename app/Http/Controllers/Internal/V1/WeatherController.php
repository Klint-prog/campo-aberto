<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function forecast(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required'],
            'farm_id' => ['required'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $tenantId = (int) $validated['tenant_id'];
        $farmId = (int) $validated['farm_id'];
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        $cached = DB::table('weather_forecasts')
            ->where('tenant_id', $tenantId)
            ->where('farm_id', $farmId)
            ->where('provider', 'open_meteo')
            ->latest('fetched_at')
            ->first();

        if ($cached) {
            return response()->json([
                'data' => $this->responseData($cached, json_decode((string) $cached->raw_payload, true) ?: [], true),
            ]);
        }

        $response = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => 'temperature_2m,precipitation,relative_humidity_2m,wind_speed_10m,shortwave_radiation',
            'timezone' => 'America/Sao_Paulo',
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Falha ao consultar API climática externa. A aplicação permaneceu disponível.',
                'fallback' => ['source' => 'empty'],
            ], 503);
        }

        $payload = $response->json();
        $hourly = $payload['hourly'] ?? [];
        $time = $hourly['time'][0] ?? now()->toDateTimeString();
        $forecastAt = Carbon::parse($time);

        $row = [
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'provider' => 'open_meteo',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $payload['timezone'] ?? 'America/Sao_Paulo',
            'forecast_at' => $forecastAt,
            'period' => 'hourly',
            'temperature_celsius' => $this->numberFrom($hourly, 'temperature_2m'),
            'precipitation_mm' => $this->numberFrom($hourly, 'precipitation'),
            'relative_humidity_percent' => $this->numberFrom($hourly, 'relative_humidity_2m'),
            'wind_speed_kmh' => $this->numberFrom($hourly, 'wind_speed_10m'),
            'solar_radiation_wm2' => $this->numberFrom($hourly, 'shortwave_radiation'),
            'heat_index_celsius' => null,
            'raw_payload' => json_encode($payload),
            'fetched_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('weather_forecasts')->updateOrInsert([
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'provider' => 'open_meteo',
            'forecast_at' => $forecastAt,
            'period' => 'hourly',
        ], $row);

        $stored = DB::table('weather_forecasts')
            ->where('tenant_id', $tenantId)
            ->where('farm_id', $farmId)
            ->where('provider', 'open_meteo')
            ->where('forecast_at', $forecastAt)
            ->where('period', 'hourly')
            ->first();

        return response()->json([
            'data' => $this->responseData($stored, $payload, false),
        ]);
    }

    public function manualRain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'farm_id' => ['required', 'integer'],
            'measured_on' => ['required', 'date'],
            'amount_mm' => ['required', 'numeric'],
            'recorded_by' => ['nullable', 'integer'],
            'gauge_name' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $id = DB::table('manual_rain_records')->insertGetId([
            ...$validated,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => DB::table('manual_rain_records')->find($id),
        ], 201);
    }

    private function responseData(object $forecast, array $payload, bool $cached): array
    {
        $precipitation = (float) ($forecast->precipitation_mm ?? 0);
        $alerts = [];

        if ($precipitation >= 1.0) {
            $alerts[] = [
                'type' => 'rain_forecast',
                'severity' => $precipitation >= 20.0 ? 'warning' : 'info',
                'message' => 'Previsão de chuva para a fazenda.',
            ];
        }

        if ($alerts === []) {
            $alerts[] = [
                'type' => 'weather_updated',
                'severity' => 'info',
                'message' => 'Previsão climática atualizada.',
            ];
        }

        return [
            'provider' => 'open_meteo',
            'tenant_id' => (int) $forecast->tenant_id,
            'farm_id' => (int) $forecast->farm_id,
            'latitude' => (float) $forecast->latitude,
            'longitude' => (float) $forecast->longitude,
            'forecast_at' => (string) $forecast->forecast_at,
            'period' => $forecast->period,
            'temperature_celsius' => $forecast->temperature_celsius !== null ? (float) $forecast->temperature_celsius : null,
            'precipitation_mm' => $forecast->precipitation_mm !== null ? (float) $forecast->precipitation_mm : null,
            'relative_humidity_percent' => $forecast->relative_humidity_percent !== null ? (float) $forecast->relative_humidity_percent : null,
            'wind_speed_kmh' => $forecast->wind_speed_kmh !== null ? (float) $forecast->wind_speed_kmh : null,
            'cached' => $cached,
            'alerts' => $alerts,
            'raw' => $payload,
        ];
    }

    private function numberFrom(array $hourly, string $key): ?float
    {
        $value = $hourly[$key][0] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }
}
