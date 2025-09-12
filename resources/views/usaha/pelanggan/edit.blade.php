@extends('adminlte::page')

@section('title', 'Edit Pelanggan')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Pelanggan</h1>
@stop

@section('content')
    <form action="{{ route('usaha.pelanggan.update', $pelanggan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="nama">Nama Pelanggan</label>
                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $pelanggan->nama) }}" required>
                    @error('nama') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $pelanggan->alamat) }}" required>
                    @error('alamat') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                   <div class="form-group">
                    <label for="alamat">Kontak  </label>
                    <input type="text" name="kontak" id="kontak" class="form-control @error('kontak') is-invalid @enderror" value="{{ old('kontak', $pelanggan->kontak) }}" required>
                    @error('kontak') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="status_pelanggan">Status Pelanggan</label>
                    <select name="status_pelanggan" id="status_pelanggan" class="form-control @error('status_pelanggan') is-invalid @enderror" required>
                        <option value="Aktif" {{ old('status_pelanggan', $pelanggan->status_pelanggan) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ old('status_pelanggan', $pelanggan->status_pelanggan) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status_pelanggan') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('usaha.pelanggan.index') }}" class="btn btn-default">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </form>
@stop
