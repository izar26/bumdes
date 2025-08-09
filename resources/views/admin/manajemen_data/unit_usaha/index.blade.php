@extends('adminlte::page')

@section('title', 'Unit Usaha')

@section('content_header')
    <h1>Manajemen Unit Usaha</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Unit Usaha</h3>
                    <div class="card-tools">
                        @can('admin_bumdes')
                            <a href="{{ route('admin.manajemen-data.unit_usaha.create') }}" class="btn btn-primary">Tambah Unit Usaha</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table id="unitUsahaTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Unit</th>
                                <th>Jenis Usaha</th>
                                <th>Status Operasi</th>
                                <th>Tanggal Mulai</th>
                                <th>Penanggung Jawab</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unitUsahas as $unitUsaha)
                                <tr>
                                    <td>{{ $unitUsaha->nama_unit }}</td>
                                    <td>{{ $unitUsaha->jenis_usaha }}</td>
                                    <td>{{ $unitUsaha->status_operasi }}</td>
                                    <td>{{ $unitUsaha->tanggal_mulai_operasi ? \Carbon\Carbon::parse($unitUsaha->tanggal_mulai_operasi)->translatedFormat('d F Y') : '-' }}</td>

                                    <td>
                                        @forelse($unitUsaha->users as $user)
                                            {{ $user->name }}@if(!$loop->last), @endif
                                        @empty
                                            -
                                        @endforelse
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.manajemen-data.unit_usaha.edit', $unitUsaha->unit_usaha_id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        @can('admin_bumdes')
                                            <form action="{{ route('admin.manajemen-data.unit_usaha.destroy', $unitUsaha->unit_usaha_id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus unit usaha ini?')">Hapus</button>
                                            </form>
                                        @endcan
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

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $("#unitUsahaTable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#unitUsahaTable_wrapper .col-md-6:eq(0)');
        });
    </script>
@stop
