@extends('adminlte::page')

@section('title', 'Laporan Buku Besar')

@section('content_header')
    {{-- Dikosongkan supaya header adminlte tidak tampil --}}
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- HEADER LAPORAN --}}
        <div class="text-center mb-4" style="border-bottom: 3px double #000; padding-bottom: 10px;">
            <img src="{{ public_path('images/logo-bumdes.png') }}" alt="Logo" style="width: 80px; position: absolute; left: 40px; top: 20px;">
            <h3 style="margin: 0;"><strong>{{ $bumdes->nama_bumdes ?? 'BUMDes Maju Bersama' }}</strong></h3>
            <p style="margin: 0;">{{ $bumdes->alamat ?? 'Alamat BUMDes' }}</p>
            <h4 style="margin-top: 10px;"><strong>Laporan Buku Besar</strong></h4>
            <p><strong>Periode:</strong> {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
        </div>

        {{-- INFORMASI AKUN --}}
        <div class="mb-3">
            <strong>Kode Akun:</strong> {{ $akun->kode_akun }} <br>
            <strong>Nama Akun:</strong> {{ $akun->nama_akun }}
        </div>

        {{-- TABEL DATA --}}
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
                {{-- Saldo Awal --}}
                <tr>
                    <td colspan="4"><strong>Saldo Awal</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($saldoAwal, 2, ',', '.') }}</strong></td>
                </tr>

                @php $saldoBerjalan = $saldoAwal; @endphp

                @foreach ($transaksis as $transaksi)
                    @php
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

                {{-- Saldo Akhir --}}
                <tr class="table-active">
                    <td colspan="4"><strong>Saldo Akhir</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($saldoBerjalan, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN --}}
        <table style="margin-top: 60px; width: 100%;">
            <tr>
                <td style="text-align: center; width: 50%;">
                    {{ $bumdes->alamat ?? '' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
                <td style="text-align: center; width: 50%;"></td>
            </tr>
            <tr>
                <td style="text-align: center;">Direktur</td>
                <td style="text-align: center;">Bendahara</td>
            </tr>
            <tr>
                <td style="height: 80px;"></td>
                <td style="height: 80px;"></td>
            </tr>
            <tr>
                <td style="text-align: center;">(___________________)</td>
                <td style="text-align: center;">(___________________)</td>
            </tr>
        </table>

        {{-- TOMBOL CETAK (non-print) --}}
        <div class="mt-4 text-right no-print">
            <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Cetak</button>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .main-sidebar, .main-header, .btn, .content-header, .no-print {
            display: none !important;
        }
        .content-wrapper, .content {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
@stop
