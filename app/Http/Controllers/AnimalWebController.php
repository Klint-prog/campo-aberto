<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsLivestockWebOptions;
use App\Models\Animal;
use App\Models\AnimalMovement;
use App\Support\LivestockDomainEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnimalWebController extends Controller
{
    use BuildsLivestockWebOptions;

    public function index(Request $request): View
    {
        $animals = $this->animalQuery($request)
            ->with(['farm', 'lot'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('animals.index', $this->viewData($request) + [
            'animals' => $animals,
            'filters' => $request->only(['farm_id', 'animal_lot_id', 'species', 'status', 'start_on', 'end_on']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('animals.create', $this->viewData($request) + ['animal' => new Animal(['status' => Animal::STATUS_ACTIVE, 'sex' => 'unknown'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        abort_unless($request->user()->canAccessFarm($data['farm_id']), Response::HTTP_FORBIDDEN);

        $animal = Animal::create($data + [
            'tenant_id' => $request->user()->tenant_id,
            'status' => Animal::STATUS_ACTIVE,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        if (isset($data['purchase_price'])) {
            $animal->movements()->create([
                'tenant_id' => $animal->tenant_id,
                'farm_id' => $animal->farm_id,
                'to_animal_lot_id' => $animal->animal_lot_id,
                'type' => AnimalMovement::TYPE_PURCHASE,
                'moved_on' => $animal->acquired_on ?? now()->toDateString(),
                'amount' => $animal->purchase_price,
                'document' => $animal->purchase_document,
                'counterparty' => $animal->origin,
                'created_by' => $request->user()->id,
            ]);
            $animal->recordDomainEvent(LivestockDomainEvent::ANIMAL_PURCHASED, $animal->eventPayload(['purchase_price' => $animal->purchase_price, 'document' => $animal->purchase_document, 'origin' => $animal->origin]));
        }

        return redirect()->route('animals.show', $animal)->with('status', 'Animal criado com sucesso.');
    }

    public function show(Request $request, Animal $animal): View
    {
        $this->authorizeAnimal($request, $animal);

        return view('animals.show', $this->viewData($request) + [
            'animal' => $animal->load(['farm', 'lot', 'movements', 'weightRecords', 'vaccinationRecords', 'healthRecords', 'feedConsumptions']),
        ]);
    }

    public function edit(Request $request, Animal $animal): View
    {
        $this->authorizeAnimal($request, $animal);

        return view('animals.edit', $this->viewData($request) + ['animal' => $animal]);
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);
        $data = $this->validated($request, updating: true);
        abort_unless($request->user()->canAccessFarm($data['farm_id']), Response::HTTP_FORBIDDEN);

        $animal->fill($data + ['updated_by' => $request->user()->id])->save();

        return redirect()->route('animals.show', $animal)->with('status', 'Animal atualizado com sucesso.');
    }

    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Código interno', 'Brinco', 'Nome', 'Fazenda', 'Lote', 'Espécie', 'Raça', 'Sexo', 'Status', 'Aquisição', 'Valor compra', 'Venda', 'Valor venda', 'Morte']);

            $this->animalQuery($request)->with(['farm', 'lot'])->chunk(200, function ($animals) use ($handle): void {
                foreach ($animals as $animal) {
                    fputcsv($handle, [$animal->internal_code, $animal->ear_tag, $animal->name, $animal->farm?->name, $animal->lot?->name, $animal->species, $animal->breed, $animal->sex, $animal->status, optional($animal->acquired_on)->format('Y-m-d'), $animal->purchase_price, optional($animal->sold_on)->format('Y-m-d'), $animal->sale_price, optional($animal->died_on)->format('Y-m-d')]);
                }
            });

            fclose($handle);
        }, 'animais-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function animalQuery(Request $request): Builder
    {
        return Animal::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when(! $request->user()->hasRole('admin'), fn (Builder $query) => $query->whereIn('farm_id', $request->user()->farms()->pluck('farms.id')))
            ->when($request->filled('farm_id'), fn (Builder $query) => $query->where('farm_id', $request->input('farm_id')))
            ->when($request->filled('animal_lot_id'), fn (Builder $query) => $query->where('animal_lot_id', $request->input('animal_lot_id')))
            ->when($request->filled('species'), fn (Builder $query) => $query->where('species', $request->input('species')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('start_on'), fn (Builder $query) => $query->whereDate('acquired_on', '>=', $request->input('start_on')))
            ->when($request->filled('end_on'), fn (Builder $query) => $query->whereDate('acquired_on', '<=', $request->input('end_on')));
    }

    protected function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'farm_id' => ['required', 'uuid'],
            'animal_lot_id' => ['nullable', 'uuid'],
            'internal_code' => ['required', 'string', 'max:255'],
            'ear_tag' => ['nullable', 'string', 'max:255'],
            'rfid' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'species' => ['required', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['required', 'in:male,female,unknown'],
            'birth_date' => ['nullable', 'date'],
            'birth_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'acquired_on' => ['nullable', 'date'],
            'purchase_price' => [$updating ? 'nullable' : 'nullable', 'numeric', 'min:0'],
            'purchase_document' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }

    protected function viewData(Request $request): array
    {
        return ['farms' => $this->farmOptions($request), 'lots' => $this->lotOptions($request), 'species' => $this->speciesOptions(), 'statuses' => $this->animalStatuses(), 'sexes' => $this->sexOptions()];
    }
}
