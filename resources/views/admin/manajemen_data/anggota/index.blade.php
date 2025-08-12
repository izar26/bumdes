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
                        {{-- CHANGED: Restructured table headers for clarity --}}
                        <thead>
                            <tr>
                                <th style="width: 5%">No.</th>
                                <th>Foto</th>
                                <th>Nama Lengkap</th>
                                <th>Email Akun</th>
                                <th>NIK</th>
                                <th>No. Telepon</th>
                                <th>Unit Usaha</th>
                                <th>Jabatan</th>
                                <th style="width: 15%">Aksi</th> {{-- Merged action columns --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($anggotas as $index => $anggota)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @php
                                        // Photo URL logic is good, retained. Uses UI Avatars as fallback.
                                        $photoUrl = $anggota->photo
                                            ? Storage::url($anggota->photo)
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($anggota->nama_lengkap) . '&background=007bff&color=fff&size=50';
                                    @endphp
                                    <img src="{{ $photoUrl }}" alt="Foto {{ $anggota->nama_lengkap }}" class="img-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>{{ $anggota->nama_lengkap ?? '-' }}</td>
                                <td>{{ optional($anggota->user)->email ?? '-' }}</td>
                                <td>{{ $anggota->nik ?? '-' }}</td>
                                <td>{{ $anggota->no_telepon ?? '-' }}</td>
                                <td>{{ optional($anggota->unitUsaha)->nama_unit ?? '-' }}</td>
                                <td>
                                    {{-- CHANGED: Display role as text, actions are in a separate column --}}
                                    @if ($anggota->user)
                                        <span class="badge badge-info">
                                            {{ Str::title(str_replace('_', ' ', $anggota->user->getRoleNames()->first() ?? 'Anggota')) }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Tidak Ada Akun</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- CHANGED: Combined all actions into a single column with tooltips --}}
                                    <div class="btn-group">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.manajemen-data.anggota.edit', $anggota->anggota_id) }}" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="Edit Data Anggota">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @can('admin_bumdes')
                                            {{-- Role & Delete actions are only for admins --}}
                                            @if ($anggota->user && !$anggota->user->hasRole('admin_bumdes'))
                                                {{-- Change Role Button --}}
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#roleModal-{{ $anggota->user->user_id }}" data-toggle="tooltip" data-placement="top" title="Ubah Jabatan">
                                                    <i class="fas fa-user-shield"></i>
                                                </button>

                                                {{-- Delete Button --}}
                                                <form action="{{ route('admin.manajemen-data.anggota.destroy', $anggota->anggota_id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="Hapus Anggota">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
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

    {{-- ADDED: Modal for Changing Roles --}}
    @can('admin_bumdes')
        @foreach ($anggotas as $anggota)
            @if ($anggota->user && !$anggota->user->hasRole('admin_bumdes'))
                <div class="modal fade" id="roleModal-{{ $anggota->user->user_id }}">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.manajemen-data.anggota.updateRole', $anggota->user->user_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h4 class="modal-title">Ubah Jabatan: {{ $anggota->nama_lengkap }}</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="role">Pilih Jabatan Baru</label>
                                        <select name="role" class="form-control">
                                            @foreach($rolesOptions as $role)
                                                <option value="{{ $role }}" @if($anggota->user->hasRole($role)) selected @endif>
                                                    {{ Str::title(str_replace('_', ' ', $role)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan
@stop

@push('js')
<script>
    $(function () {
        // Initialize Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize DataTable
        $('#manajemen-anggota-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true, // CHANGED: Enabled the search bar
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": { // ADDED: Indonesian language pack for DataTables
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });

        // ADDED: SweetAlert2 confirmation for delete action
        $('.form-delete').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: 'Anda Yakin?',
                text: "Data anggota yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

{{-- You may need to add these plugins if they aren't already available globally --}}
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
