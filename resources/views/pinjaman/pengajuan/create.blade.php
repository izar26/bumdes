@extends('adminlte::page')

@section('title', 'Catat Pinjaman Baru')

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar"></i> Catat Pinjaman Baru</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success"> {{-- Ubah warna jadi success biar beda --}}
                <div class="card-header">
                    <h3 class="card-title">Formulir Pencatatan Pinjaman</h3>
                </div>

                <form action="{{ route('simpanan.pinjaman.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-info"></i> Info!</h5>
                            Data yang diinput di sini akan <strong>langsung aktif</strong> dan jadwal angsuran akan otomatis terbentuk.
                        </div>

                        <div class="form-group">
                            <label for="anggota_id">Pilih Anggota</label>
                            <select name="anggota_id" class="form-control select2 @error('anggota_id') is-invalid @enderror" required>
                                <option value="">-- Cari Nama / NIK --</option>
                                @foreach ($anggotas as $anggota)
                                    <option value="{{ $anggota->anggota_id }}">
                                        {{ $anggota->nama_lengkap }} - {{ $anggota->nik }}
                                    </option>
                                @endforeach
                            </select>
                            @error('anggota_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_pengajuan">Tanggal Pencairan / Transaksi</label>
                            <input type="date" name="tanggal_pengajuan"
                                   class="form-control @error('tanggal_pengajuan') is-invalid @enderror"
                                   value="{{ old('tanggal_pengajuan', now()->toDateString()) }}" required>
                            <small class="text-muted">Tanggal ini akan menjadi tanggal mulai perhitungan angsuran.</small>
                            @error('tanggal_pengajuan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jumlah_pinjaman">Jumlah Pinjaman (Rp)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="jumlah_pinjaman" class="form-control @error('jumlah_pinjaman') is-invalid @enderror" placeholder="0" required>
                                    </div>
                                    @error('jumlah_pinjaman') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tenor">Tenor (Bulan)</label>
                                    <input type="number" name="tenor" class="form-control @error('tenor') is-invalid @enderror" placeholder="Contoh: 12" required>
                                    @error('tenor') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tujuan_pinjaman">Keterangan / Tujuan (Opsional)</label>
                            <textarea name="tujuan_pinjaman" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('tujuan_pinjaman') }}</textarea>
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('simpanan.pinjaman.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan & Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    {{-- Jika pakai Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Aktifkan Select2 jika classnya ada
            $('.select2').select2();
        });
    </script>
@stop
