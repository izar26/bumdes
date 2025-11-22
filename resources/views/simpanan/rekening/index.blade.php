@extends('adminlte::page')

@section('title', 'Daftar Rekening Simpanan')

@section('content_header')
    <h1><i class="fas fa-users"></i> Daftar Rekening Simpanan Anggota</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ringkasan Saldo Anggota</h3>
            <div class="card-tools">
                {{-- TOMBOL TAMBAH: Mengarah ke route rekening.create --}}
                <a href="{{ route('simpanan.rekening.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Buka Rekening Baru
                </a>
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
                        <th style="width: 5%">No</th>
                        <th>No Rekening</th>
                        <th>Nama Anggota</th>
                        <th>Jenis Simpanan</th>
                        <th>Saldo Saat Ini</th>
                        <th style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekenings as $rekening)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $rekening->no_rekening }}</strong></td>
                            <td>{{ $rekening->anggota->nama_lengkap ?? 'N/A' }}</td>
                            <td>{{ $rekening->jenisSimpanan->nama_jenis ?? 'N/A' }}</td>
                            <td><strong>Rp {{ number_format($rekening->saldo) }}</strong></td>
                            <td>
                                {{-- Rute ini diarahkan ke detail anggota --}}
                                <a href="{{ route('simpanan.rekening.show', $rekening->anggota_id) }}" class="btn btn-xs btn-info" title="Lihat Detail">
                                    <i class="fas fa-search"></i> Detail
                                </a>
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
            // Pastikan DataTables sudah terinstall di AdminLTE Anda
            $('.dataTable').DataTable({
                "responsive": true,
                "autoWidth": false,
            });
        });
    </script>
@endsection
