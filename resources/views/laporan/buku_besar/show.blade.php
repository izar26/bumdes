@extends('adminlte::page')

@section('title', 'Laporan Buku Besar')

@section('content_header')
    {{-- Dikosongkan agar tampilan laporan lebih bersih --}}
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center">
            <h3>Laporan Buku Besar</h3>
            <h5>BUMDes Maju Bersama</h5>
            <p><strong>Periode:</strong> {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
        </div>
        <hr>
        
        <div class="mb-3">
            <strong>Kode Akun:</strong> {{ $akun->kode_akun }} <br>
            <strong>Nama Akun:</strong> {{ $akun->nama_akun }}
        </div>

        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center">
                    <th style="width: 15%">Tanggal</th>
                    <th>Keterangan</th>
                    <th style="width: 15%">Debit</th>
                    <th style="width: 15%">Kredit</th>
                    <th style="width: 15%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                {{-- Baris untuk Saldo Awal --}}
                <tr>
                    <td colspan="4"><strong>Saldo Awal</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($saldoAwal, 2, ',', '.') }}</strong></td>
                </tr>

                @php
                    $saldoBerjalan = $saldoAwal; // Inisialisasi saldo berjalan
                @endphp

                {{-- Loop untuk setiap transaksi --}}
                @foreach ($transaksis as $transaksi)
                    @php
                        // Hitung saldo berjalan untuk setiap baris
                        $saldoBerjalan += ($transaksi->debit - $transaksi->kredit);
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($transaksi->jurnal->tanggal_transaksi)->format('d M Y') }}</td>
                        <td>{{ $transaksi->jurnal->deskripsi }}</td>
                        <td class="text-right">{{ $transaksi->debit > 0 ? 'Rp ' . number_format($transaksi->debit, 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $transaksi->kredit > 0 ? 'Rp ' . number_format($transaksi->kredit, 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($saldoBerjalan, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Baris untuk Saldo Akhir --}}
                <tr class="table-active">
                    <td colspan="4"><strong>Saldo Akhir</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($saldoBerjalan, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="mt-4 text-right">
            <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Cetak</button>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- CSS khusus untuk halaman cetak --}}
    <style>
        @media print {
            .main-sidebar, .main-header, .btn, .content-header {
                display: none !important;
            }
            .content-wrapper, .content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@stop