@extends('adminlte::page')

@section('title', 'Buka Rekening Baru')

@section('content_header')
    <h1><i class="fas fa-user-plus"></i> Buka Rekening Simpanan Baru</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Form Buka Rekening</h3></div>

                {{-- Pastikan route ini ada di web.php: Route::post('rekening', ...)->name('rekening.store') --}}
                <form action="{{ route('simpanan.rekening.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Pilih Anggota --}}
                        <div class="form-group">
                            <label>Pilih Anggota</label>
                            <select name="anggota_id" class="form-control select2" required>
                                <option value="">-- Cari Anggota --</option>
                                @foreach($anggotas as $anggota)
                                    <option value="{{ $anggota->anggota_id }}">{{ $anggota->nama_lengkap }} - {{ $anggota->nik }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pilih Jenis Simpanan --}}
                        <div class="form-group">
                            <label>Jenis Simpanan</label>
                            <select name="jenis_simpanan_id" class="form-control" required>
                                <option value="">-- Pilih Produk Simpanan --</option>
                                @foreach($jenisSimpanans as $jenis)
                                    <option value="{{ $jenis->jenis_simpanan_id }}">{{ $jenis->nama_jenis }} ({{ $jenis->kode_jenis }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Saldo awal akan diset otomatis menjadi <strong>Rp 0</strong>.
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Buka Rekening</button>
                        <a href="{{ route('simpanan.rekening.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Aktifkan Select2 jika librarynya sudah diload di AdminLTE
        $('.select2').select2();
    });
</script>
@stop
