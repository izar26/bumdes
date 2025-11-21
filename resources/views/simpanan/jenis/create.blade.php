@extends('adminlte::page')

@section('title', 'Tambah Jenis Simpanan')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Tambah Jenis Simpanan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Jenis Simpanan Baru</h3>
                </div>
                <form action="{{ route('simpanan.jenis-simpanan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="kode_jenis">Kode Jenis</label>
                            <input type="text" name="kode_jenis" class="form-control @error('kode_jenis') is-invalid @enderror" id="kode_jenis" placeholder="Contoh: SP, SW, SS" value="{{ old('kode_jenis') }}" required>
                            @error('kode_jenis')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_jenis">Nama Jenis</label>
                            <input type="text" name="nama_jenis" class="form-control @error('nama_jenis') is-invalid @enderror" id="nama_jenis" placeholder="Contoh: Simpanan Pokok" value="{{ old('nama_jenis') }}" required>
                            @error('nama_jenis')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi (Opsional)</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="{{ route('simpanan.jenis-simpanan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
