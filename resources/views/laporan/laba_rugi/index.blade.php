@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content_header')
    <h1>Laporan Laba Rugi</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <form action="{{ route('laporan.laba-rugi.generate') }}" method="POST" target="_blank">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="end_date">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-t') }}" required>
                </div>

                @if(isset($unitUsahas) && !$unitUsahas->isEmpty())
                <div class="form-group col-md-12">
                    <label for="unit_usaha_id">Filter Unit Usaha (Opsional)</label>
                    <select class="form-control" id="unit_usaha_id" name="unit_usaha_id"
                        @if($unitUsahas->count() === 1) disabled @endif>
                        <option value="">-- Tampilkan Semua Unit Usaha --</option>
                        @foreach ($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}"
                                @if($unitUsahas->count() === 1) selected @endif>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @if($unitUsahas->count() === 1)
                        <input type="hidden" name="unit_usaha_id" value="{{ $unitUsahas->first()->unit_usaha_id }}">
                    @endif
                </div>
                @endif
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
