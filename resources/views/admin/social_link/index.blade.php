@extends('adminlte::page')

@section('title', 'Link Media Sosial')

@section('content_header')
    <h1 class="m-0 text-dark">Kelola Link Media Sosial</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible">
                    <i class="icon fas fa-check"></i> {{ $message }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $link_edit->exists ? 'Edit Link' : 'Tambah Link' }}</h3>
                        </div>
                        <form action="{{ $link_edit->exists ? route('admin.social_link.update', $link_edit->id) : route('admin.social_link.store') }}" method="POST">
                            @csrf
                            @if($link_edit->exists) @method('PUT') @endif
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="platform">Nama Platform</label>
                                    <input type="text" name="platform" class="form-control" value="{{ old('platform', $link_edit->platform) }}" placeholder="Contoh: Facebook">
                                </div>
                                <div class="form-group">
                                    <label for="icon">Ikon Font Awesome</label>
                                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $link_edit->icon) }}" placeholder="Contoh: fa-brands fa-facebook">
                                    <small class="form-text text-muted">Cari ikon di <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a>.</small>
                                </div>
                                <div class="form-group">
                                    <label for="url">URL Lengkap</label>
                                    <input type="url" name="url" class="form-control" value="{{ old('url', $link_edit->url) }}" placeholder="https://www.facebook.com/namahalaman">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ $link_edit->exists ? 'Update' : 'Simpan' }}</button>
                                @if($link_edit->exists)
                                    <a href="{{ route('admin.social_link.index') }}" class="btn btn-secondary">Batal</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Daftar Link</h3></div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Ikon</th>
                                        <th>Platform</th>
                                        <th>URL</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($links as $link)
                                        <tr>
                                            <td><i class="{{ $link->icon }} fa-2x"></i></td>
                                            <td>{{ $link->platform }}</td>
                                            <td>{{ $link->url }}</td>
                                            <td>
                                                <a href="{{ route('admin.social_link.index', ['edit' => $link->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <form action="{{ route('admin.social_link.destroy', $link->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus link ini?')">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">Belum ada link.</td></tr>
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