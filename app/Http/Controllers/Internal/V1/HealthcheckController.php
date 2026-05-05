<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthcheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => true,
            'database' => $this->databaseAvailable(),
            'cache' => $this->cacheAvailable(),
            'storage' => $this->storageWritable(),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $healthy ? 200 : 503);
    }

    private function databaseAvailable(): bool
    {
        try {
            DB::select('select 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function cacheAvailable(): bool
    {
        try {
            Cache::put('healthcheck:last_seen_at', now()->toISOString(), 30);
            return Cache::has('healthcheck:last_seen_at');
        } catch (\Throwable) {
            return false;
        }
    }

    private function storageWritable(): bool
    {
        try {
            $path = 'healthcheck/.probe';
            Storage::disk('local')->put($path, now()->toISOString());
            Storage::disk('local')->delete($path);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
