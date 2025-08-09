@extends('adminlte::page')

@section('title', 'Beranda Anggota')

@section('content_header')
    <h1>Selamat Datang, {{ Auth::user()->name }}!</h1>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <x-adminlte-card title="Informasi Profil Anda" theme="info" icon="fas fa-user-circle" collapsible>
                    <p><strong>Nama Lengkap:</strong> {{ $anggota->nama_lengkap }}</p>
                    <p><strong>Email:</strong> {{ $anggota->user->email }}</p>
                    <p><strong>NIK:</strong> {{ $anggota->nik ?? 'Belum diisi' }}</p>
                    <p><strong>Tanggal Lahir:</strong> {{ $anggota->tanggal_lahir ?? 'Belum diisi' }}</p>
                    <p><strong>Alamat:</strong> {{ $anggota->alamat ?? 'Belum diisi' }}</p>
                    <p><strong>Nomor Telepon:</strong> {{ $anggota->nomor_telepon ?? 'Belum diisi' }}</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">Edit Profil</a>
                </x-adminlte-card>
            </div>

            <div class="col-md-6">
                <x-adminlte-card title="Anggota Lain" theme="secondary" icon="fas fa-users" collapsible>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($anggotaLain as $item)
                                    <tr>
                                        <td>{{ $item->nama_lengkap }}</td>
                                        <td><span class="badge badge-{{ $item->status_anggota == 'aktif' ? 'success' : 'danger' }}">{{ Str::title($item->status_anggota) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Tidak ada anggota lain.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $anggotaLain->links() }}
                </x-adminlte-card>
            </div>
        </div>
    </div>
@stop
