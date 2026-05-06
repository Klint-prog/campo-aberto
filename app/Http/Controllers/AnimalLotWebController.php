<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsLivestockWebOptions;
use App\Models\AnimalLot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnimalLotWebController extends Controller
{
    use BuildsLivestockWebOptions;

    public function index(Request $request): View
    {
        $lots = $this->lotQuery($request)
            ->with(['farm', 'pasture'])
            ->withCount('animals')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('animal-lots.index', $this->viewData($request) + [
            'lots' => $lots,
            'filters' => $request->only(['farm_id', 'species', 'status', 'start_on', 'end_on']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('animal-lots.create', $this->viewData($request) + ['animalLot' => new AnimalLot]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        abort_unless($request->user()->canAccessFarm($data['farm_id']), Response::HTTP_FORBIDDEN);

        $lot = AnimalLot::create($data + ['tenant_id' => $request->user()->tenant_id]);

        return redirect()->route('animal-lots.show', $lot)->with('status', 'Lote de animais criado com sucesso.');
    }

    public function show(Request $request, AnimalLot $animalLot): View
    {
        $this->authorizeLot($request, $animalLot);

        return view('animal-lots.show', $this->viewData($request) + [
            'animalLot' => $animalLot->load(['farm', 'pasture', 'animals']),
        ]);
    }

    public function edit(Request $request, AnimalLot $animalLot): View
    {
        $this->authorizeLot($request, $animalLot);

        return view('animal-lots.edit', $this->viewData($request) + ['animalLot' => $animalLot]);
    }

    public function update(Request $request, AnimalLot $animalLot): RedirectResponse
    {
        $this->authorizeLot($request, $animalLot);
        $data = $this->validated($request);
        abort_unless($request->user()->canAccessFarm($data['farm_id']), Response::HTTP_FORBIDDEN);

        $animalLot->fill($data)->save();

        return redirect()->route('animal-lots.show', $animalLot)->with('status', 'Lote de animais atualizado com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nome', 'Código', 'Fazenda', 'Pastagem', 'Espécie', 'Finalidade', 'Status', 'Início', 'Encerramento', 'Animais']);

            $this->lotQuery($request)->with(['farm', 'pasture'])->withCount('animals')->chunk(200, function ($lots) use ($handle): void {
                foreach ($lots as $lot) {
                    fputcsv($handle, [$lot->name, $lot->code, $lot->farm?->name, $lot->pasture?->name, $lot->species, $lot->purpose, $lot->status, optional($lot->started_on)->format('Y-m-d'), optional($lot->closed_on)->format('Y-m-d'), $lot->animals_count]);
                }
            });

            fclose($handle);
        }, 'lotes-animais-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function lotQuery(Request $request): Builder
    {
        return AnimalLot::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn (Builder $query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('species'), fn (Builder $query) => $query->where('species', $request->input('species')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('start_on'), fn (Builder $query) => $query->whereDate('started_on', '>=', $request->input('start_on')))
            ->when($request->filled('end_on'), fn (Builder $query) => $query->whereDate('started_on', '<=', $request->input('end_on')));
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'farm_id' => ['required', 'uuid'],
            'pasture_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'species' => ['required', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
            'started_on' => ['nullable', 'date'],
            'closed_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function authorizeLot(Request $request, AnimalLot $animalLot): void
    {
        abort_unless($animalLot->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animalLot->farm_id), Response::HTTP_FORBIDDEN);
    }

    protected function viewData(Request $request): array
    {
        return ['farms' => $this->farmOptions($request), 'pastures' => $this->pastureOptions($request), 'species' => $this->speciesOptions(), 'statuses' => $this->lotStatuses()];
    }
}
