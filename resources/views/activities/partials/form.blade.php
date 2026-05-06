@csrf
<div class="form-grid">
    <div>
        <label for="farm_id">Fazenda</label>
        <select id="farm_id" name="farm_id" required>
            <option value="">Selecione</option>
            @foreach ($farms as $id => $name)
                <option value="{{ $id }}" @selected(old('farm_id', $activity->farm_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('farm_id') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="title">Título</label>
        <input id="title" name="title" value="{{ old('title', $activity->title) }}" required>
        @error('title') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="type">Tipo</label>
        <select id="type" name="type" required>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $activity->type ?: 'planting') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <div class="errors">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="plot_id">Talhão/plot</label>
        <select id="plot_id" name="plot_id">
            <option value="">Não informado</option>
            @foreach ($plots as $id => $name)
                <option value="{{ $id }}" @selected(old('plot_id', $activity->plot_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="season_id">Safra</label>
        <select id="season_id" name="season_id">
            <option value="">Não informada</option>
            @foreach ($seasons as $id => $name)
                <option value="{{ $id }}" @selected(old('season_id', $activity->season_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="crop_id">Cultura</label>
        <select id="crop_id" name="crop_id">
            <option value="">Não informada</option>
            @foreach ($crops as $id => $name)
                <option value="{{ $id }}" @selected(old('crop_id', $activity->crop_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="crop_variety_id">Variedade</label>
        <select id="crop_variety_id" name="crop_variety_id">
            <option value="">Não informada</option>
            @foreach ($cropVarieties as $id => $name)
                <option value="{{ $id }}" @selected(old('crop_variety_id', $activity->crop_variety_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="planned_start_on">Início planejado</label>
        <input type="date" id="planned_start_on" name="planned_start_on" value="{{ old('planned_start_on', optional($activity->planned_start_on)->format('Y-m-d')) }}">
    </div>

    <div>
        <label for="planned_end_on">Fim planejado</label>
        <input type="date" id="planned_end_on" name="planned_end_on" value="{{ old('planned_end_on', optional($activity->planned_end_on)->format('Y-m-d')) }}">
    </div>

    <div>
        <label for="planned_area_ha">Área planejada (ha)</label>
        <input type="number" step="0.0001" id="planned_area_ha" name="planned_area_ha" value="{{ old('planned_area_ha', $activity->planned_area_ha) }}">
    </div>

    <div>
        <label for="estimated_productivity_kg_ha">Produtividade estimada (kg/ha)</label>
        <input type="number" step="0.01" id="estimated_productivity_kg_ha" name="estimated_productivity_kg_ha" value="{{ old('estimated_productivity_kg_ha', $activity->estimated_productivity_kg_ha) }}">
    </div>

    <div>
        <label for="description">Descrição</label>
        <textarea id="description" name="description" rows="4" style="width:100%; border:1px solid var(--line); border-radius:12px; padding:11px 12px; font:inherit">{{ old('description', $activity->description) }}</textarea>
    </div>

    <div class="actions">
        <button class="btn" type="submit">Salvar atividade</button>
        <a class="btn secondary" href="{{ route('activities.index') }}">Cancelar</a>
    </div>
</div>
