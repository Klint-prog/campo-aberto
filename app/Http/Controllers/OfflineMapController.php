<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\OfflineMapPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OfflineMapController extends Controller
{
    public function status(Request $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request, $farm);

        $package = OfflineMapPackage::query()
            ->where('tenant_id', $farm->tenant_id)
            ->where('farm_id', $farm->id)
            ->where('status', 'ready')
            ->latest('generated_at')
            ->latest()
            ->first();

        return response()->json([
            'data' => [
                'available' => $package !== null,
                'farm_id' => $farm->id,
                'package' => $package,
                'tile_url_template' => $package
                    ? route('farms.map.offline.tile', ['farm' => $farm, 'z' => '{z}', 'x' => '{x}', 'y' => '{y}'], false)
                    : null,
                'message' => $package
                    ? 'Mapa offline disponível para esta fazenda.'
                    : 'Mapa offline ainda não disponível para esta fazenda.',
            ],
        ]);
    }

    public function tile(Request $request, Farm $farm, int $z, int $x, string $y): Response
    {
        $this->authorizeFarm($request, $farm);

        $package = OfflineMapPackage::query()
            ->where('tenant_id', $farm->tenant_id)
            ->where('farm_id', $farm->id)
            ->where('status', 'ready')
            ->where('min_zoom', '<=', $z)
            ->where('max_zoom', '>=', $z)
            ->latest('generated_at')
            ->latest()
            ->first();

        abort_unless($package, 404, 'Mapa offline não disponível para esta fazenda.');

        $extension = pathinfo($y, PATHINFO_EXTENSION) ?: $package->tile_format;
        $tileY = pathinfo($y, PATHINFO_FILENAME);
        $relativePath = trim((string) $package->storage_path, '/')."/{$z}/{$x}/{$tileY}.{$extension}";

        abort_unless(Storage::disk('local')->exists($relativePath), 404, 'Tile offline não encontrado.');

        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'pbf' => 'application/x-protobuf',
            default => 'image/png',
        };

        return response(Storage::disk('local')->get($relativePath), 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    private function authorizeFarm(Request $request, Farm $farm): void
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($farm->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($farm), 403);
    }
}
