@extends('adminlte::page')

@section('title', 'Manajemen Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Anggota</h3>
                    <div class="card-tools">
                        @can('admin_bumdes')
                            <a href="{{ route('admin.manajemen-data.anggota.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Anggota Baru
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="manajemen-anggota-table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No.</th>
                                <th>Nama Lengkap</th>
                                <th>Email Akun</th>
                                <th>NIK</th>
                                <th>No. Telepon</th>
                                <th>Unit Usaha</th>
                                <th style="width: 25%">Jabatan dan Aksi</th>
                                <th style="width: 5%">Edit</th>
                            </tr>
                        </thead>
                       <tbody>
    @foreach ($anggotas as $index => $anggota)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $anggota->nama_lengkap }}</td>
            <td>{{ optional($anggota->user)->email ?? '-' }}</td>
            <td>{{ $anggota->nik ?? '-' }}</td>
            <td>{{ $anggota->no_telepon ?? '-' }}</td>
            <td>{{ optional($anggota->unitUsaha)->nama_unit ?? '-' }}</td>
            <td>
                @if ($anggota->user && $anggota->user->hasRole('admin_bumdes'))
                    <span class="badge badge-warning">
                        {{ Str::title(str_replace('_', ' ', $anggota->user->getRoleNames()->first())) }}
                    </span>
                @elseif ($anggota->user)
                    <form action="{{ route('admin.manajemen-data.anggota.updateRole', $anggota->user->user_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="input-group">
                            <select name="role" class="form-control">
                                @foreach($rolesOptions as $role)
                                    <option value="{{ $role }}" @if($anggota->user->hasRole($role)) selected @endif>
                                        {{ Str::title(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success">Ubah</button>
                            </div>
                        </div>
                    </form>
                @else
                    <span class="text-danger">Tidak ada akun</span>
                @endif
            </td>
            <td>
                      <a href="{{ route('admin.manajemen-data.anggota.edit', $anggota->anggota_id) }}" class="btn btn-sm btn-info">
        <i class="fas fa-edit"></i>
    </a>
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

@push('js')
    <script>
        $(function() {
            $('#manajemen-anggota-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@endpush
@section('plugins.Datatables', true)
