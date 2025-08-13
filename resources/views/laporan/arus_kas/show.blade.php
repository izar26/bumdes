@extends('adminlte::page')

@section('title', 'Laporan Arus Kas')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop

@section('content')
@php
    // Safeguards untuk memastikan variabel ada
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $endDate = $endDate ?? now();
    $penandaTangan1 = $penandaTangan1 ?? [];
    $penandaTangan2 = $penandaTangan2 ?? [];
    $arusOperasi = $arusOperasi ?? [];
    $arusInvestasi = $arusInvestasi ?? [];
    $arusPendanaan = $arusPendanaan ?? [];
    $totalOperasi = $totalOperasi ?? 0;
    $totalInvestasi = $totalInvestasi ?? 0;
    $totalPendanaan = $totalPendanaan ?? 0;
    $kenaikanPenurunanKas = $kenaikanPenurunanKas ?? 0;
    $saldoKasAwal = $saldoKasAwal ?? 0;
    $saldoKasAkhir = $saldoKasAkhir ?? 0;
@endphp
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT (Menggunakan struktur baru yang standar) --}}
        <div class="kop">
            @if(optional($bumdes)->logo)
                <img src="{{ public_path('storage/' . $bumdes->logo) }}" alt="Logo">
            @else
                <img src="https://placehold.co/75x75/008080/FFFFFF?text=Logo" alt="Logo">
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
            <p>Untuk Periode yang Berakhir pada <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA (Menggunakan styling baru) --}}
        <table class="table-laporan table-arus-kas">
            <tbody>
                {{-- ARUS KAS DARI AKTIVITAS OPERASI --}}
                <tr class="header-row">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Operasi</strong></td>
                </tr>
                @forelse ($arusOperasi as $item)
                    <tr>
                        <td class="item-name">
                            @if($item['jumlah'] > 0)
                                Penerimaan dari {{ $item['nama'] }}
                            @else
                                Pembayaran untuk {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="item-value">{{ 'Rp ' . $item['jumlah'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada aktivitas operasi</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Arus Kas Bersih dari Aktivitas Operasi</strong></td>
                    <td class="item-value"><strong>{{ 'Rp ' . $saldoKasAkhir }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                {{-- ARUS KAS DARI AKTIVITAS INVESTASI --}}
                <tr class="header-row">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Investasi</strong></td>
                </tr>
                 @forelse ($arusInvestasi as $item)
                    <tr>
                        <td class="item-name">
                            @if($item['jumlah'] > 0)
                                Penjualan {{ $item['nama'] }}
                            @else
                                Pembelian {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="item-value">{{ 'Rp ' . $saldoKasAkhir }}</td>
                    </tr>
                @empty
                     <tr>
                        <td class="item-name">Tidak ada aktivitas investasi</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Arus Kas Bersih dari Aktivitas Investasi</strong></td>
                    <td class="item-value"><strong>{{ 'Rp ' . $saldoKasAkhir }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                {{-- ARUS KAS DARI AKTIVITAS PENDANAAN --}}
                <tr class="header-row">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Pendanaan</strong></td>
                </tr>
                @forelse ($arusPendanaan as $item)
                    <tr>
                        <td class="item-name">
                             @if($item['jumlah'] > 0)
                                Penerimaan {{ $item['nama'] }}
                            @else
                                Pembayaran {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="item-value">{{ 'Rp ' . $saldoKasAkhir }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada aktivitas pendanaan</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Arus Kas Bersih dari Aktivitas Pendanaan</strong></td>
                    <td class="item-value"><strong>{{ 'Rp ' . $saldoKasAkhir }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                {{-- REKONSILIASI KAS --}}
                <tr class="reconciliation-row">
                    <td><strong>Kenaikan (Penurunan) Bersih Kas</strong></td>
                    <td class="item-value"><strong>{{ 'Rp ' . $saldoKasAkhir }}</strong></td>
                </tr>
                <tr class="reconciliation-row">
                    <td>Saldo Kas Awal Periode</td>
                    <td class="item-value">{{ 'Rp ' . $saldoKasAkhir }}</td>
                </tr>
                <tr class="grand-total-row">
                    <td><strong>Saldo Kas Akhir Periode</strong></td>
                    <td class="item-value"><strong>{{ 'Rp ' .$saldoKasAkhir }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN (Menggunakan struktur baru yang standar) --}}
        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    {{ 'Cianjur' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                 <td>{{-- Kosong untuk spasi --}}</td>
                <td>Menyetujui,</td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $penandaTangan1['jabatan'] ?? 'Direktur' }}</strong>
                </td>
                <td>
                    <strong>{{ $penandaTangan2['jabatan'] ?? 'Bendahara' }}</strong>
                </td>
            </tr>
            <tr class="ttd-space">
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="nama-terang">
                    ({{ $penandaTangan1['nama'] ?? '.........................................' }})
                </td>
                <td class="nama-terang">
                    ({{ $penandaTangan2['nama'] ?? '.........................................' }})
                </td>
            </tr>
        </table>

        {{-- TOMBOL CETAK (non-print) --}}
        <div class="mt-4 text-right no-print">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    /* Styling Laporan Standar */
    .kop { display: flex; align-items: center; }
    .kop img { height: 75px; margin-right: 15px; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h1 { margin: 0; font-size: 22px; text-transform: uppercase; color: #006666; }
    .kop-text h2 { margin: 2px 0 0; font-size: 14px; font-weight: normal; color: #333; }
    .kop-text p { margin: 2px 0; font-size: 12px; }
    .garis-pembatas { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 4px; margin-top: 5px; margin-bottom: 20px; }
    .judul { text-align: center; margin-bottom: 20px; }
    .judul h3 { margin: 5px 0; font-size: 16px; text-transform: uppercase; }
    .judul p { margin: 2px 0; }
    
    /* Styling Tabel Laporan Arus Kas */
    .table-laporan { width: 100%; }
    .table-arus-kas, .table-arus-kas td { border: none !important; }
    .table-arus-kas .header-row td { font-size: 14px; padding-top: 15px; }
    .table-arus-kas .item-name { padding-left: 30px !important; }
    .table-arus-kas .item-value { text-align: right; width: 30%; }
    .table-arus-kas .total-row td { border-top: 1px solid #ccc !important; padding-top: 5px; }
    .table-arus-kas .reconciliation-row td { padding-top: 5px; }
    .table-arus-kas .grand-total-row td { border-top: 3px double #000 !important; font-size: 14px; padding-top: 8px; font-weight: bold; }
    .table-arus-kas .spacer { border: none; padding: 10px; }

    /* Styling Footer Tanda Tangan */
    .footer { margin-top: 50px; width: 100%; border: none; }
    .footer td { border: none; padding: 5px; text-align: center; }
    .ttd-space { height: 70px; }
    .nama-terang { text-decoration: underline; font-weight: bold; }

    /* Aturan untuk Mencetak */
    @media print {
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer { display: none !important; }
        .content-wrapper, .content, .card, .card-body { margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
        .kop { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@stop
