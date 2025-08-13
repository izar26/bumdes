@extends('adminlte::page')

@section('title', 'Manajemen Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Session Alerts --}}
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
                    <h3 class="card-title">Daftar Anggota</h3>
                    <div class="card-tools">
                        @can('admin_bumdes')
                            <a href="{{ route('admin.manajemen-data.anggota.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Anggota Baru
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="manajemen-anggota-table">
                        <thead>
                            <tr>
                                <th style="width: 5%">No.</th>
                                <th style="width: 10%">Foto</th>
                                <th style="width: 35%">Info Anggota</th>
                                <th style="width: 25%">Jabatan</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($anggotas as $index => $anggota)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @php
                                        $photoUrl = $anggota->photo
                                            ? Storage::url($anggota->photo)
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($anggota->nama_lengkap) . '&background=007bff&color=fff&size=50';
                                    @endphp
                                    <img src="{{ $photoUrl }}" alt="Foto {{ $anggota->nama_lengkap }}" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $anggota->nama_lengkap ?? 'Nama Tidak Ada' }}</strong>
                                    </div>
                                    <div class="text-muted">
                                        <div>
                                            <i class="fas fa-envelope fa-fw mr-1"></i>
                                            {{ optional($anggota->user)->email ?? '-' }}
                                        </div>
                                        <div>
                                            <i class="fas fa-phone fa-fw mr-1"></i>
                                            {{ $anggota->no_telepon ?? '-' }}
                                        </div>
                                        <div>
                                            <i class="fas fa-map-marker-alt fa-fw mr-1"></i>
                                            <small>{{ $anggota->alamat ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- Logika untuk menampilkan dropdown jabatan interaktif --}}
                                    @can('admin_bumdes')
                                        @if ($anggota->user && $anggota->user->hasRole('admin_bumdes'))
                                            <span class="badge badge-warning">Admin Bumdes</span>
                                        @elseif ($anggota->user)
                                            <form action="{{ route('admin.manajemen-data.anggota.updateRole', $anggota->user->user_id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group">
                                                    <select name="role" class="form-control form-control-sm">
                                                        @foreach($rolesOptions as $role)
                                                            <option value="{{ $role }}" @if($anggota->user->hasRole($role)) selected @endif>
                                                                {{ Str::title(str_replace('_', ' ', $role)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-sm btn-success">Ubah</button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <span class="badge badge-secondary">Tidak Ada Akun</span>
                                        @endif
                                    @else
                                        @if ($anggota->user)
                                            <span class="badge badge-info">{{ Str::title(str_replace('_', ' ', $anggota->user->getRoleNames()->first() ?? 'Anggota')) }}</span>
                                        @else
                                            <span class="badge badge-secondary">Tidak Ada Akun</span>
                                        @endif
                                    @endcan
                                </td>
                                <td>
                                      <a href="{{ route('admin.manajemen-data.user.show', $anggota->anggota_id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    {{-- Tombol Aksi dengan Teks --}}
                                    <form id="delete-form-{{ $anggota->anggota_id }}" action="{{ route('admin.manajemen-data.anggota.destroy', $anggota->anggota_id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <div class="btn-group">
                                        @can('admin_bumdes')
                                        <a href="{{ route('admin.manajemen-data.anggota.edit', $anggota->anggota_id) }}" class="btn btn-sm btn-info">Edit</a>
                                        @endcan
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
        modalId="anggotaActionModal"
        title="Konfirmasi Aksi"
        body="Apakah Anda yakin dengan tindakan ini?"
        confirmButtonText="Lanjutkan"
        confirmButtonClass="btn-primary" />
@stop

@push('js')
<script>
    $(function () {
        $('#manajemen-anggota-table').DataTable({
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
    });
</script>
@endpush

@section('plugins.Datatables', true)
