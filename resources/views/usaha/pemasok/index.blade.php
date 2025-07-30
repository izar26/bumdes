@extends('adminlte::page')
@section('title', 'Manajemen Pemasok')
@section('content_header')
    <h1>Manajemen Pemasok</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pemasok</h3>
        <div class="card-tools">
            <a href="{{ route('usaha.pemasok.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Pemasok
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
        <table id="table-pemasok" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pemasok</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pemasoks as $key => $pemasok)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $pemasok->nama_pemasok }}</td>
                        <td>{{ $pemasok->no_telepon ?? '-' }}</td>
                        <td>{{ $pemasok->email ?? '-' }}</td>
                        <td>
                            <a href="{{ route('usaha.pemasok.edit', $pemasok->pemasok_id) }}" class="btn btn-info btn-xs">Edit</a>
                            <form action="{{ route('usaha.pemasok.destroy', $pemasok->pemasok_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pemasok ini?');">
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
@section('plugins.Datatables', true)
@section('js')
<script>
    $('#table-pemasok').DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
    });
</script>
@stop
