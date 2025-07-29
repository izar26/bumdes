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
            <a href="{{ route('penjualan.create') }}" class="btn btn-primary btn-sm">
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
        {{-- ... kode lainnya ... --}}
<table id="table-penjualan" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No. Invoice</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Status</th>
            <th style="width: 120px;">Aksi</th> {{-- Lebarkan sedikit kolom aksi --}}
        </tr>
    </thead>
    <tbody>
        @forelse ($penjualans as $penjualan)
            <tr>
                {{-- ... kolom lainnya ... --}}
                <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d M Y') }}</td>
                <td>{{ $penjualan->no_invoice }}</td>
                <td>{{ $penjualan->nama_pelanggan ?? 'Umum' }}</td>
                <td class="text-right">{{ 'Rp ' . number_format($penjualan->total_penjualan, 0, ',', '.') }}</td>
                <td>
                    @if($penjualan->status_penjualan == 'Lunas')
                        <span class="badge badge-success">Lunas</span>
                    @else
                        <span class="badge badge-warning">Belum Lunas</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('penjualan.show', $penjualan->penjualan_id) }}" class="btn btn-info btn-xs">Detail</a>
                    
                    {{-- ================== TAMBAHKAN FORM INI ================== --}}
                    <form action="{{ route('penjualan.destroy', $penjualan->penjualan_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin membatalkan dan menghapus transaksi ini? Aksi ini tidak bisa dikembalikan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                    </form>
                    {{-- ======================================================== --}}
                </td>
            </tr>
        @empty
            {{-- ... --}}
        @endforelse
    </tbody>
</table>
{{-- ... kode lainnya ... --}}
    </div>
</div>
@stop

{{-- Aktifkan plugin datatables --}}
@section('plugins.Datatables', true)
@section('js')
<script>
    $(document).ready(function() {
        $('#table-penjualan').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "order": [[ 0, "desc" ]] // Urutkan berdasarkan tanggal terbaru
        });
    });
</script>
@stop