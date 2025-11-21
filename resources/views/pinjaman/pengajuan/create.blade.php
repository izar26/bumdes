@extends('adminlte::page')

@section('title', 'Buat Pengajuan Pinjaman')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Buat Pengajuan Pinjaman</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Form Pengajuan Pinjaman</h3></div>

                <form action="{{ route('simpanan.pinjaman.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{-- Field Anggota (Harap diisi dengan logika pencarian atau dropdown Anggota) --}}
                        <div class="form-group">
                            <label for="anggota_id">Anggota</label>
                            <select name="anggota_id" class="form-control @error('anggota_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Anggota --</option>
                              @foreach ($anggotas as $anggota)
                                <option value="{{ $anggota->anggota_id }}">{{ $anggota->nama_lengkap }} (NIK: {{ $anggota->nik }})</option>
                               @endforeach
                            </select>
                            @error('anggota_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_pengajuan">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal_pengajuan" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" value="{{ old('tanggal_pengajuan', now()->toDateString()) }}" required>
                            @error('tanggal_pengajuan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="jumlah_pinjaman">Jumlah Pinjaman (Pokok)</label>
                            <input type="number" name="jumlah_pinjaman" class="form-control @error('jumlah_pinjaman') is-invalid @enderror" placeholder="Contoh: 5000000" required>
                            @error('jumlah_pinjaman') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="tenor">Tenor (Bulan)</label>
                            <input type="number" name="tenor" class="form-control @error('tenor') is-invalid @enderror" placeholder="Contoh: 12" required>
                            @error('tenor') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="tujuan_pinjaman">Tujuan Pinjaman</label>
                            <textarea name="tujuan_pinjaman" class="form-control" rows="3">{{ old('tujuan_pinjaman') }}</textarea>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Ajukan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
