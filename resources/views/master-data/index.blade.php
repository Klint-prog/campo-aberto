@extends('layouts.app')

@section('title', $config['title'].' - Campo Aberto')
@section('page_title', $config['title'])
@section('breadcrumb', 'Cadastros mestres rurais')

@section('content')
    <section class="card">
        <div class="actions" style="justify-content:space-between; margin-bottom:18px">
            <div>
                <h2 style="margin:0">{{ $config['title'] }}</h2>
                <p class="muted" style="margin:4px 0 0">CRUD visual com escopo por tenant e fazenda.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route($module.'.export', request()->query()) }}">Exportar CSV</a>
                <a class="btn" href="{{ route($module.'.create') }}">Novo cadastro</a>
            </div>
        </div>

        <form method="GET" class="card" style="box-shadow:none; margin-bottom:18px; padding:14px">
            <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); align-items:end">
                <div>
                    <label for="q">Busca</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nome, código ou status">
                </div>

                @if (! empty($farms) && collect($config['fields'])->has('farm_id'))
                    <div>
                        <label for="farm_id">Fazenda</label>
                        <select id="farm_id" name="farm_id">
                            <option value="">Todas</option>
                            @foreach ($farms as $id => $name)
                                <option value="{{ $id }}" @selected(($filters['farm_id'] ?? '') === $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (array_key_exists('status', $config['fields']))
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Todos</option>
                            @foreach (($config['fields']['status']['options'] ?? []) as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (array_key_exists('is_active', $config['fields']))
                    <div>
                        <label for="active">Ativo</label>
                        <select id="active" name="active">
                            <option value="">Todos</option>
                            <option value="1" @selected(($filters['active'] ?? '') === '1')>Sim</option>
                            <option value="0" @selected(($filters['active'] ?? '') === '0')>Não</option>
                        </select>
                    </div>
                @endif

                <div class="actions">
                    <button class="btn" type="submit">Filtrar</button>
                    <a class="btn secondary" href="{{ route($module.'.index') }}">Limpar</a>
                </div>
            </div>
        </form>

        @if ($records->isEmpty())
            <div class="empty">
                <h3>Nenhum registro encontrado</h3>
                <p>Use o botão “Novo cadastro” para iniciar o cadastro mestre.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>Atualizado em</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($records as $record)
                        <tr>
                            @foreach ($columns as $column => $label)
                                <td>
                                    @if ($column === 'farm_id' && $record->relationLoaded('farm'))
                                        {{ $record->farm?->name ?? '—' }}
                                    @elseif ($column === 'crop_id' && $record->relationLoaded('crop'))
                                        {{ $record->crop?->name ?? '—' }}
                                    @elseif (is_bool(data_get($record, $column)))
                                        {{ data_get($record, $column) ? 'Sim' : 'Não' }}
                                    @else
                                        {{ data_get($record, $column) ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                            <td>{{ optional($record->updated_at ?? $record->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary" href="{{ route($module.'.show', $record) }}">Ver</a>
                                    <a class="btn secondary" href="{{ route($module.'.edit', $record) }}">Editar</a>
                                    <form method="POST" action="{{ route($module.'.destroy', $record) }}" onsubmit="return confirm('Remover este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger" type="submit">Remover</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $records->links() }}</div>
        @endif
    </section>
@endsection
