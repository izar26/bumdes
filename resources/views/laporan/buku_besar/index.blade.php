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

            <div class="form-group">
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
                <i class="fas fa-eye"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@stop

{{-- Tambahkan script untuk Select2 jika Anda menggunakannya agar dropdown lebih interaktif --}}
@section('plugins.Select2', true)
@section('js')
<script>
    $(document).ready(function() {
        $('#akun_id').select2();
    });
</script>
@stop