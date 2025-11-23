@extends('adminlte::page')

@section('title', 'Jenis Simpanan')

@section('content_header')
    <h1><i class="fas fa-list"></i> Manajemen Jenis Simpanan</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Jenis Simpanan</h3>
                    <div class="card-tools">
                        <a href="{{ route('simpanan.jenis-simpanan.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Jenis Simpanan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Kode</th>
                                <th>Nama Jenis</th>
                                <th>Deskripsi</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jenisSimpanan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>**{{ $item->kode_jenis }}**</td>
                                    <td>{{ $item->nama_jenis }}</td>
                                    <td>{{ $item->deskripsi ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('simpanan.jenis-simpanan.edit', $item->jenis_simpanan_id) }}" class="btn btn-xs btn-info" title="Edit"><i class="fas fa-edit"></i></a>

                                        <form action="{{ route('simpanan.jenis-simpanan.destroy', $item->jenis_simpanan_id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus jenis simpanan ini?')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Inisialisasi DataTables jika diperlukan
        $(document).ready(function() {
            $('.dataTable').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop
