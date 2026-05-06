<section class="card" style="margin-top:18px">
    <h3 style="margin-top:0">Insumos consumidos</h3>

    <form method="POST" action="{{ route('activities.inputs.store', $activity) }}" style="margin-bottom:16px">
        @csrf
        <div class="form-grid" style="max-width:none; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); align-items:end">
            <div><label>Insumo</label><input name="input_name" required></div>
            <div><label>Tipo</label><input name="input_type" placeholder="fertilizante, defensivo"></div>
            <div><label>Referência</label><input name="input_reference" placeholder="SKU/lote opcional"></div>
            <div><label>Quantidade</label><input type="number" step="0.0001" name="quantity" required></div>
            <div><label>Unidade</label><input name="unit" placeholder="kg, L, un" required></div>
            <div><label>Custo unitário</label><input type="number" step="0.0001" name="unit_cost"></div>
            <div><button class="btn" type="submit">Registrar consumo</button></div>
        </div>
    </form>

    @if ($activity->inputs->isEmpty())
        <p class="muted">Nenhum consumo registrado para esta atividade.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Insumo</th><th>Tipo</th><th>Quantidade</th><th>Custo unitário</th><th>Total</th><th>Ações</th></tr></thead>
                <tbody>
                @foreach ($activity->inputs as $input)
                    <tr>
                        <td>{{ $input->input_name }}</td>
                        <td>{{ $input->input_type ?? '—' }}</td>
                        <td>{{ $input->quantity }} {{ $input->unit }}</td>
                        <td>{{ $input->unit_cost ?? '—' }}</td>
                        <td>{{ $input->total_cost ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('activities.inputs.destroy', [$activity, $input]) }}" onsubmit="return confirm('Remover este consumo?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
