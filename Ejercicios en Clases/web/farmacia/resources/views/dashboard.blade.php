@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h2>Dashboard</h2>
<div class="row mt-3">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Medicamentos</h5>
                <p class="card-text display-6">{{ $totalMedicamentos }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Stock Bajo (&lt;10)</h5>
                <p class="card-text display-6">{{ $stockBajo }}</p>
            </div>
        </div>
    </div>
</div>
<h4>Medicamentos por Laboratorio</h4>
<table class="table table-striped">
    <thead><tr><th>Laboratorio</th><th>Cantidad</th></tr></thead>
    <tbody>
        @foreach($porLaboratorio as $lab)
        <tr><td>{{ $lab->nombre }}</td><td>{{ $lab->medicamentos_count }}</td></tr>
        @endforeach
    </tbody>
</table>
@endsection
