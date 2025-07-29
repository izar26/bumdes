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
                        <th>Status</th> {{-- <--- NEW: Column for user status --}}
                        <th>Unit Usaha Dikelola</th> {{-- <--- NEW: Column for managed units --}}
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
                                {{-- NEW: Display user active status --}}
                                @if ($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                {{-- NEW: Display units managed by this user --}}
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

                                {{-- NEW: Toggle Active/Inactive Form instead of Delete --}}
                                <form action="{{ route('admin.manajemen-data.user.toggleActive', $user->user_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="btn btn-{{ $user->is_active ? 'danger' : 'success' }} btn-xs"
                                            onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna {{ $user->name }}?')">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                {{-- Optional: If you still want a permanent delete button for super admins --}}
                                {{-- @if (Auth::user()->isSuperAdmin()) --}}
                                {{-- <form action="{{ route('admin.user.destroy', $user->user_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('PERINGATAN: Ini akan menghapus pengguna secara permanen. Lanjutkan?')">Hapus Permanen</button>
                                </form> --}}
                                {{-- @endif --}}
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

@section('css')
    {{-- Add any specific CSS for this page if needed --}}
@stop

@section('js')
    {{-- Add any specific JS for this page if needed --}}
@stop
