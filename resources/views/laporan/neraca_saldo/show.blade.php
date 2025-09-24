@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Helper function untuk format Rupiah
    function format_rp($value) {
        if ($value == 0) return 'Rp 0';
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
            <h3>Neraca Saldo</h3>
            <p>Per <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm" style="margin-top: 20px;">
            <thead>
                {{-- HEADER TABLE DIPERBARUI --}}
                <tr class="text-center">
                    <th style="width: 15%;">Kode Akun</th>
                    <th style="width: 40%;">Nama Akun</th>
                    <th style="width: 22.5%;">Debit</th>
                    <th style="width: 22.5%;">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebit = 0;
                    $totalKredit = 0;
                @endphp
                @forelse ($laporanData as $data)
                    <tr>
                        <td class="text-center">{{ $data->kode_akun }}</td>
                        <td>{{ $data->nama_akun }}</td>
                        <td class="text-right">{{ $data->saldo_debit > 0 ? format_rp($data->saldo_debit) : '' }}</td>
                        <td class="text-right">{{ $data->saldo_kredit > 0 ? format_rp($data->saldo_kredit) : '' }}</td>
                    </tr>
                    @php
                        $totalDebit += $data->saldo_debit;
                        $totalKredit += $data->saldo_kredit;
                    @endphp
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data untuk ditampilkan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-light font-weight-bold">
                    <th colspan="2" class="text-right">TOTAL</th>
                    <th class="text-right">{{ format_rp($totalDebit) }}</th>
                    <th class="text-right">{{ format_rp($totalKredit) }}</th>
                </tr>
                 <tr>
                    <th colspan="2" class="text-right">Status</th>
                    <th colspan="2" class="text-center">
                        @if (round($totalDebit, 2) == round($totalKredit, 2))
                            <span class="badge badge-success">Seimbang</span>
                        @else
                            <span class="badge badge-danger">Tidak Seimbang</span>
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>

        {{-- TANDA TANGAN (DIRAPIKAN) --}}
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
        height: 4px;
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
    }
    /* Menyesuaikan perataan teks khusus untuk footer */
    tfoot th {
        text-align: right;
    }
    tfoot th:last-child, tfoot th:nth-child(2) {
         text-align: center !important;
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
        .kop, .garis-pembatas, th, .bg-light {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@stop