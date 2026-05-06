@csrf
<div class="form-grid">
    <div>
        <label for="farm_id">Fazenda</label>
        <select id="farm_id" name="farm_id" required>
            <option value="">Selecione</option>
            @foreach ($farms as $id => $name)
                <option value="{{ $id }}" @selected(old('farm_id', $harvest->farm_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('farm_id') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="crop_id">Cultura</label>
        <select id="crop_id" name="crop_id" required>
            <option value="">Selecione</option>
            @foreach ($crops as $id => $name)
                <option value="{{ $id }}" @selected(old('crop_id', $harvest->crop_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('crop_id') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div><label for="harvested_on">Data da colheita</label><input type="date" id="harvested_on" name="harvested_on" value="{{ old('harvested_on', optional($harvest->harvested_on)->format('Y-m-d')) }}" required></div>
    <div><label for="harvested_area_ha">Área colhida (ha)</label><input type="number" step="0.0001" id="harvested_area_ha" name="harvested_area_ha" value="{{ old('harvested_area_ha', $harvest->harvested_area_ha) }}" required></div>
    <div><label for="total_weight_kg">Peso total (kg)</label><input type="number" step="0.0001" id="total_weight_kg" name="total_weight_kg" value="{{ old('total_weight_kg', $harvest->total_weight_kg) }}" required></div>

    <div>
        <label for="plot_id">Talhão/plot</label>
        <select id="plot_id" name="plot_id"><option value="">Não informado</option>@foreach ($plots as $id => $name)<option value="{{ $id }}" @selected(old('plot_id', $harvest->plot_id) === $id)>{{ $name }}</option>@endforeach</select>
    </div>
    <div>
        <label for="season_id">Safra</label>
        <select id="season_id" name="season_id"><option value="">Não informada</option>@foreach ($seasons as $id => $name)<option value="{{ $id }}" @selected(old('season_id', $harvest->season_id) === $id)>{{ $name }}</option>@endforeach</select>
    </div>
    <div>
        <label for="crop_variety_id">Variedade</label>
        <select id="crop_variety_id" name="crop_variety_id"><option value="">Não informada</option>@foreach ($cropVarieties as $id => $name)<option value="{{ $id }}" @selected(old('crop_variety_id', $harvest->crop_variety_id) === $id)>{{ $name }}</option>@endforeach</select>
    </div>
    <div>
        <label for="activity_id">Atividade relacionada</label>
        <select id="activity_id" name="activity_id"><option value="">Não vinculada</option>@foreach ($activities as $id => $title)<option value="{{ $id }}" @selected(old('activity_id', $harvest->activity_id) === $id)>{{ $title }}</option>@endforeach</select>
    </div>
    <div><label for="quality_grade">Qualidade</label><input id="quality_grade" name="quality_grade" value="{{ old('quality_grade', $harvest->quality_grade) }}" placeholder="Ex.: Tipo 1"></div>

    <div class="actions"><button class="btn" type="submit">Salvar colheita</button><a class="btn secondary" href="{{ route('harvests.index') }}">Cancelar</a></div>
</div>
