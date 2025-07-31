@extends('adminlte::page')

@section('title', 'Edit Kategori Usaha')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Kategori Usaha</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Kategori</h3>
                </div>
                <form action="{{ route('usaha.kategori.update', $kategori->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text"
                                   class="form-control @error('nama_kategori') is-invalid @enderror"
                                   id="nama_kategori"
                                   name="nama_kategori"
                                   value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                                   placeholder="Masukkan nama kategori"
                                   required>
                            @error('nama_kategori')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                      id="deskripsi"
                                      name="deskripsi"
                                      rows="3"
                                      placeholder="Masukkan deskripsi kategori">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('usaha.kategori.index') }}" class="btn btn-default float-right">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Informasi Kategori</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Dibuat Pada</dt>
                        <dd class="col-sm-8">{{ $kategori->created_at->format('d F Y H:i') }}</dd>

                        <dt class="col-sm-4">Diperbarui Pada</dt>
                        <dd class="col-sm-8">{{ $kategori->updated_at->format('d F Y H:i') }}</dd>

                        <dt class="col-sm-4">Jumlah Produk</dt>
                        <dd class="col-sm-8">{{ $kategori->produks_count ?? '0' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .card-primary.card-outline {
            border-top: 3px solid #007bff;
        }
    </style>
@stop
