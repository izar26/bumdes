@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen User</h1>
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
                    <h3 class="card-title">Daftar Pengguna</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.manajemen-data.anggota.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah User Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="user-table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No.</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jabatan</th>
                                <th>Terakhir Login</th>
                                <th style="width: 20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->roles->isNotEmpty())
                                        <span class="badge badge-info">{{ Str::title(str_replace('_', ' ', $user->getRoleNames()->first())) }}</span>
                                    @else
                                        <span class="badge badge-secondary">Belum ada jabatan</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $user->last_login ? $user->last_login->format('d M Y, H:i') : 'Belum pernah login' }}
                                </td>
                                <td>
                                    <form id="toggle-active-form-{{ $user->user_id }}" action="{{ route('admin.manajemen-data.user.toggleActive', $user->user_id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PUT')
                                    </form>

                                    <form id="delete-form-{{ $user->user_id }}" action="{{ route('admin.manajemen-data.user.destroy', $user->user_id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <div class="btn-group">
                                        <a href="{{ route('admin.manajemen-data.anggota.edit', $user->user_id) }}" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit User">
                                            Edit
                                        </a>

                                        @if(Auth::id() != $user->user_id)
                                         <button type="button" class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
    data-toggle="modal"
    data-target="#userActionModal"
    data-form-id="toggle-active-form-{{ $user->user_id }}"
    data-title="Konfirmasi Status Pengguna"
    data-body="Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna '{{ $user->name }}'?"
    data-button-text="Ya, {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
    data-button-class="{{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
    data-toggle="tooltip" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
    {{-- BARIS INI YANG DIUBAH --}}
    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
</button>

                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-toggle="modal"
                                                data-target="#userActionModal"
                                                data-form-id="delete-form-{{ $user->user_id }}"
                                                data-title="Konfirmasi Hapus Pengguna"
                                                data-body="Apakah Anda yakin ingin menghapus pengguna '{{ $user->name }}'? Tindakan ini tidak dapat dibatalkan."
                                                data-button-text="Ya, Hapus"
                                                data-button-class="btn-danger"
                                                data-toggle="tooltip" title="Hapus User">
                                              Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-modal
        modalId="userActionModal"
        title="Konfirmasi Aksi"
        body="Apakah Anda yakin?"
        confirmButtonText="Lanjutkan"
        confirmButtonClass="btn-primary" />
@stop

@push('js')
<script>
    $(function () {
        // Inisialisasi DataTables untuk tabel pengguna
        $('#user-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });

        // Inisialisasi Tooltip
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

@section('plugins.Datatables', true)
