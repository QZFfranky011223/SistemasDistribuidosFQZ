@extends('layouts.app')
@section('title', 'Editar Medicamento')
@section('content')
<h2>Editar Medicamento</h2>
<form action="{{ route('medicamentos.update', $medicamento) }}" method="POST">
    @csrf @method('PUT')
    @include('medicamentos._form')
    <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
    <a href="{{ route('medicamentos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
</form>
@endsection
