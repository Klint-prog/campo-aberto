@csrf

<div class="form-grid">
    @foreach ($fields as $name => $field)
        <div>
            @if (($field['type'] ?? 'text') === 'checkbox')
                <input type="hidden" name="{{ $name }}" value="0">
                <label style="display:flex; gap:8px; align-items:center">
                    <input style="width:auto" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $record?->{$name} ?? true))>
                    {{ $field['label'] }}
                </label>
            @else
                <label for="{{ $name }}">{{ $field['label'] }}</label>

                @if (($field['type'] ?? 'text') === 'textarea')
                    <textarea id="{{ $name }}" name="{{ $name }}" rows="4" style="width:100%; border:1px solid var(--line); border-radius:12px; padding:11px 12px; font:inherit">{{ old($name, $record?->{$name}) }}</textarea>
                @elseif (($field['type'] ?? 'text') === 'select' || ($field['type'] ?? 'text') === 'farm' || ($field['type'] ?? 'text') === 'crop')
                    <select id="{{ $name }}" name="{{ $name }}">
                        <option value="">Selecione</option>
                        @foreach (($field['options'] ?? []) as $value => $label)
                            <option value="{{ $value }}" @selected(old($name, $record?->{$name}) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $name }}"
                        name="{{ $name }}"
                        type="{{ $field['type'] ?? 'text' }}"
                        step="{{ $field['step'] ?? '' }}"
                        value="{{ old($name, $record?->{$name}) }}"
                    >
                @endif
            @endif

            @error($name)
                <div class="errors">{{ $message }}</div>
            @enderror
        </div>
    @endforeach

    <div class="actions">
        <button class="btn" type="submit">Salvar</button>
        <a class="btn secondary" href="{{ route($module.'.index') }}">Cancelar</a>
    </div>
</div>
