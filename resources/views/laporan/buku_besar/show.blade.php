@extends('adminlte::page')

@section('title', 'Laporan Buku Besar')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Safeguards untuk memastikan variabel ada
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $tanggalCetak = $tanggalCetak ?? now();
    $lokasi = optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes';
    
    // Inisialisasi saldo berjalan dengan saldo awal
    $saldoBerjalan = $saldoAwal;

    // Menentukan apakah akun memiliki saldo normal Debit.
    // Ini untuk menghindari penulisan array berulang di dalam loop.
    // Pastikan tipe akun 'assets' (jika ada) juga dimasukkan untuk konsistensi.
    $akunNormalDebit = ['Aset', 'HPP', 'Beban', 'assets']; 
@endphp
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT --}}
        <div class="text-center" style="border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px;">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/'. $bumdes->logo) }}" alt="Logo" style="width: 80px; position: absolute; left: 40px; top: 30px;">
            @endif
            <h4 class="font-weight-bold mb-1">{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h4>
            <p class="mb-1">{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</p>
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Buku Besar</h5>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- INFORMASI AKUN --}}
        <div class="mb-3">
            <strong>Kode Akun:</strong> {{ $akun->kode_akun }} <br>
            <strong>Nama Akun:</strong> {{ $akun->nama_akun }}
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center table-active">
                    <th style="width: 15%">Tanggal</th>
                    <th>Keterangan</th>
                    <th style="width: 15%">Debit</th>
                    <th style="width: 15%">Kredit</th>
                    <th style="width: 15%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4"><strong>Saldo Awal</strong></td>
                    <td class="text-right"><strong>{{ 'Rp '. number_format($saldoAwal, 0, ',', '.') }}</strong></td>
                </tr>
                
                @forelse ($transaksis as $transaksi)
                    @php
                    // --- INI BAGIAN YANG DIPERBAIKI ---
                    // Logika perhitungan saldo yang benar sesuai dengan prinsip akuntansi.

                    if (in_array($akun->tipe_akun, $akunNormalDebit)) {
                        // Untuk Akun Aset/Beban: Saldo Awal + Debit - Kredit
                        $saldoBerjalan += ($transaksi->debit - $transaksi->kredit);
                    } else {
                        // Untuk Akun Kewajiban/Ekuitas/Pendapatan: Saldo Awal + Kredit - Debit
                        $saldoBerjalan += ($transaksi->kredit - $transaksi->debit);
                    }
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($transaksi->jurnal->tanggal_transaksi)->format('d M Y') }}</td>
                        <td>{{ $transaksi->jurnal->deskripsi }}</td>
                        <td class="text-right">{{ $transaksi->debit > 0 ? 'Rp '. number_format($transaksi->debit, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $transaksi->kredit > 0 ? 'Rp '. number_format($transaksi->kredit, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ 'Rp '. number_format($saldoBerjalan, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
                
                <tr class="table-active">
                    <td colspan="4"><strong>Saldo Akhir</strong></td>
                    <td class="text-right"><strong>{{ 'Rp '. number_format($saldoBerjalan, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN --}}
        <table style="margin-top: 60px; width: 100%;" class="table-borderless">
            <tr>
                <td style="text-align: center; width: 50%;"></td>
                <td style="text-align: center; width: 50%;">
                    {{ $lokasi }}, {{ $tanggalCetak->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">Mengetahui,</td>
                <td style="text-align: center;">Menyetujui,</td>
            </tr>
            <tr>
                <td style="text-align: center;"><strong>{{ $penandaTangan2['jabatan'] ?? 'Bendahara' }}</strong></td>
                <td style="text-align: center;"><strong>{{ $penandaTangan1['jabatan'] ?? 'Direktur' }}</strong></td>
            </tr>
            <tr style="height: 80px;"><td></td><td></td></tr>
            <tr>
                <td style="text-align: center;">( {{ $penandaTangan2['nama'] ?? '____________________' }} )</td>
                <td style="text-align: center;">( {{ $penandaTangan1['nama'] ?? '____________________' }} )</td>
            </tr>
        </table>

        <div class="mt-4 text-right no-print">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    @media print {
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer, .card-header, form {
            display: none !important;
        }
        .content-wrapper, .content, .card, .card-body {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@stop

