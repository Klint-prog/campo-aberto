<?php

namespace App\Services;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GeoJsonService
{
    public function validateFeatureCollection(array $geojson): void
    {
        if (($geojson['type'] ?? null) !== 'FeatureCollection' || ! isset($geojson['features']) || ! is_array($geojson['features'])) {
            throw ValidationException::withMessages([
                'geojson' => 'O GeoJSON deve ser uma FeatureCollection válida.',
            ]);
        }

        foreach ($geojson['features'] as $index => $feature) {
            $this->validateFeature($feature, "features.$index");
        }
    }

    public function validateFeature(array $feature, string $path = 'feature'): void
    {
        if (($feature['type'] ?? null) !== 'Feature' || ! isset($feature['geometry']) || ! is_array($feature['geometry'])) {
            throw ValidationException::withMessages([
                'geojson' => "{$path} deve ser uma Feature com geometry.",
            ]);
        }

        $type = $feature['geometry']['type'] ?? null;

        if (! in_array($type, ['Polygon', 'MultiPolygon'], true)) {
            throw ValidationException::withMessages([
                'geojson' => "{$path}.geometry deve ser Polygon ou MultiPolygon.",
            ]);
        }

        if (empty($feature['geometry']['coordinates']) || ! is_array($feature['geometry']['coordinates'])) {
            throw ValidationException::withMessages([
                'geojson' => "{$path}.geometry.coordinates é obrigatório.",
            ]);
        }
    }

    public function readUploadedGeoJson(UploadedFile $file): array
    {
        $decoded = json_decode($file->getContent(), true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'geojson' => 'Arquivo GeoJSON inválido ou malformado.',
            ]);
        }

        return $decoded;
    }

    public function importPlots(Farm $farm, array $geojson, string $userId): int
    {
        $this->validateFeatureCollection($geojson);

        $count = 0;

        DB::transaction(function () use ($farm, $geojson, $userId, &$count): void {
            foreach ($geojson['features'] as $feature) {
                $properties = $feature['properties'] ?? [];
                $name = $properties['name'] ?? $properties['nome'] ?? $properties['talhao'] ?? 'Talhão '.($count + 1);
                $code = $properties['code'] ?? $properties['codigo'] ?? null;
                $now = now();

                DB::insert(
                    'INSERT INTO plots (id, tenant_id, farm_id, name, code, properties, created_by, updated_by, created_at, updated_at, geom) VALUES (?, ?, ?, ?, ?, ?::jsonb, ?, ?, ?, ?, ST_Multi(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)))',
                    [
                        (string) Str::uuid(),
                        $farm->tenant_id,
                        $farm->id,
                        $name,
                        $code,
                        json_encode($properties),
                        $userId,
                        $userId,
                        $now,
                        $now,
                        json_encode($feature['geometry']),
                    ]
                );

                $count++;
            }
        });

        return $count;
    }

    public function farmFeatureCollection(Farm $farm): array
    {
        $features = [];

        foreach ($farm->plots()->orderBy('name')->get() as $plot) {
            $features[] = $this->modelFeature($plot, 'plot');
        }

        foreach ($farm->pastures()->orderBy('name')->get() as $pasture) {
            $features[] = $this->modelFeature($pasture, 'pasture');
        }

        foreach ($farm->fields()->orderBy('name')->get() as $field) {
            $features[] = $this->modelFeature($field, 'field');
        }

        foreach ($farm->mapFeatures()->orderBy('name')->get() as $feature) {
            $features[] = $this->modelFeature($feature, 'map_feature');
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    private function modelFeature(Model $model, string $layer): array
    {
        return [
            'type' => 'Feature',
            'id' => $model->getKey(),
            'geometry' => $model->geo_json,
            'properties' => array_merge($model->properties ?? [], [
                'id' => $model->getKey(),
                'layer' => $layer,
                'name' => $model->name,
                'code' => $model->code ?? null,
                'area_ha' => $model->area_ha ?? null,
                'perimeter_m' => $model->perimeter_m ?? null,
            ]),
        ];
    }
}
