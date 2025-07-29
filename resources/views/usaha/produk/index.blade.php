@extends('adminlte::page')

@section('title', 'Daftar Produk')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>Daftar Produk</h2>
        <a href="{{ route('usaha.produk.create') }}" class="btn btn-primary">Tambah Produk</a>
    </div>
    <div class="card-body">
        @if ($produks->isEmpty())
            <div class="alert alert-warning" role="alert">
                Belum ada produk yang terdaftar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Produk</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Satuan</th>
                            <th>Unit Usaha</th>
                            <th>Kategori</th>
                            <th>Stok Minimum</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produks as $produk)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>Rp {{ number_format($produk->harga_beli, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($produk->harga_jual, 2, ',', '.') }}</td>
                                <td>{{ $produk->satuan_unit }}</td>
                                <td>{{ $produk->unitUsaha->nama_unit ?? 'N/A' }}</td>
                                <td>{{ $produk->kategori->nama_kategori ?? 'Tidak Berkategori' }}</td>
                                <td>{{ $produk->stok_minimum }}</td>
                                <td>
                                    <a href="{{ route('usaha.produk.edit', $produk->produk_id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                                    <form action="{{ route('usaha.produk.destroy', $produk->produk_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display:inline;">
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
