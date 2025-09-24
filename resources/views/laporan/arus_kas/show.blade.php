@extends('adminlte::page')

@section('title', 'Laporan Arus Kas')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Safeguards untuk memastikan variabel default ada jika terjadi error
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $startDate = $startDate ?? now();
    $endDate = $endDate ?? now();
    $tanggalCetak = $tanggalCetak ?? now();
    $lokasi = optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes';
    $penandaTangan1 = $penandaTangan1 ?? ['jabatan' => 'Direktur', 'nama' => ''];
    $penandaTangan2 = $penandaTangan2 ?? ['jabatan' => 'Bendahara', 'nama' => ''];
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
            <h3>Laporan Arus Kas</h3>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm" style="margin-top: 20px;">
            <tbody>
                {{-- ARUS KAS DARI AKTIVITAS OPERASI --}}
                <tr class="table-active">
                    <td colspan="2"><strong>ARUS KAS DARI AKTIVITAS OPERASI</strong></td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Masuk</strong></td>
                    <td></td>
                </tr>
                <tr><td style="padding-left: 40px;">Penerimaan kas dari penjualan jasa</td><td class="text-right" style="width: 25%">{{ number_format($kas['operasi_masuk']['penjualan_jasa'] ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Penerimaan kas dari penjualan barang dagangan</td><td class="text-right">{{ number_format($kas['operasi_masuk']['penjualan_barang_dagangan'] ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Penerimaan kas dari penjualan barang jadi</td><td class="text-right">{{ number_format($kas['operasi_masuk']['penjualan_barang_jadi'] ?? 0, 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Penerimaan kas dari bunga bank</td><td class="text-right">{{ number_format($kas['operasi_masuk']['bunga_bank'] ?? 0, 0, ',', '.') }}</td></tr>
                <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas masuk dari aktivitas operasi</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['operasi_masuk'] ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
                
                <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Keluar</strong></td>
                    <td></td>
                </tr>
                <tr><td style="padding-left: 40px;">Pengeluaran kas untuk pembayaran gaji/upah</td><td class="text-right">{{ number_format(abs($kas['operasi_keluar']['pembayaran_gaji'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Pengeluaran kas untuk pembayaran biaya produksi</td><td class="text-right">{{ number_format(abs($kas['operasi_keluar']['pembayaran_biaya_produksi'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Pengeluaran kas untuk pembayaran beban-beban lain</td><td class="text-right">{{ number_format(abs($kas['operasi_keluar']['pembayaran_beban_lain'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas keluar dari aktivitas operasi</strong></td>
                    <td class="text-right"><strong>({{ number_format(abs($totals['operasi_keluar'] ?? 0), 0, ',', '.') }})</strong></td>
                </tr>
                 <tr class="font-weight-bold" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td>Arus kas bersih dari aktivitas operasi</td>
                    <td class="text-right">{{ number_format($arus_kas_bersih['operasi'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS INVESTASI --}}
                <tr class="table-active"><td colspan="2"><strong>ARUS KAS DARI AKTIVITAS INVESTASI</strong></td></tr>
                <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Masuk</strong></td>
                    <td></td>
                </tr>
                 <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas masuk dari aktivitas investasi</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['investasi_masuk'] ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Keluar</strong></td>
                    <td></td>
                </tr>
                <tr><td style="padding-left: 40px;">Pengeluaran kas untuk pembelian aset tetap</td><td class="text-right">{{ number_format(abs($kas['investasi_keluar']['pembelian_aset_tetap'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td style="padding-left: 40px;">Pengeluaran kas untuk pembelian investasi</td><td class="text-right">{{ number_format(abs($kas['investasi_keluar']['pembelian_investasi'] ?? 0), 0, ',', '.') }}</td></tr>
                 <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas keluar dari aktivitas investasi</strong></td>
                    <td class="text-right"><strong>({{ number_format(abs($totals['investasi_keluar'] ?? 0), 0, ',', '.') }})</strong></td>
                </tr>
                 <tr class="font-weight-bold" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td>Arus kas bersih dari aktivitas investasi</td>
                    <td class="text-right">{{ number_format($arus_kas_bersih['investasi'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS PENDANAAN --}}
                <tr class="table-active"><td colspan="2"><strong>ARUS KAS DARI AKTIVITAS PEMBIAYAAN</strong></td></tr>
                <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Masuk</strong></td>
                    <td></td>
                </tr>
                 <tr><td style="padding-left: 40px;">Penerimaan kas dari penyertaan modal desa</td><td class="text-right">{{ number_format($kas['pendanaan_masuk']['penyertaan_modal_desa'] ?? 0, 0, ',', '.') }}</td></tr>
                 <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas masuk dari aktivitas pembiayaan</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['pendanaan_masuk'] ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
                 <tr>
                    <td style="padding-left: 20px;"><strong>Arus Kas Keluar</strong></td>
                    <td></td>
                </tr>
                <tr class="bg-light">
                    <td style="padding-left: 20px;"><strong>Jumlah arus kas keluar dari aktivitas pembiayaan</strong></td>
                    <td class="text-right"><strong>({{ number_format(abs($totals['pendanaan_keluar'] ?? 0), 0, ',', '.') }})</strong></td>
                </tr>
                 <tr class="font-weight-bold" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td>Arus kas bersih dari aktivitas pembiayaan</td>
                    <td class="text-right">{{ number_format($arus_kas_bersih['pendanaan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- REKONSILIASI KAS --}}
                <tr style="border-top: 2px solid #6c757d;">
                    <td><strong>Kenaikan (penurunan) Kas</strong></td>
                    <td class="text-right"><strong>{{ number_format($kenaikan_penurunan_kas ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Saldo kas awal periode</td>
                    <td class="text-right">{{ number_format($saldoKasAwal ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-success" style="border-top: 1px solid #6c757d;">
                    <td><strong>Saldo kas akhir periode</strong></td>
                    <td class="text-right"><strong>{{ number_format($saldo_kas_akhir ?? 0, 0, ',', '.') }}</strong></td>
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
        .table-active, .bg-light, .table-success {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@stop