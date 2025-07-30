@extends('adminlte::page')

@section('title', 'Daftar Pengguna')

@section('content_header')
    <h1>Daftar Pengguna</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manajemen Pengguna Sistem</h3>
            <div class="card-tools">
                <a href="{{ route('admin.manajemen-data.user.create') }}" class="btn btn-primary btn-sm">Tambah Pengguna Baru</a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Unit Usaha Dikelola</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username ?? '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $rolesOptions[$user->role] ?? $user->role }}</td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->role === 'manajer_unit_usaha' && $user->unitUsahas->isNotEmpty())
                                    <ul>
                                        @foreach ($user->unitUsahas as $unitUsaha)
                                            <li>{{ $unitUsaha->nama_unit }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.manajemen-data.user.show', $user->user_id) }}" class="btn btn-info btn-xs">Detail</a>
                                <a href="{{ route('admin.manajemen-data.user.edit', $user->user_id) }}" class="btn btn-warning btn-xs">Edit</a>

                                {{-- Toggle Active/Non-Active Form --}}
                                <form id="toggle-form-{{ $user->user_id }}"
                                    action="{{ route('admin.manajemen-data.user.toggleActive', $user->user_id) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="button"
                                        class="btn btn-{{ $user->is_active ? 'danger' : 'success' }} btn-xs"
                                        data-toggle="modal"
                                        data-target="#confirmModal" {{-- Change this to a generic modal ID like 'confirmModal' --}}
                                        data-form-id="toggle-form-{{ $user->user_id }}"
                                        data-title="Konfirmasi Perubahan Status" {{-- Dynamic Title --}}
                                        data-body="Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna {{ $user->name }}?"
                                        data-button-text="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        data-button-class="btn-{{ $user->is_active ? 'danger' : 'success' }}">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                {{-- Delete Form --}}
                                <form id="delete-form-{{ $user->user_id }}"
                                    action="{{ route('admin.manajemen-data.user.destroy', $user->user_id) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-danger btn-xs"
                                            data-toggle="modal"
                                            data-target="#confirmModal" {{-- Use the same generic modal ID --}}
                                            data-form-id="delete-form-{{ $user->user_id }}"
                                            data-title="Konfirmasi Penghapusan" {{-- Dynamic Title --}}
                                            data-body="Apakah Anda yakin ingin menghapus pengguna {{ $user->name }} secara permanen?"
                                            data-button-text="Hapus Permanen"
                                            data-button-class="btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada pengguna yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@include('components.confirm-modal', [
    'modalId' => 'confirmModal',
    'title' => 'Konfirmasi Aksi',
    'body' => 'Apakah Anda yakin?',
    'confirmButtonText' => 'Lanjutkan',
    'confirmButtonClass' => 'btn-primary',
    'actionFormId' => ''
])
