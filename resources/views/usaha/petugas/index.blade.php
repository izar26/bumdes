@extends('adminlte::page')

@section('title', 'Manajemen Petugas')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Petugas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">Daftar Petugas Penagihan</span>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPetugasModal">
                <i class="fa fa-plus"></i> Tambah Petugas
            </button>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif
            @if(session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle"></i> Gagal memproses data:
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-success">
                        <tr>
                            <th>#</th>
                            <th>Nama Petugas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semua_petugas as $petugas)
                        <tr>
                            <td>{{ $loop->iteration + $semua_petugas->firstItem() - 1 }}</td>
                            <td id="nama-petugas-{{$petugas->id}}">{{ $petugas->nama_petugas }}</td>
                            <td>
                                @if($petugas->status == 'Aktif')
                                    <span class="badge badge-success">{{ $petugas->status }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $petugas->status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Tombol Edit sekarang memicu modal --}}
                                <button type="button" class="btn btn-sm btn-warning edit-btn"
                                        data-toggle="modal"
                                        data-target="#editPetugasModal"
                                        data-id="{{ $petugas->id }}"
                                        data-nama="{{ $petugas->nama_petugas }}"
                                        data-status="{{ $petugas->status }}"
                                        title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                    data-toggle="modal" data-target="#confirmModal"
                                    data-form-id="delete-petugas-{{ $petugas->id }}"
                                    data-title="Konfirmasi Hapus"
                                    data-body="Apakah Anda yakin?"
                                    data-button-text="Hapus"
                                    data-button-class="btn-danger" title="Hapus"><i class="fa fa-trash"></i></button>
                                <form id="delete-petugas-{{ $petugas->id }}" action="{{ route('usaha.petugas.destroy', $petugas) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data petugas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($semua_petugas->hasPages())
        <div class="card-footer clearfix">
            {{ $semua_petugas->links() }}
        </div>
        @endif
    </div>

    <div class="modal fade" id="addPetugasModal" tabindex="-1" role="dialog" aria-labelledby="addPetugasModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPetugasModalLabel">Tambah Petugas Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('usaha.petugas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_petugas_add">Nama Petugas</label>
                            <input type="text" name="nama_petugas" id="nama_petugas_add" class="form-control" value="{{ old('nama_petugas') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPetugasModal" tabindex="-1" role="dialog" aria-labelledby="editPetugasModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPetugasModalLabel">Edit Data Petugas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                {{-- Action form akan diisi oleh JavaScript --}}
                <form id="editPetugasForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_petugas_edit">Nama Petugas</label>
                            <input type="text" name="nama_petugas" id="nama_petugas_edit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="status_edit">Status Petugas</label>
                            <select name="status" id="status_edit" class="form-control" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Membuka kembali modal TAMBAH jika ada error validasi
    @if($errors->any() && old('nama_petugas') && !old('status'))
        $('#addPetugasModal').modal('show');
    @endif

    // Membuka kembali modal EDIT jika ada error validasi
    @if($errors->any() && old('status'))
        $('#editPetugasModal').modal('show');
    @endif

    // Script untuk mengisi data ke modal EDIT
    $('.edit-btn').on('click', function() {
        // Ambil data dari atribut data-* di tombol
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const status = $(this).data('status'); // Ambil status baru

        // Buat URL action untuk form
        const url = '{{ url("usaha/petugas") }}/' + id;

        // Set action form dan isi value input di dalam modal edit
        $('#editPetugasForm').attr('action', url);
        $('#nama_petugas_edit').val(nama);
        $('#status_edit').val(status); // Set nilai dropdown status
    });
});
</script>
@stop
