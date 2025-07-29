@extends('adminlte::page')

@section('title', 'Manajemen Produk')

@section('content_header')
    <h1>Manajemen Produk</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Produk</h3>
        <div class="card-tools">
            <a href="{{ route('produk.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                {{ session('success') }}
            </div>
        @endif
        <table id="table-produk" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Unit Usaha</th>
                    <th>Harga Jual</th>
                    <th>Satuan</th>
                    <th>Deskripsi</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produks as $key => $produk)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $produk->nama_produk }}</td>
                        <td>{{ $produk->unitUsaha->nama_unit ?? 'N/A' }}</td>
                        <td>{{ 'Rp ' . number_format($produk->harga_jual, 0, ',', '.') }}</td>
                        <td>{{ $produk->deskripsi_produk ?? 'Tidak ada deskripsi' }}</td>
                        <td>{{ $produk->kategori ?? 'Umum' }}</td>
                        <td>{{ $produk->satuan_unit }}</td>
                        <td>
                            <a href="{{ route('produk.edit', $produk->produk_id) }}" class="btn btn-info btn-xs">Edit</a>
                            <form action="{{ route('produk.destroy', $produk->produk_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

{{-- Aktifkan plugin datatables & sweetalert2 --}}
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('js')
<script>
    $(document).ready(function() {
        $('#table-produk').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@stop
