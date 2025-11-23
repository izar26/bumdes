@extends('adminlte::page')

@section('title', 'Kelola Potensi')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola Potensi</h1>
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
                            <h3 class="card-title">{{ $potensi_edit->exists ? 'Edit Potensi' : 'Tambah Potensi' }}</h3>
                        </div>

                        <form action="{{ $potensi_edit->exists ? route('admin.potensi.update', $potensi_edit->id) : route('admin.potensi.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($potensi_edit->exists)
                                @method('PUT')
                            @endif

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="judul">Nama Potensi</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $potensi_edit->judul) }}" placeholder="Contoh: Pariwisata Alam">
                                    @error('judul') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi Singkat</label>
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan deskripsi singkat">{{ old('deskripsi', $potensi_edit->deskripsi) }}</textarea>
                                    @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="gambar">Gambar</label>
                                    <input type="file" class="form-control-file @error('gambar') is-invalid @enderror" id="gambar" name="gambar">
                                    @error('gambar') <span class="text-danger">{{ $message }}</span> @enderror

                                    @if($potensi_edit->gambar)
                                        <div class="mt-2">
                                            <small>Gambar saat ini:</small><br>
                                            <img src="{{ asset('storage/' . $potensi_edit->gambar) }}" alt="{{ $potensi_edit->judul }}" style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ $potensi_edit->exists ? 'Update' : 'Simpan' }}</button>
                                @if($potensi_edit->exists)
                                    <a href="{{ route('admin.potensi.index') }}" class="btn btn-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tabel Data di Kanan --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Potensi Desa</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">No.</th>
                                        <th>Gambar</th>
                                        <th>Judul</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 150px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($potensis as $item)
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
                                            <td>{{ Str::limit($item->deskripsi, 50) }}</td>
                                            <td>
                                                <a href="{{ route('admin.potensi.index', ['edit' => $item->id]) }}" class="btn btn-sm btn-primary">Edit</a>

                                                <form id="delete-potensi-{{ $item->id }}" action="{{ route('admin.potensi.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-toggle="modal" data-target="#confirmModal"
                                                        data-form-id="delete-potensi-{{ $item->id }}"
                                                        data-title="Konfirmasi Hapus"
                                                        data-body="Yakin ingin menghapus data ini?"
                                                        data-button-text="Hapus"
                                                        data-button-class="btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada data potensi.</td>
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
