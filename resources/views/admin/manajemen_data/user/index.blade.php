@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content_header')
    <h1>Manajemen User</h1>
@stop

@section('content')
    <a href="{{ route('admin.manajemen-data.user.create') }}" class="btn btn-primary mb-3">Tambah User</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Terakhir Kali Login</th>
                <th>Aksi</th>
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
                        {{ Str::title(str_replace('_', ' ', $user->getRoleNames()->first())) }}
                    @else
                        Belum ada jabatan
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.manajemen-data.user.toggleActive', $user->user_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-secondary' }}"
                            @if(Auth::id() == $user->user_id) disabled @endif>
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </td>
                <td>
                    {{ $user->last_login ? $user->last_login->format('d-m-Y H:i') : 'Belum pernah login' }}
                </td>
                <td>
                    <a href="{{ route('admin.manajemen-data.user.edit', $user->user_id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.manajemen-data.user.destroy', $user->user_id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')"
                            @if(Auth::id() == $user->user_id) disabled @endif>Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@stop
