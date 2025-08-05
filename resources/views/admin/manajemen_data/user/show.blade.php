@extends('adminlte::page')

@section('title', 'Detail Pengguna')

@section('content_header')
    <h1>Detail Pengguna: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Pengguna</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Nama Lengkap</b> <span class="float-right">{{ $user->name }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Username</b> <span class="float-right">{{ $user->username ?? '-' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <span class="float-right">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Peran (Role)</b> <span class="float-right">{{ $rolesOptions[$user->role] ?? $user->role }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> <span class="float-right">
                                @if ($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </span>
                        </li>
                    </ul>
                    <a href="{{ route('admin.manajemen-data.user.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Unit Usaha yang Ditugaskan</h3>
                </div>
                <div class="card-body">
                    @if ($user->unitUsahas->count() > 0)
                        <ul class="list-group">
                            @foreach ($user->unitUsahas as $unitUsaha)
                                <li class="list-group-item">{{ $unitUsaha->nama_unit }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Tidak ada unit usaha yang ditugaskan.</p>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Waktu Pembuatan & Pembaruan</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Dibuat pada</b> <span class="float-right">{{ $user->created_at->format('d-m-Y H:i') }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Terakhir diperbarui</b> <span class="float-right">{{ $user->updated_at->format('d-m-Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
