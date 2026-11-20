@extends('layouts.app')
@section('title', 'Medicamentos')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Medicamentos</h2>
    <a href="{{ route('medicamentos.create') }}" class="btn btn-primary">+ Nuevo</a>
</div>
<form method="GET" action="{{ route('medicamentos.index') }}" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre comercial o principio activo..." value="{{ request('search') }}">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
</form>
<table class="table table-striped">
    <thead>
        <tr><th>Nombre Comercial</th><th>Principio Activo</th><th>Presentación</th><th>Precio</th><th>Stock</th><th>Laboratorio</th><th>Acciones</th></tr>
    </thead>
    <tbody>
        @forelse($medicamentos as $med)
        <tr>
            <td>{{ $med->nombre_comercial }}</td>
            <td>{{ $med->principio_activo }}</td>
            <td>{{ $med->presentacion }}</td>
            <td>Bs. {{ number_format($med->precio, 2) }}</td>
            <td>{{ $med->stock }}</td>
            <td>{{ $med->laboratorio->nombre ?? '-' }}</td>
            <td>
                <a href="{{ route('medicamentos.edit', $med) }}" class="btn btn-sm btn-warning">Editar</a>
                <form action="{{ route('medicamentos.destroy', $med) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">No hay medicamentos.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $medicamentos->appends(request()->query())->links() }}
@endsection
