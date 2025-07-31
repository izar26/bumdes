@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content_header')
    <h1>Laporan Neraca Saldo</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <form action="{{ route('laporan.neraca-saldo.generate') }}" method="POST" target="_blank">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="report_date">Pilih Tanggal Laporan</label>
                <input type="date" class="form-control" id="report_date" name="report_date" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-eye"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop