@extends('adminlte::page')

@section('title', 'Edit Jenis Simpanan')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Jenis Simpanan</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Edit: **{{ $jenisSimpanan->nama_jenis }}**</h3>
                </div>
                <form action="{{ route('simpanan.jenis-simpanan.update', $jenisSimpanan->jenis_simpanan_id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Atau @method('PATCH') --}}
                    <div class="card-body">
                        <div class="form-group">
                            <label for="kode_jenis">Kode Jenis</label>
                            <input type="text" name="kode_jenis" class="form-control @error('kode_jenis') is-invalid @enderror" id="kode_jenis" value="{{ old('kode_jenis', $jenisSimpanan->kode_jenis) }}" required>
                            @error('kode_jenis')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_jenis">Nama Jenis</label>
                            <input type="text" name="nama_jenis" class="form-control @error('nama_jenis') is-invalid @enderror" id="nama_jenis" value="{{ old('nama_jenis', $jenisSimpanan->nama_jenis) }}" required>
                            @error('nama_jenis')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi (Opsional)</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" rows="3">{{ old('deskripsi', $jenisSimpanan->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Perbarui</button>
                        <a href="{{ route('simpanan.jenis-simpanan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
