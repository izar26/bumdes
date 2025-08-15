@extends('adminlte::page')

@section('title', 'Filter Neraca')

@section('content_header')
    <h1>Laporan Neraca</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan Neraca</h3>
    </div>
    <form action="{{ route('laporan.neraca.generate') }}" method="GET" target="_blank">
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="form-group">
                <label for="report_date">Per Tanggal:</label>
                <input type="date" name="report_date" id="report_date" class="form-control" value="{{ old('report_date', date('Y-m-d')) }}" required>
            </div>

            @if(auth()->user()->hasRole(['bendahara_bumdes', 'admin_bumdes']))
                <div class="form-group">
                    <label for="unit_usaha_id">Unit Usaha:</label>
                    <select name="unit_usaha_id" id="unit_usaha_id" class="form-control">
                        <option value="">-- Semua Unit Usaha --</option>
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id') == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                {{ $unitUsaha->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop
