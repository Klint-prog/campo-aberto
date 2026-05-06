<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class AnimalMovementWebController extends Controller
{
    public function sell(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

        $data = $request->validate([
            'sale_price' => ['required', 'numeric', 'min:0'],
            'sale_document' => ['nullable', 'string', 'max:255'],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'sold_on' => ['nullable', 'date'],
        ]);

        $animal->sell($data['sale_price'], $data['sale_document'] ?? null, $data['counterparty'] ?? null, isset($data['sold_on']) ? Carbon::parse($data['sold_on']) : null);

        return redirect()->route('animals.show', $animal)->with('status', 'Venda registrada com sucesso.');
    }

    public function die(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorizeAnimal($request, $animal);

        $data = $request->validate([
            'death_cause' => ['nullable', 'string', 'max:1000'],
            'died_on' => ['nullable', 'date'],
        ]);

        $animal->die($data['death_cause'] ?? null, isset($data['died_on']) ? Carbon::parse($data['died_on']) : null);

        return redirect()->route('animals.show', $animal)->with('status', 'Mortalidade registrada com sucesso.');
    }

    protected function authorizeAnimal(Request $request, Animal $animal): void
    {
        abort_unless($animal->tenant_id === $request->user()->tenant_id && $request->user()->canAccessFarm($animal->farm_id), Response::HTTP_FORBIDDEN);
    }
}
