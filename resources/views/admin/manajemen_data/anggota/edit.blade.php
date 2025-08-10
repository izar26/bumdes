@extends('adminlte::page')

@section('title', 'Edit Data Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Data Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Anggota</h3>
                </div>
                <form role="form" action="{{ route('admin.manajemen-data.anggota.update', $anggota->anggota_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required>
                                    @error('nama_lengkap') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nik">NIK</label>
                                    <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $anggota->nik) }}" required>
                                    @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                                    @error('alamat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="no_telepon">No Telepon</label>
                                    <input type="text" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}" required>
                                    @error('no_telepon') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" required>
                                        <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="photo">Foto Profil</label>
                                    <input type="file" name="photo" class="form-control-file @error('photo') is-invalid @enderror" id="photo">
                                    @error('photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    @if($anggota->photo)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($anggota->photo) }}" alt="Foto Profil" class="img-thumbnail" width="150">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
