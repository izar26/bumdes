@extends('adminlte::page')

@section('title', 'Filter Laporan Perubahan Ekuitas')

@section('content_header')
    <h1>Laporan Perubahan Ekuitas</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <form action="{{ route('laporan.perubahan-ekuitas.generate') }}" method="POST" target="_blank">
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
                <div class="form-group col-md-4">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-t') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="tanggal_cetak">Tanggal Cetak (Opsional)</label>
                    <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            @if(isset($unitUsahas) && !$unitUsahas->isEmpty())
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="unit_usaha_id">Filter Unit Usaha (Opsional)</label>
                    @php
                        $isSingleUnitManager = $user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha']) && $unitUsahas->count() === 1;
                    @endphp
                    <select class="form-control" id="unit_usaha_id" name="unit_usaha_id" @if($isSingleUnitManager) disabled @endif>
                        @unless($isSingleUnitManager)
                            <option value="">-- Tampilkan Laporan Gabungan --</option>
                        @endunless
                        @foreach ($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}" @if($isSingleUnitManager) selected @endif>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @if($isSingleUnitManager)
                        <input type="hidden" name="unit_usaha_id" value="{{ $unitUsahas->first()->unit_usaha_id }}">
                    @endif
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-eye"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop
