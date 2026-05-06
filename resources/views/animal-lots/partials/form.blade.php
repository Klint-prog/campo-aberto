@csrf
<div class="form-grid">
    <div><label>Fazenda</label><select name="farm_id" required><option value="">Selecione</option>@foreach($farms as $id => $name)<option value="{{ $id }}" @selected(old('farm_id', $animalLot->farm_id) === $id)>{{ $name }}</option>@endforeach</select>@error('farm_id')<div class="errors">{{ $message }}</div>@enderror</div>
    <div><label>Pastagem</label><select name="pasture_id"><option value="">Sem pastagem</option>@foreach($pastures as $id => $name)<option value="{{ $id }}" @selected(old('pasture_id', $animalLot->pasture_id) === $id)>{{ $name }}</option>@endforeach</select></div>
    <div><label>Nome</label><input name="name" value="{{ old('name', $animalLot->name) }}" required></div>
    <div><label>Código</label><input name="code" value="{{ old('code', $animalLot->code) }}"></div>
    <div><label>Espécie</label><select name="species" required>@foreach($species as $value => $label)<option value="{{ $value }}" @selected(old('species', $animalLot->species) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div><label>Finalidade</label><input name="purpose" value="{{ old('purpose', $animalLot->purpose) }}" placeholder="Cria, recria, engorda, leite..."></div>
    <div><label>Status</label><select name="status" required>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $animalLot->status ?: 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div><label>Início</label><input type="date" name="started_on" value="{{ old('started_on', optional($animalLot->started_on)->format('Y-m-d')) }}"></div>
    <div><label>Encerramento</label><input type="date" name="closed_on" value="{{ old('closed_on', optional($animalLot->closed_on)->format('Y-m-d')) }}"></div>
    <div><label>Observações</label><input name="notes" value="{{ old('notes', $animalLot->notes) }}"></div>
    <div class="actions"><button class="btn" type="submit">Salvar lote</button><a class="btn secondary" href="{{ route('animal-lots.index') }}">Cancelar</a></div>
</div>
