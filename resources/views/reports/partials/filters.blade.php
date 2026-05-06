<div class="filters">
    <form method="GET">
        <label>Período:</label>
        <input type="date" name="start_date">
        <input type="date" name="end_date">

        <label>Fazenda:</label>
        <select name="farm_id">
            <option value="">Todas</option>
        </select>

        <label>Safra:</label>
        <select name="harvest_id">
            <option value="">Todas</option>
        </select>

        <label>Cultura:</label>
        <select name="crop_id">
            <option value="">Todas</option>
        </select>

        <label>Lote:</label>
        <select name="lot_id">
            <option value="">Todos</option>
        </select>

        <label>Status:</label>
        <select name="status">
            <option value="">Todos</option>
        </select>

        <label>Tipo:</label>
        <select name="type">
            <option value="">Todos</option>
        </select>

        <button type="submit">Filtrar</button>
    </form>
</div>