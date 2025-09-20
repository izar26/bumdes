@extends('adminlte::page')

@section('title', 'Filter Neraca')

@section('content_header')
    <h1>Laporan Posisi Keuangan (Neraca)</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <form action="{{ route('laporan.neraca.generate') }}" method="POST" target="_blank">
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
                    <label for="start_date">Saldo Awal per Tanggal</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="end_date">Saldo Akhir per Tanggal</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-t') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="tanggal_cetak">Tanggal Cetak (Opsional)</label>
                    <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            {{-- PENYESUAIAN DROPDOWN FILTER --}}
            @if(isset($unitUsahas))
            @php
                $user = Auth::user();
                $isBumdesAdmin = $user->hasAnyRole(['bendahara_bumdes', 'direktur_bumdes', 'admin_bumdes', 'sekretaris_bumdes']);
                $isSingleUnitManager = $user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha']) && $unitUsahas->count() === 1;
            @endphp
            
            @if($isBumdesAdmin || !$unitUsahas->isEmpty())
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="unit_usaha_id">Filter (Opsional)</label>
                    <select class="form-control" id="unit_usaha_id" name="unit_usaha_id" @if($isSingleUnitManager) disabled @endif>
                        @if($isBumdesAdmin)
                            {{-- Opsi untuk admin BUMDes --}}
                            <option value="">-- Tampilkan Gabungan (Semua Unit & Pusat) --</option>
                            <option value="pusat">-- Hanya BUMDes Pusat --</option>
                        @elseif(!$isSingleUnitManager)
                             {{-- Opsi untuk manajer multi-unit --}}
                            <option value="">-- Tampilkan Semua Unit Saya --</option>
                        @endif

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
            @endif
            {{-- AKHIR PENYESUAIAN --}}

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-eye"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop
