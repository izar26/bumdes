@extends('adminlte::page')

@section('title', 'Daftar Pinjaman')

@section('content_header')
    <h1><i class="fas fa-money-check-alt"></i> Daftar Pinjaman</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Histori Pengajuan Pinjaman</h3>
            <div class="card-tools">
                <a href="{{ route('simpanan.pinjaman.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Pengajuan Baru</a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered table-striped dataTable">
                <thead>
                    <tr>
                        <th>No Pinjaman</th>
                        <th>Anggota</th>
                        <th>Tgl Pengajuan</th>
                        <th>Jumlah Pinjam</th>
                        <th>Tenor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pinjamans as $pinjaman)
                        <tr>
                            <td>{{ $pinjaman->no_pinjaman }}</td>
                            <td>**{{ $pinjaman->anggota->nama_lengkap ?? 'N/A' }}**</td>
                            <td>{{ $pinjaman->tanggal_pengajuan->format('d/m/Y') }}</td>
                            <td>Rp {{ number_format($pinjaman->jumlah_pinjaman) }}</td>
                            <td>{{ $pinjaman->tenor }} bulan</td>
                            <td>
                                @php
                                    $badge = ['pending' => 'warning', 'approved' => 'info', 'rejected' => 'danger', 'lunas' => 'success'];
                                @endphp
                                <span class="badge badge-{{ $badge[$pinjaman->status] ?? 'secondary' }}">{{ strtoupper($pinjaman->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('simpanan.pinjaman.show', $pinjaman->pinjaman_id) }}" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.dataTable').DataTable();
        });
    </script>
@endsection
