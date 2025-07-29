@extends('adminlte::page')


@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>Daftar Kategori</h2>
        <a href="{{ route('usaha.kategori.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>
    <div class="card-body">
        @if ($kategoris->isEmpty())
            <div class="alert alert-warning" role="alert">
                Belum ada kategori yang terdaftar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoris as $kategori)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $kategori->nama_kategori }}</td>
                                <td>{{ $kategori->deskripsi ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('usaha.kategori.edit', $kategori->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                                    <form action="{{ route('usaha.kategori.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Menghapus kategori akan mengatur kategori produk terkait menjadi NULL.');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
