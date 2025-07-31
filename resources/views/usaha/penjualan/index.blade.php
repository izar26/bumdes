@extends('adminlte::page')

@section('title', 'Daftar Penjualan')

@section('content_header')
    <h1>Daftar Transaksi Penjualan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Penjualan</h3>
        <div class="card-tools">
            <a href="{{ route('usaha.penjualan.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Buat Transaksi Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error')) {{-- Tambahkan penanganan error juga --}}
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                {{ session('error') }}
            </div>
        @endif
<table id="table-penjualan" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No. Invoice</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Status</th>
            <th style="width: 120px;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($penjualans as $penjualan)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d M Y') }}</td>
                <td>{{ $penjualan->no_invoice }}</td>
                <td>{{ $penjualan->nama_pelanggan ?? 'Umum' }}</td>
                {{-- Menggunakan helper function formatRupiah yang sudah dibuat --}}
                <td class="text-right">{{ formatRupiah($penjualan->total_penjualan) }}</td>
                <td>
                    @if($penjualan->status_penjualan == 'Lunas')
                        <span class="badge badge-success">Lunas</span>
                    @else
                        <span class="badge badge-warning">Belum Lunas</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('usaha.penjualan.show', $penjualan->penjualan_id) }}" class="btn btn-info btn-xs">Detail</a>

                    {{-- Delete Form (menggunakan modal dinamis) --}}
                    <form id="delete-form-{{ $penjualan->penjualan_id }}"
                          action="{{ route('usaha.penjualan.destroy', $penjualan->penjualan_id) }}" {{-- Pastikan route ini benar --}}
                          method="POST"
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-sm btn-danger mb-1"
                                data-toggle="modal"
                                data-target="#confirmModal"
                                data-form-id="delete-form-{{ $penjualan->penjualan_id }}"
                                data-title="Konfirmasi Penghapusan Transaksi" {{-- Judul modal --}}
                                data-body="Apakah Anda yakin ingin membatalkan dan menghapus transaksi dengan invoice '{{ $penjualan->no_invoice }}' ini secara permanen? Aksi ini tidak bisa dikembalikan."
                                data-button-text="Hapus Permanen"
                                data-button-class="btn-danger">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data penjualan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
    </div>
</div>

    @include('components.confirm-modal', [
        'modalId' => 'confirmModal',
        'title' => 'Konfirmasi Aksi',
        'body' => 'Apakah Anda yakin?',
        'confirmButtonText' => 'Lanjutkan',
        'confirmButtonClass' => 'btn-primary',
        'actionFormId' => ''
    ])

@stop

@section('plugins.Datatables', true)
@section('js')
<script>
    $(document).ready(function() {
        $('#table-penjualan').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "order": [[ 0, "desc" ]]
        });
    });
</script>
@stop
