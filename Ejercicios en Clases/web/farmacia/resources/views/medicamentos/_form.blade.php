@if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
<div class="mb-3">
    <label class="form-label">Nombre Comercial</label>
    <input type="text" name="nombre_comercial" class="form-control @error('nombre_comercial') is-invalid @enderror" value="{{ old('nombre_comercial', $medicamento->nombre_comercial ?? '') }}">
    @error('nombre_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Principio Activo</label>
    <input type="text" name="principio_activo" class="form-control @error('principio_activo') is-invalid @enderror" value="{{ old('principio_activo', $medicamento->principio_activo ?? '') }}">
    @error('principio_activo')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Presentación</label>
    <select name="presentacion" class="form-select @error('presentacion') is-invalid @enderror">
        @foreach(['Tabletas','Jarabe','Inyectable','Cápsulas'] as $p)
        <option value="{{ $p }}" {{ old('presentacion', $medicamento->presentacion ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
        @endforeach
    </select>
    @error('presentacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Precio (Bs.)</label>
    <input type="number" step="0.01" min="0" name="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $medicamento->precio ?? '') }}">
    @error('precio')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Stock</label>
    <input type="number" min="0" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $medicamento->stock ?? '') }}">
    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Requiere Receta</label>
    <select name="requiere_receta" class="form-select">
        <option value="0" {{ old('requiere_receta', $medicamento->requiere_receta ?? 0) == '0' ? 'selected' : '' }}>No</option>
        <option value="1" {{ old('requiere_receta', $medicamento->requiere_receta ?? 0) == '1' ? 'selected' : '' }}>Sí</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Laboratorio</label>
    <select name="laboratorio_id" class="form-select @error('laboratorio_id') is-invalid @enderror">
        <option value="">Seleccione...</option>
        @foreach($laboratorios as $lab)
        <option value="{{ $lab->id }}" {{ old('laboratorio_id', $medicamento->laboratorio_id ?? '') == $lab->id ? 'selected' : '' }}>{{ $lab->nombre }}</option>
        @endforeach
    </select>
    @error('laboratorio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
