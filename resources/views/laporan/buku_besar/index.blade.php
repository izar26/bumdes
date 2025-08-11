@extends('adminlte::page')

@section('title', 'Laporan Buku Besar')

@section('content_header')
    <h1>Laporan Buku Besar</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan Buku Besar</h3>
    </div>
    <form action="{{ route('laporan.buku-besar.generate') }}" method="POST" target="_blank">
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
                    <label for="akun_id">Pilih Akun</label>
                    <select class="form-control" id="akun_id" name="akun_id" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach ($akuns as $akun)
                            <option value="{{ $akun->akun_id }}">
                                [{{ $akun->kode_akun }}] {{ $akun->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- HANYA TAMPILKAN FILTER INI UNTUK BENDAHARA --}}
                @if(auth()->user()->hasRole('bendahara_bumdes'))
                <div class="form-group col-md-6">
                    <label for="unit_usaha_id">Filter Unit Usaha</label>
                    <select class="form-control" id="unit_usaha_id" name="unit_usaha_id">
                        <option value="">-- Semua Unit Usaha --</option>
                        @foreach ($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}">{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="end_date">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-t') }}" required>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)
@section('js')
<script>
    $(document).ready(function() {
        $('#akun_id').select2();
        $('#unit_usaha_id').select2();
    });
</script>
@stop
