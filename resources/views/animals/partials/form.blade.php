@csrf
<div class="form-grid">
    <div><label>Fazenda</label><select name="farm_id" required><option value="">Selecione</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(old('farm_id', $animal->farm_id) === $id)>{{ $name }}</option>@endforeach</select></div>
    <div><label>Lote</label><select name="animal_lot_id"><option value="">Sem lote</option>@foreach($lots as $id => $name)<option value="{{ $id }}" @selected(old('animal_lot_id', $animal->animal_lot_id) === $id)>{{ $name }}</option>@endforeach</select></div>
    <div><label>Código interno</label><input name="internal_code" value="{{ old('internal_code', $animal->internal_code) }}" required></div>
    <div><label>Brinco</label><input name="ear_tag" value="{{ old('ear_tag', $animal->ear_tag) }}"></div>
    <div><label>RFID</label><input name="rfid" value="{{ old('rfid', $animal->rfid) }}"></div>
    <div><label>Nome</label><input name="name" value="{{ old('name', $animal->name) }}"></div>
    <div><label>Espécie</label><select name="species" required>@foreach($species as $value => $label)<option value="{{ $value }}" @selected(old('species', $animal->species) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div><label>Raça</label><input name="breed" value="{{ old('breed', $animal->breed) }}"></div>
    <div><label>Sexo</label><select name="sex" required>@foreach($sexes as $value => $label)<option value="{{ $value }}" @selected(old('sex', $animal->sex ?: 'unknown') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div><label>Nascimento</label><input type="date" name="birth_date" value="{{ old('birth_date', optional($animal->birth_date)->format('Y-m-d')) }}"></div>
    <div><label>Peso nascimento kg</label><input type="number" step="0.001" min="0" name="birth_weight_kg" value="{{ old('birth_weight_kg', $animal->birth_weight_kg) }}"></div>
    <div><label>Aquisição/compra</label><input type="date" name="acquired_on" value="{{ old('acquired_on', optional($animal->acquired_on)->format('Y-m-d')) }}"></div>
    <div><label>Valor compra</label><input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $animal->purchase_price) }}"></div>
    <div><label>Documento compra</label><input name="purchase_document" value="{{ old('purchase_document', $animal->purchase_document) }}"></div>
    <div><label>Origem/fornecedor</label><input name="origin" value="{{ old('origin', $animal->origin) }}"></div>
    <div class="actions"><button class="btn" type="submit">Salvar animal</button><a class="btn secondary" href="{{ route('animals.index') }}">Cancelar</a></div>
</div>
