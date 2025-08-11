@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8"> {{-- Menggunakan grid untuk memusatkan form --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit User</h3>
                </div>
                <form action="{{ route('admin.manajemen-data.user.update', $user->user_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" id="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" id="email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.manajemen-data.user.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
            </div>
    </div>
@stop
