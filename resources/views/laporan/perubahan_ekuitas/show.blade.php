@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop

@section('content')
@php
    // Safeguards agar tidak undefined
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $reportDate = $reportDate ?? now();
    $laporanData = (isset($laporanData) && is_iterable($laporanData)) ? $laporanData : [];
    $penandaTangan1 = $penandaTangan1 ?? [];
    $penandaTangan2 = $penandaTangan2 ?? [];

    // Hitung total jika belum disediakan dari controller
    $totalDebit = $totalDebit ?? 0;
    $totalKredit = $totalKredit ?? 0;
    if (($totalDebit === 0 && $totalKredit === 0) && !empty($laporanData)) {
        foreach ($laporanData as $row) {
            $totalDebit += (float)($row['debit'] ?? 0);
            $totalKredit += (float)($row['kredit'] ?? 0);
        }
    }
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
            <h3>Laporan Neraca Saldo</h3>
            <p>Per Tanggal: <strong>{{ $reportDate->format('d F Y') }}</strong></p>
        </div>

        {{-- TABEL DATA (Menggunakan styling baru) --}}
        <table class="table-laporan">
            <thead>
                <tr>
                    <th style="width: 15%;">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th style="width: 20%;">Debit</th>
                    <th style="width: 20%;">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanData as $data)
                    <tr>
                        <td class="text-center">{{ $data['kode_akun'] ?? '' }}</td>
                        <td>{{ $data['nama_akun'] ?? '' }}</td>
                        <td class="text-right">{{ ($data['debit'] ?? 0) > 0 ? 'Rp ' . $data['debit'] : '-' }}</td>
                        <td class="text-right">{{ ($data['kredit'] ?? 0) > 0 ? 'Rp ' . $data['kredit'] : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <th colspan="2" class="text-right">TOTAL</th>
                    <th class="text-right">{{ 'Rp ' . $totalDebit }}</th>
                    <th class="text-right">{{ 'Rp ' . $totalKredit }}</th>
                </tr>
                <tr class="status-row">
                    <th colspan="2" class="text-right">Status</th>
                    <th colspan="2" class="text-center">
                        @if (round($totalDebit, 2) == round($totalKredit, 2))
                            <span class="badge-balance success">Seimbang (Balanced)</span>
                        @else
                            <span class="badge-balance danger">Tidak Seimbang (Unbalanced)</span>
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>

        {{-- TANDA TANGAN (Menggunakan struktur baru yang standar) --}}
        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    {{  'Cianjur' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
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

    /* Styling Tabel Laporan */
    .table-laporan { border-collapse: collapse; width: 100%; font-size: 12px; }
    .table-laporan th { background: #008080; color: white; border: 1px solid #006666; padding: 8px; text-align: center; }
    .table-laporan td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
    .table-laporan tfoot th { background-color: #f8f9fa; color: #333; }
    .table-laporan .total-row th { background-color: #e6f2f2; }
    .table-laporan .status-row th { background-color: #fff; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-balance { padding: 5px 10px; border-radius: 12px; color: white; font-size: 12px; }
    .badge-balance.success { background-color: #28a745; }
    .badge-balance.danger { background-color: #dc3545; }

    /* Styling Footer Tanda Tangan */
    .footer { margin-top: 50px; width: 100%; border: none; }
    .footer td { border: none; padding: 5px; text-align: center; }
    .ttd-space { height: 70px; }
    .nama-terang { text-decoration: underline; font-weight: bold; }

    /* Aturan untuk Mencetak */
    @media print {
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer { display: none !important; }
        .content-wrapper, .content, .card, .card-body { margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
        .table-laporan th, .kop, .badge-balance { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@stop
