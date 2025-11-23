@extends('adminlte::page')

@section('title', 'Daftar Pelanggan')

{{-- TAMBAHAN: Mengaktifkan plugin DataTables dari AdminLTE --}}
@section('plugins.DataTables', true)

@section('content_header')
    <h1 class="m-0 text-dark">Daftar Pelanggan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">
                Data Semua Pelanggan
            </span>
            <a href="{{ route('usaha.pelanggan.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Pelanggan Baru
            </a>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif
            @if(session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif

            <div class="table-responsive">
                {{-- DIUBAH: Menambahkan id="pelanggan-table" agar bisa ditarget oleh JavaScript --}}
                <table class="table table-hover table-striped" id="pelanggan-table">
                    <thead class="thead-primary">
                        <tr>
                            <th>No.</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semua_pelanggan as $pelanggan)
                        <tr>
                            {{-- DIUBAH: Cukup gunakan $loop->iteration karena DataTables mengelola semua data --}}
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pelanggan->nama }}</td>
                            <td>{{ $pelanggan->alamat }}</td>
                            <td>{{$pelanggan->kontak}}</td>
                            <td class="text-center">
                                <span class="badge {{ $pelanggan->status_pelanggan == 'Aktif' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $pelanggan->status_pelanggan }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('usaha.pelanggan.edit', $pelanggan) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-toggle="modal" data-target="#confirmModal"
                                        data-form-id="delete-pelanggan-{{ $pelanggan->id }}"
                                        data-title="Konfirmasi Hapus"
                                        data-body="Apakah Anda yakin? Pelanggan yang memiliki tagihan tidak dapat dihapus."
                                        data-button-text="Hapus"
                                        data-button-class="btn-danger" title="Hapus"><i class="fa fa-trash"></i></button>
                                    <form id="delete-pelanggan-{{ $pelanggan->id }}" action="{{ route('usaha.pelanggan.destroy', $pelanggan) }}" method="POST" style="display: none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pelanggan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#pelanggan-table').DataTable({
            "responsive": true, // Agar tabel responsif di perangkat mobile
            "lengthChange": true, // Menampilkan opsi untuk mengubah jumlah data per halaman
            "autoWidth": false, // Menonaktifkan penyesuaian lebar otomatis
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Menggunakan bahasa Indonesia
            },
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Menonaktifkan sorting untuk kolom 'Aksi' (indeks ke-5)
            ]
        });
    });
</script>
@stop
