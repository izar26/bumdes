@extends('adminlte::page')

@section('title', 'Tambah Pengguna')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Pengguna</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan!</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.manajemen-data.user.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control"
                               value="{{ old('username') }}">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Akun</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Pilih Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($rolesOptions as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('admin.manajemen-data.user.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
