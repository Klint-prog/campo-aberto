<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AnimalLot;
use App\Models\Farm;
use App\Models\Pasture;
use Illuminate\Http\Request;

trait BuildsLivestockWebOptions
{
    protected function farmOptions(Request $request): array
    {
        $query = Farm::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name');

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function lotOptions(Request $request): array
    {
        $query = AnimalLot::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name');

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function pastureOptions(Request $request): array
    {
        $query = Pasture::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name');

        if (! $request->user()->hasRole('admin')) {
            $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id'));
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function speciesOptions(): array
    {
        return ['bovine' => 'Bovinos', 'buffalo' => 'Bubalinos', 'goat' => 'Caprinos', 'sheep' => 'Ovinos', 'swine' => 'Suínos', 'horse' => 'Equinos', 'poultry' => 'Aves'];
    }

    protected function animalStatuses(): array
    {
        return ['active' => 'Ativo', 'sold' => 'Vendido', 'dead' => 'Morto'];
    }

    protected function lotStatuses(): array
    {
        return ['active' => 'Ativo', 'closed' => 'Encerrado'];
    }

    protected function sexOptions(): array
    {
        return ['male' => 'Macho', 'female' => 'Fêmea', 'unknown' => 'Não informado'];
    }
}
