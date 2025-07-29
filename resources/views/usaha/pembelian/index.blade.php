@extends('adminlte::page')
@section('title', 'Daftar Pembelian')
@section('content_header')
    <h1>Daftar Transaksi Pembelian</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Pembelian</h3>
        <div class="card-tools">
            <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Buat Transaksi Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <table id="table-pembelian" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Faktur</th>
                    <th>Pemasok</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pembelians as $pembelian)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d M Y') }}</td>
                        <td>{{ $pembelian->no_faktur ?? '-' }}</td>
                        <td>{{ $pembelian->pemasok->nama_pemasok ?? 'N/A' }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($pembelian->nominal_pembelian, 0, ',', '.') }}</td>
                        <td>
                            @if($pembelian->status_pembelian == 'Lunas')
                                <span class="badge badge-success">Lunas</span>
                            @else
                                <span class="badge badge-warning">Belum Lunas</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pembelian.show', $pembelian->pembelian_id) }}" class="btn btn-info btn-xs">Detail</a>
                            <form action="{{ route('pembelian.destroy', $pembelian->pembelian_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus transaksi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada transaksi pembelian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
@section('plugins.Datatables', true)
@section('js')
<script>
    $('#table-pembelian').DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "order": [[ 0, "desc" ]]
    });
</script>
@stop