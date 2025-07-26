@extends('adminlte::page')

@section('title', 'Kelola Profil Desa')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola Profil Desa</h1>
@stop

@section('content')
    <form action="{{ route('admin.profil.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="icon fas fa-check"></i> {{ $message }}
                            </div>
                        @endif
                        
                        <h4>Data Utama Desa</h4>
                        
                        <div class="form-group">
                            <label for="nama_desa">Nama Desa</label>
                            <input type="text" name="nama_desa" class="form-control @error('nama_desa') is-invalid @enderror" value="{{ old('nama_desa', $profil->nama_desa) }}">
                             @error('nama_desa') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="logo">Logo Desa (Kosongkan jika tidak ingin ganti)</label>
                            <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror">
                             @error('logo') <span class="text-danger">{{ $message }}</span> @enderror
                             @if($profil->logo)
                                <div class="mt-2">
                                    <small>Logo saat ini:</small><br>
                                    <img src="{{ asset('storage/' . $profil->logo) }}" alt="Logo Desa" style="max-height: 80px; background-color: #f8f9fa; padding: 5px; border-radius: 4px;">
                                </div>
                            @endif
                        </div>

                        <hr>
                        
                        <h4>Data Profil Halaman Depan</h4>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Desa</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="5">{{ old('deskripsi', $profil->deskripsi) }}</textarea>
                            @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jumlah_penduduk">Jumlah Penduduk</label>
                                    <input type="number" name="jumlah_penduduk" class="form-control @error('jumlah_penduduk') is-invalid @enderror" value="{{ old('jumlah_penduduk', $profil->jumlah_penduduk) }}">
                                    @error('jumlah_penduduk') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jumlah_kk">Jumlah KK</label>
                                    <input type="number" name="jumlah_kk" class="form-control @error('jumlah_kk') is-invalid @enderror" value="{{ old('jumlah_kk', $profil->jumlah_kk) }}">
                                    @error('jumlah_kk') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="luas_wilayah">Luas Wilayah (Contoh: 500 Ha)</label>
                                    <input type="text" name="luas_wilayah" class="form-control @error('luas_wilayah') is-invalid @enderror" value="{{ old('luas_wilayah', $profil->luas_wilayah) }}">
                                    @error('luas_wilayah') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4>Data Kontak (Footer)</h4>
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $profil->alamat) }}">
                             @error('alamat') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $profil->email) }}">
                                     @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telepon">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $profil->telepon) }}">
                                     @error('telepon') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop