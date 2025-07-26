@extends('adminlte::page')

@section('title', 'Kelola Berita')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola Berita</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ $message }}
                </div>
            @endif

            <div class="row">
                {{-- Form Input/Edit di Kiri --}}
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $berita_edit->exists ? 'Edit Berita' : 'Tambah Berita' }}</h3>
                        </div>
                        
                        <form action="{{ $berita_edit->exists ? route('admin.berita.update', $berita_edit->id) : route('admin.berita.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($berita_edit->exists)
                                @method('PUT')
                            @endif

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="judul">Judul Berita</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $berita_edit->judul) }}">
                                    @error('judul') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="konten">Konten Singkat</label>
                                    <textarea class="form-control @error('konten') is-invalid @enderror" id="konten" name="konten" rows="4">{{ old('konten', $berita_edit->konten) }}</textarea>
                                    @error('konten') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="gambar">Gambar (Opsional)</label>
                                    <input type="file" class="form-control-file @error('gambar') is-invalid @enderror" id="gambar" name="gambar">
                                    @error('gambar') <span class="text-danger">{{ $message }}</span> @enderror
                                    
                                    @if($berita_edit->gambar)
                                        <div class="mt-2">
                                            <small>Gambar saat ini:</small><br>
                                            <img src="{{ asset('storage/' . $berita_edit->gambar) }}" alt="{{ $berita_edit->judul }}" style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ $berita_edit->exists ? 'Update' : 'Simpan' }}</button>
                                @if($berita_edit->exists)
                                    <a href="{{ route('admin.berita.index') }}" class="btn btn-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tabel Data di Kanan --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Berita</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">No.</th>
                                        <th>Gambar</th>
                                        <th>Judul</th>
                                        <th>Konten</th>
                                        <th style="width: 150px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($beritas as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if($item->gambar)
                                                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" style="max-width: 100px;">
                                                @else
                                                    <small>Tidak ada gambar</small>
                                                @endif
                                            </td>
                                            <td>{{ $item->judul }}</td>
                                            <td>{{ Str::limit($item->konten, 50) }}</td>
                                            <td>
                                                <a href="{{ route('admin.berita.index', ['edit' => $item->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <form action="{{ route('admin.berita.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada data berita.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop