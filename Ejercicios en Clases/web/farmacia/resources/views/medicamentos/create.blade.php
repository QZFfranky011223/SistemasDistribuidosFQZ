@extends('layouts.app')
@section('title', 'Nuevo Medicamento')
@section('content')
<h2>Nuevo Medicamento</h2>
<form action="{{ route('medicamentos.store') }}" method="POST">
    @csrf
    @include('medicamentos._form')
    <button type="submit" class="btn btn-primary mt-3">Guardar</button>
    <a href="{{ route('medicamentos.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
</form>
@endsection
