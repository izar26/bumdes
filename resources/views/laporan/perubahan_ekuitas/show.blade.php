@extends('adminlte::page')

@section('title', 'Laporan Perubahan Ekuitas')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Safeguards dan Helper
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $startDate = $startDate ?? now();
    $endDate = $endDate ?? now();
    $tanggalCetak = $tanggalCetak ?? now();
    $lokasi = optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes';
    $penandaTangan1 = $penandaTangan1 ?? ['jabatan' => 'Direktur', 'nama' => ''];
    $penandaTangan2 = $penandaTangan2 ?? ['jabatan' => 'Bendahara', 'nama' => ''];

    function format_rp($value) {
        if ($value == 0) return 'Rp 0';
        if ($value < 0) return '(Rp ' . number_format(abs($value), 0, ',', '.') . ')';
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
@endphp
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT BARU --}}
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
            <h3>Laporan Perubahan Ekuitas</h3>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm" style="margin-top: 20px;">
            <tbody>
                {{-- PENYERTAAN MODAL --}}
                <tr class="table-active">
                    <td colspan="2"><strong>PENYERTAAN MODAL</strong></td>
                </tr>
                <tr>
                    <td class="pl-4">Penyertaan Modal Desa Awal</td>
                    <td class="text-right" style="width: 25%">{{ format_rp($modal_desa_awal ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-4">Penyertaan Modal Masyarakat Awal</td>
                    <td class="text-right">{{ format_rp($modal_masyarakat_awal ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-4">Penambahan Investasi periode berjalan:</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="pl-5">Penyertaan Modal Desa</td>
                    <td class="text-right">{{ format_rp($penambahan_modal_desa ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-5">Penyertaan Modal Masyarakat</td>
                    <td class="text-right">{{ format_rp($penambahan_modal_masyarakat ?? 0) }}</td>
                </tr>
                <tr class="font-weight-bold" style="border-top: 1px solid #dee2e6;">
                    <td class="pl-4">Penyertaan Modal Akhir</td>
                    <td class="text-right">{{ format_rp($penyertaan_modal_akhir ?? 0) }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- SALDO LABA --}}
                <tr class="table-active">
                    <td colspan="2"><strong>SALDO LABA</strong></td>
                </tr>
                 <tr>
                    <td class="pl-4">Saldo Laba Awal</td>
                    <td class="text-right">{{ format_rp($saldo_laba_awal ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-4">Laba (Rugi) periode berjalan</td>
                    <td class="text-right">{{ format_rp($laba_rugi_periode_berjalan ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-4">Bagi Hasil Penyertaan:</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="pl-5">Bagi Hasil Penyertaan Modal Desa</td>
                    <td class="text-right">{{ format_rp($bagi_hasil_desa ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="pl-5">Bagi Hasil Penyertaan Modal Masyarakat</td>
                    <td class="text-right">{{ format_rp($bagi_hasil_masyarakat ?? 0) }}</td>
                </tr>
                <tr class="font-weight-bold" style="border-top: 1px solid #dee2e6;">
                    <td class="pl-4">Saldo Laba Akhir</td>
                    <td class="text-right">{{ format_rp($saldo_laba_akhir ?? 0) }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- MODAL DONASI --}}
                 <tr class="table-active">
                    <td colspan="2"><strong>MODAL DONASI/SUMBANGAN</strong></td>
                </tr>
                 <tr>
                    <td class="pl-4">Modal Donasi/Sumbangan</td>
                    <td class="text-right">{{ format_rp($modal_donasi_akhir ?? 0) }}</td>
                </tr>
                 <tr><td colspan="2">&nbsp;</td></tr>

                {{-- EKUITAS AKHIR --}}
                <tr class="table-success font-weight-bold" style="border-top: 3px double #000;">
                    <td><strong>EKUITAS AKHIR</strong></td>
                    <td class="text-right"><strong>{{ format_rp($ekuitas_akhir ?? 0) }}</strong></td>
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
        .kop, .garis-pembatas {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Memastikan warna latar belakang baris di laporan ikut tercetak */
        .table-active, .table-success {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@stop