@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Helper function untuk format Rupiah
    function format_rp($value) {
        if ($value < 0) {
            return '(Rp ' . number_format(abs($value), 0, ',', '.') . ')';
        }
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
            <h3>Laporan Laba Rugi</h3>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm" style="width: 100%; margin-top: 20px;">
            <tbody>
                {{-- PENDAPATAN --}}
                <tr class="table-active"><td colspan="2"><strong>PENDAPATAN USAHA</strong></td></tr>
                <tr><td><strong>Pendapatan Jasa</strong></td><td class="text-right" style="width: 25%">{{ format_rp($pendapatan_jasa) }}</td></tr>
                <tr><td><strong>Penjualan barang dagangan bersih</strong></td><td class="text-right">{{ format_rp($penjualan_dagang_bersih) }}</td></tr>
                <tr><td><strong>Penjualan barang jadi bersih</strong></td><td class="text-right">{{ format_rp($penjualan_jadi_bersih) }}</td></tr>
                <tr class="bg-light font-weight-bold"><td class="pl-4">Total Pendapatan</td><td class="text-right">{{ format_rp($total_pendapatan) }}</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- HPP --}}
                <tr class="table-active"><td colspan="2"><strong>HARGA POKOK PENJUALAN</strong></td></tr>
                <tr class="bg-light font-weight-bold"><td class="pl-4">Total Harga Pokok Penjualan</td><td class="text-right">({{ format_rp(abs($total_hpp)) }})</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- LABA KOTOR --}}
                <tr class="table-secondary font-weight-bold"><td>LABA (RUGI) KOTOR</td><td class="text-right">{{ format_rp($laba_kotor) }}</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- BEBAN-BEBAN USAHA --}}
                <tr class="table-active"><td colspan="2"><strong>BEBAN-BEBAN USAHA</strong></td></tr>
                <tr><td class="pl-4"><strong>Beban Administrasi dan Umum</strong></td><td></td></tr>
                <tr><td class="pl-5">Beban Pegawai Bagian Administrasi Umum</td><td class="text-right">{{ format_rp($beban_adm_pegawai) }}</td></tr>
                <tr><td class="pl-5">Beban Perlengkapan</td><td class="text-right">{{ format_rp($beban_adm_perlengkapan) }}</td></tr>
                <tr><td class="pl-5">Beban Pemeliharaan dan Perbaikan</td><td class="text-right">{{ format_rp($beban_adm_pemeliharaan) }}</td></tr>
                <tr><td class="pl-5">Beban Utilitas</td><td class="text-right">{{ format_rp($beban_adm_utilitas) }}</td></tr>
                <tr><td class="pl-5">Beban Sewa dan Asuransi</td><td class="text-right">{{ format_rp($beban_adm_sewa) }}</td></tr>
                <tr><td class="pl-5">Beban Kebersihan dan Keamanan</td><td class="text-right">{{ format_rp($beban_adm_kebersihan) }}</td></tr>
                <tr><td class="pl-5">Beban Penyisihan dan Penyusutan/Amortisasi</td><td class="text-right">{{ format_rp($beban_adm_penyusutan) }}</td></tr>
                <tr><td class="pl-5">Beban Administrasi dan Umum Lainnya</td><td class="text-right">{{ format_rp($beban_adm_lain) }}</td></tr>
                <tr class="bg-light font-weight-bold"><td class="pl-4">Total Beban Administrasi Umum</td><td class="text-right">({{ format_rp(abs($total_beban_adm)) }})</td></tr>
                
                <tr><td class="pl-4"><strong>Beban Operasional</strong></td><td></td></tr>
                <tr><td class="pl-5">Beban Pegawai Bagian Operasional</td><td class="text-right">{{ format_rp($beban_ops_pegawai) }}</td></tr>
                <tr><td class="pl-5">Beban Pemeliharaan dan Perbaikan</td><td class="text-right">{{ format_rp($beban_ops_pemeliharaan) }}</td></tr>
                <tr><td class="pl-5">Beban Operasional Lainnya</td><td class="text-right">{{ format_rp($beban_ops_lain) }}</td></tr>
                <tr class="bg-light font-weight-bold"><td class="pl-4">Total Beban Operasional</td><td class="text-right">({{ format_rp(abs($total_beban_ops)) }})</td></tr>

                <tr><td class="pl-4"><strong>Beban Pemasaran</strong></td><td></td></tr>
                <tr><td class="pl-5">Beban Pegawai Bagian Pemasaran</td><td class="text-right">{{ format_rp($beban_pemasaran_pegawai) }}</td></tr>
                <tr><td class="pl-5">Beban Iklan dan Promosi</td><td class="text-right">{{ format_rp($beban_pemasaran_iklan) }}</td></tr>
                <tr><td class="pl-5">Beban Pemasaran Lainnya</td><td class="text-right">{{ format_rp($beban_pemasaran_lain) }}</td></tr>
                <tr class="bg-light font-weight-bold"><td class="pl-4">Total Beban Pemasaran</td><td class="text-right">({{ format_rp(abs($total_beban_pemasaran)) }})</td></tr>
                
                <tr class="bg-light font-weight-bold" style="border-top: 1px solid #dee2e6;"><td>Total Beban-Beban Usaha</td><td class="text-right">({{ format_rp(abs($total_beban_usaha)) }})</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- LABA OPERASI --}}
                <tr class="table-secondary font-weight-bold"><td>LABA (RUGI) OPERASI</td><td class="text-right">{{ format_rp($laba_operasi) }}</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- PENDAPATAN & BEBAN LAIN-LAIN --}}
                <tr class="table-active"><td colspan="2"><strong>PENDAPATAN DAN BEBAN LAIN-LAIN</strong></td></tr>
                <tr><td class="pl-4">Total Pendapatan Lain-lain</td><td class="text-right">{{ format_rp($pendapatan_lain) }}</td></tr>
                <tr><td class="pl-4">Total Beban Lain-lain</td><td class="text-right">({{ format_rp(abs($beban_lain)) }})</td></tr>
                <tr><td class="pl-4">Beban Pajak</td><td class="text-right">({{ format_rp(abs($beban_pajak)) }})</td></tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- LABA BERSIH SEBELUM BAGI HASIL --}}
                <tr class="table-secondary font-weight-bold"><td>LABA (RUGI) BERSIH SEBELUM BAGI HASIL</td><td class="text-right">{{ format_rp($laba_sebelum_bagi_hasil) }}</td></tr>
                <tr><td class="pl-4">BAGI HASIL PENYERTAAN MODAL DESA</td><td class="text-right">({{ format_rp(abs($bagi_hasil_desa)) }})</td></tr>
                <tr><td class="pl-4">BAGI HASIL PENYERTAAN MODAL MASYARAKAT</td><td class="text-right">({{ format_rp(abs($bagi_hasil_masyarakat)) }})</td></tr>
                
                {{-- LABA BERSIH FINAL --}}
                <tr class="table-success font-weight-bold" style="border-top: 3px double #000;">
                    <td>LABA (RUGI) BERSIH SETELAH BAGI HASIL</td>
                    <td class="text-right">{{ format_rp($laba_bersih_final) }}</td>
                </tr>

            </tbody>
        </table>

        {{-- TANDA TANGAN --}}
        <table style="margin-top: 60px; width: 100%;" class="table-borderless">
            <tr>
                <td style="text-align: center; width: 50%;"></td>
                <td style="text-align: center; width: 50%;">
                    {{ optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes' }}, {{ $tanggalCetak->translatedFormat('d F Y') }}
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
    /* CSS KOP SURAT */
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
        /* Memastikan warna latar belakang baris di laporan laba rugi ikut tercetak */
        .table-active, .bg-light, .table-secondary, .table-success {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@stop