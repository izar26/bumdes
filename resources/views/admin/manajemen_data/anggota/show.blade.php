@extends('adminlte::page')

@section('title', 'Detail Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi Lengkap Anggota: {{ $anggota->nama_lengkap ?? 'Nama Tidak Ada' }}</h3>
                </div>
                <div class="card-body box-profile">
                    {{-- Bagian Foto Profil --}}
                    <div class="text-center mb-4">
                        @php
                            $photoUrl = $anggota->photo
                                        ? Storage::url($anggota->photo)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($anggota->nama_lengkap ?? 'Anggota') . '&background=007bff&color=fff&size=150';
                        @endphp
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $photoUrl }}"
                             alt="Foto Profil Anggota"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>

                    <h3 class="profile-username text-center">{{ $anggota->nama_lengkap ?? '-' }}</h3>
                    <p class="text-muted text-center">{{ $anggota->jabatan ?? 'Tidak Ada Jabatan' }}</p>

                    <hr>

                    {{-- Informasi Dasar --}}
                    <h5 class="mt-4 mb-2">Data Pribadi</h5>
                    <dl class="row">
                        <dt class="col-sm-3">ID Anggota:</dt>
                        <dd class="col-sm-9">{{ $anggota->anggota_id }}</dd>

                        <dt class="col-sm-3">NIK:</dt>
                        <dd class="col-sm-9">{{ $anggota->nik }}</dd>

                        <dt class="col-sm-3">Jenis Kelamin:</dt>
                        <dd class="col-sm-9">{{ $anggota->jenis_kelamin ?? '-' }}</dd>

                        <dt class="col-sm-3">No. Telepon:</dt>
                        <dd class="col-sm-9">{{ $anggota->no_telepon ?? '-' }}</dd>

                        <dt class="col-sm-3">Alamat:</dt>
                        <dd class="col-sm-9">{{ $anggota->alamat ?? '-' }}</dd>
                    </dl>

                    <hr>

                    {{-- Informasi Akun dan Keanggotaan --}}
                    <h5 class="mt-4 mb-2">Informasi Akun & Keanggotaan</h5>
                    <dl class="row">
                        <dt class="col-sm-3">ID Pengguna:</dt>
                        <dd class="col-sm-9">{{ $anggota->user_id ?? '-' }}</dd>

                        <dt class="col-sm-3">Email Akun:</dt>
                        <dd class="col-sm-9">{{ optional($anggota->user)->email ?? 'Tidak ada akun' }}</dd>

                        <dt class="col-sm-3">Tanggal Daftar:</dt>
                        <dd class="col-sm-9">{{ $anggota->tanggal_daftar ? \Carbon\Carbon::parse($anggota->tanggal_daftar)->translatedFormat('d F Y') : '-' }}</dd>

                        <dt class="col-sm-3">Unit Usaha:</dt>
                        <dd class="col-sm-9">{{ optional($anggota->unitUsaha)->nama_unit ?? '--' }}</dd>

                        <dt class="col-sm-3">Status Anggota:</dt>
                        <dd class="col-sm-9">{{ $anggota->status_anggota }}</dd>

                        <dt class="col-sm-3">Profil Lengkap:</dt>
                        <dd class="col-sm-9">
                            @if($anggota->is_profile_complete)
                                <span class="badge badge-success">Lengkap</span>
                            @else
                                <span class="badge badge-warning">Belum Lengkap</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.manajemen-data.anggota.edit', $anggota->anggota_id) }}" class="btn btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit Data Anggota
                    </a>
                    <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .profile-user-img {
            border: 3px solid #ced4da;
            padding: 3px;
        }
    </style>
@stop
