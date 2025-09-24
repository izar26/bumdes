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
    $akunNormalDebit = ['Aset', 'HPP', 'Beban', 'assets']; 
@endphp
<div class="card">
    <div class="card-body">
        
        {{-- KOP SURAT GAYA JURNAL UMUM --}}
        <div class="kop">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/'. $bumdes->logo) }}" alt="Logo">
            @endif
            <div class="kop-text">
                <h1>{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h1>
                <h2>{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</h2>
                <p>Email: {{ optional($bumdes)->email ?? '-' }} | Telp: {{ optional($bumdes)->telepon ?? '-' }}</p>
            </div>
        </div>
        <div class="garis-pembatas"></div>

        {{-- JUDUL LAPORAN --}}
        <div class="judul">
            <h3>Laporan Buku Besar</h3>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- INFORMASI AKUN --}}
        <div class="mb-3" style="margin-top: 20px;">
            <strong>Kode Akun:</strong> {{ $akun->kode_akun }} <br>
            <strong>Nama Akun:</strong> {{ $akun->nama_akun }}
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm">
            <thead>
                {{-- HEADER TABLE DENGAN LATAR BELAKANG --}}
                <tr class="text-center">
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
                
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Saldo Akhir</strong></td>
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
    /* CSS KOP SURAT & TABEL */
    .kop {
        display: flex;
        align-items: center;
        padding: 10px;
    }
    .kop img {
        height: 75px;
        width: auto;
        margin-right: 20px;
    }
    .kop-text {
        flex: 1;
        text-align: center;
    }
    .kop-text h1 {
        margin: 0;
        font-size: 22px;
        font-weight: bold;
        text-transform: uppercase;
        color: #006666;
    }
    .kop-text h2 {
        margin: 2px 0 0;
        font-size: 14px;
        font-weight: normal;
        color: #333;
    }
    .kop-text p {
        margin: 2px 0;
        font-size: 12px;
    }

    /* Garis Ganda */
        .garis-pembatas {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

    .judul {
        text-align: center;
        margin-bottom: 10px;
    }
    .judul h3 {
        margin: 5px 0;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .judul p {
        margin: 2px 0;
        font-size: 14px;
    }

    th {
        background: #008080 !important;
        color: white !important;
        text-align: center;
    }

    .total-row {
        background: #e6f2f2;
        font-weight: bold;
    }
    
    /* CSS UNTUK PRINT */
    @media print {
        body {
            margin: 0;
        }
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer, .card-header, form {
            display: none !important;
        }
        .content-wrapper, .content, .card, .card-body {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .kop, .garis-pembatas, th, .total-row {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@stop