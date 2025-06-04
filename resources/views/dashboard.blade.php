@extends('layouts.app')

@section('content')
    @php
        $chart = new \App\Charts\MaintenanceChart();
        $chartData = $chart->getData();
    @endphp

    <div>
        <h1 class="text-2xl font-bold mb-4">Maintenance per Bulan</h1>
        @include('charts.maintenance', ['chartData' => $chartData])
    </div>
@endsection
