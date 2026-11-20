@extends('layouts.app')
@section('title', $medicamento->nombre_comercial)
@section('content')
<h2>{{ $medicamento->nombre_comercial }}</h2>
<dl class="row">
    <dt class="col-sm-3">Principio Activo</dt><dd class="col-sm-9">{{ $medicamento->principio_activo }}</dd>
    <dt class="col-sm-3">Presentación</dt><dd class="col-sm-9">{{ $medicamento->presentacion }}</dd>
    <dt class="col-sm-3">Precio</dt><dd class="col-sm-9">Bs. {{ number_format($medicamento->precio, 2) }}</dd>
    <dt class="col-sm-3">Stock</dt><dd class="col-sm-9">{{ $medicamento->stock }}</dd>
    <dt class="col-sm-3">Receta</dt><dd class="col-sm-9">{{ $medicamento->requiere_receta ? 'Sí' : 'No' }}</dd>
    <dt class="col-sm-3">Laboratorio</dt><dd class="col-sm-9">{{ $medicamento->laboratorio->nombre ?? '-' }}</dd>
</dl>
<a href="{{ route('medicamentos.index') }}" class="btn btn-secondary">Volver</a>
@endsection
