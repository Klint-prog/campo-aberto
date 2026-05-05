<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ManualRainRecord;
use App\Models\WeatherForecast;
use App\Models\WeatherHistory;
use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WeatherController extends Controller
{
    public function forecast(Request $request, WeatherService $weatherService): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['required', 'integer', 'min:1'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['sometimes', 'string', 'max:80'],
            'refresh' => ['sometimes', 'boolean'],
        ]);

        try {
            return response()->json([
                'data' => $weatherService->forecastForFarm($data, (bool) ($data['refresh'] ?? false)),
            ]);
        } catch (RuntimeException $exception) {
            $cached = WeatherForecast::query()
                ->where('tenant_id', $data['tenant_id'])
                ->where('farm_id', $data['farm_id'])
                ->orderByDesc('forecast_at')
                ->limit(56)
                ->get();

            return response()->json([
                'message' => 'Falha ao consultar API climática externa. A aplicação permaneceu disponível.',
                'error' => $exception->getMessage(),
                'fallback' => [
                    'source' => $cached->isNotEmpty() ? 'database_cache' : 'empty',
                    'forecasts' => $cached,
                ],
            ], 503);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['required', 'integer', 'min:1'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
        ]);

        $query = WeatherHistory::query()
            ->where('tenant_id', $data['tenant_id'])
            ->where('farm_id', $data['farm_id'])
            ->orderByDesc('observed_on');

        if (! empty($data['from'])) {
            $query->whereDate('observed_on', '>=', $data['from']);
        }

        if (! empty($data['to'])) {
            $query->whereDate('observed_on', '<=', $data['to']);
        }

        return response()->json(['data' => $query->paginate(30)]);
    }

    public function storeManualRain(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'min:1'],
            'farm_id' => ['required', 'integer', 'min:1'],
            'measured_on' => ['required', 'date'],
            'amount_mm' => ['required', 'numeric', 'min:0', 'max:2000'],
            'gauge_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'recorded_by' => ['nullable', 'integer', 'min:1'],
        ]);

        $record = ManualRainRecord::create($data);

        WeatherHistory::updateOrCreate(
            [
                'tenant_id' => $record->tenant_id,
                'farm_id' => $record->farm_id,
                'observed_on' => $record->measured_on,
                'source' => 'manual_rain_gauge',
            ],
            [
                'precipitation_mm' => $record->amount_mm,
                'raw_payload' => [
                    'manual_rain_record_id' => $record->id,
                    'gauge_name' => $record->gauge_name,
                ],
            ]
        );

        return response()->json(['data' => $record], 201);
    }
}
