@extends('layouts.app')

@section('title', 'Animal - Campo Aberto')
@section('page_title', $animal->internal_code)
@section('breadcrumb', 'Pecuária operacional')

@section('content')
<div class="grid">
    <section class="card">
        <div class="actions" style="justify-content:space-between"><div><h2 style="margin:0">{{ $animal->internal_code }}</h2><p class="muted">{{ $animal->farm?->name }} · {{ $animal->lot?->name ?? 'Sem lote' }} · {{ $species[$animal->species] ?? $animal->species }} · {{ $statuses[$animal->status] ?? $animal->status }}</p></div><a class="btn secondary" href="{{ route('animals.edit', $animal) }}">Editar animal</a></div>
        <p><strong>Brinco:</strong> {{ $animal->ear_tag ?? '—' }} · <strong>RFID:</strong> {{ $animal->rfid ?? '—' }} · <strong>Sexo:</strong> {{ $sexes[$animal->sex] ?? $animal->sex }}</p>
        <p><strong>Compra:</strong> {{ optional($animal->acquired_on)->format('d/m/Y') ?? '—' }} · <strong>Valor:</strong> {{ $animal->purchase_price ?? '—' }} · <strong>Origem:</strong> {{ $animal->origin ?? '—' }}</p>
    </section>

    <section class="grid cards">
        <form class="card" method="POST" action="{{ route('animals.sell', $animal) }}">@csrf<h3 style="margin-top:0">Registrar venda</h3><div class="form-grid"><input type="date" name="sold_on"><input type="number" step="0.01" min="0" name="sale_price" placeholder="Valor" required><input name="sale_document" placeholder="Documento"><input name="counterparty" placeholder="Comprador"><button class="btn" type="submit">Vender</button></div></form>
        <form class="card" method="POST" action="{{ route('animals.die', $animal) }}">@csrf<h3 style="margin-top:0">Registrar morte</h3><div class="form-grid"><input type="date" name="died_on"><input name="death_cause" placeholder="Causa"><button class="btn danger" type="submit">Registrar morte</button></div></form>
    </section>

    <section class="grid cards">
        <form class="card" method="POST" action="{{ route('animals.weights.store', $animal) }}">@csrf<h3 style="margin-top:0">Pesagem</h3><div class="form-grid"><input type="date" name="weighed_on" required><input type="number" step="0.001" min="0" name="weight_kg" placeholder="Peso kg" required><input type="number" step="0.0001" name="average_daily_gain_kg" placeholder="GMD kg"><input name="notes" placeholder="Observações"><button class="btn" type="submit">Registrar pesagem</button></div></form>
        <form class="card" method="POST" action="{{ route('animals.vaccinations.store', $animal) }}">@csrf<h3 style="margin-top:0">Vacinação</h3><div class="form-grid"><input type="date" name="vaccinated_on" required><input name="vaccine_name" placeholder="Vacina" required><input name="batch_number" placeholder="Lote da vacina"><input type="number" step="0.0001" min="0" name="dose" placeholder="Dose"><input name="dose_unit" placeholder="Unidade"><input type="date" name="next_due_on"><input name="responsible" placeholder="Responsável"><button class="btn" type="submit">Registrar vacinação</button></div></form>
        <form class="card" method="POST" action="{{ route('animals.health-records.store', $animal) }}">@csrf<h3 style="margin-top:0">Sanidade/tratamento</h3><div class="form-grid"><input name="type" placeholder="Tipo" required><input type="date" name="recorded_on" required><input name="diagnosis" placeholder="Diagnóstico"><input name="medicine_name" placeholder="Medicamento"><input type="number" step="0.0001" min="0" name="quantity_consumed" placeholder="Quantidade"><input name="quantity_unit" placeholder="Unidade"><input name="responsible" placeholder="Responsável"><button class="btn" type="submit">Registrar tratamento</button></div></form>
        <form class="card" method="POST" action="{{ route('animals.feed-consumptions.store', $animal) }}">@csrf<h3 style="margin-top:0">Alimentação</h3><div class="form-grid"><input type="date" name="consumed_on" required><input name="feed_name" placeholder="Alimento" required><input type="number" step="0.0001" min="0" name="quantity" placeholder="Quantidade" required><input name="unit" placeholder="Unidade" required><input type="number" step="0.0001" min="0" name="unit_cost" placeholder="Custo unitário"><button class="btn" type="submit">Registrar alimentação</button></div></form>
    </section>

    <section class="card"><h3 style="margin-top:0">Histórico operacional</h3><div class="table-wrap"><table><thead><tr><th>Tipo</th><th>Data</th><th>Resumo</th></tr></thead><tbody>
        @foreach($animal->weightRecords as $record)<tr><td>Pesagem</td><td>{{ optional($record->weighed_on)->format('d/m/Y') }}</td><td>{{ $record->weight_kg }} kg</td></tr>@endforeach
        @foreach($animal->vaccinationRecords as $record)<tr><td>Vacinação</td><td>{{ optional($record->vaccinated_on)->format('d/m/Y') }}</td><td>{{ $record->vaccine_name }}</td></tr>@endforeach
        @foreach($animal->healthRecords as $record)<tr><td>Sanidade</td><td>{{ optional($record->recorded_on)->format('d/m/Y') }}</td><td>{{ $record->type }} {{ $record->medicine_name ? '· '.$record->medicine_name : '' }}</td></tr>@endforeach
        @foreach($animal->feedConsumptions as $record)<tr><td>Alimentação</td><td>{{ optional($record->consumed_on)->format('d/m/Y') }}</td><td>{{ $record->feed_name }} · {{ $record->quantity }} {{ $record->unit }}</td></tr>@endforeach
    </tbody></table></div></section>
</div>
@endsection
