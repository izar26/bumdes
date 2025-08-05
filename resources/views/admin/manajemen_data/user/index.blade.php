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
            @if (session('success') || session('error') || $errors->any())
                <div class="alert alert-{{ session('success') ? 'success' : (session('error') ? 'danger' : 'danger') }} alert-dismissible fade show" role="alert">
                    @if(session('success'))
                        {{ session('success') }}
                    @elseif(session('error'))
                        {{ session('error') }}
                    @elseif($errors->any())
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="user-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Unit Usaha Dikelola</th>
                            <th style="width: 1%;">Aksi</th>
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
                                    {{-- PERBAIKAN: Periksa peran 'manajer_unit_usaha' ATAU 'admin_unit_usaha' --}}
                                    @if (in_array($user->role, ['manajer_unit_usaha', 'admin_unit_usaha']) && $user->unitUsahas->isNotEmpty())
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
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            <a href="{{ route('admin.manajemen-data.user.show', $user->user_id) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-info"></i> Detail
                                            </a>
                                            <a href="{{ route('admin.manajemen-data.user.edit', $user->user_id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>

                                            {{-- Tombol Toggle Aktif/Nonaktif --}}
                                            <form id="toggle-form-{{ $user->user_id }}" action="{{ route('admin.manajemen-data.user.toggleActive', $user->user_id) }}" method="POST" style="display:none;">
                                                @csrf @method('PUT')
                                            </form>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#confirmModal"
                                                data-form-id="toggle-form-{{ $user->user_id }}"
                                                data-title="Konfirmasi Perubahan Status"
                                                data-body="Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna {{ $user->name }}?"
                                                data-button-text="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                data-button-class="btn-{{ $user->is_active ? 'danger' : 'success' }}">
                                                <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }} text-{{ $user->is_active ? 'danger' : 'success' }}"></i> {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </a>

                                            {{-- Tombol Hapus --}}
                                            <form id="delete-form-{{ $user->user_id }}" action="{{ route('admin.manajemen-data.user.destroy', $user->user_id) }}" method="POST" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#confirmModal"
                                                data-form-id="delete-form-{{ $user->user_id }}"
                                                data-title="Konfirmasi Penghapusan"
                                                data-body="Apakah Anda yakin ingin menghapus pengguna {{ $user->name }} secara permanen? Aksi ini tidak dapat dibatalkan!"
                                                data-button-text="Hapus Permanen"
                                                data-button-class="btn-danger">
                                                <i class="fas fa-trash text-danger"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
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
    </div>

    @include('components.confirm-modal', [
        'modalId' => 'confirmModal',
        'title' => 'Konfirmasi Aksi',
        'body' => 'Apakah Anda yakin?',
        'confirmButtonText' => 'Lanjutkan',
        'confirmButtonClass' => 'btn-primary',
        'actionFormId' => ''
    ])
@stop

{{-- Tambahkan script untuk DataTables --}}
@push('js')
<script>
    $(document).ready(function() {
        $('#user-table').DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>
@endpush

{{-- Konfigurasi AdminLTE untuk DataTables --}}
@section('plugins.Datatables', true)
