@extends('adminlte::page')

@section('title', 'Laporan Arus Kas')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Safeguards
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $startDate = $startDate ?? now();
    $endDate = $endDate ?? now();
    $tanggalCetak = $tanggalCetak ?? now();
    $lokasi = optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes';
    $penandaTangan1 = $penandaTangan1 ?? ['jabatan' => 'Direktur', 'nama' => ''];
    $penandaTangan2 = $penandaTangan2 ?? ['jabatan' => 'Bendahara', 'nama' => ''];
    $arusOperasi = $arusOperasi ?? ['items' => [], 'total' => 0];
    $arusInvestasi = $arusInvestasi ?? ['items' => [], 'total' => 0];
    $arusPendanaan = $arusPendanaan ?? ['items' => [], 'total' => 0];
    $kenaikanPenurunanKas = $kenaikanPenurunanKas ?? 0;
    $saldoKasAwal = $saldoKasAwal ?? 0;
    $saldoKasAkhir = $saldoKasAkhir ?? 0;
@endphp
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT --}}
        <div class="text-center" style="border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px;">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo" style="width: 80px; position: absolute; left: 40px; top: 30px;">
            @endif
            <h4 class="font-weight-bold mb-1">{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h4>
            <p class="mb-1">{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</p>
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Arus Kas</h5>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm">
            <tbody>
                {{-- ARUS KAS DARI AKTIVITAS OPERASI --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Operasi</strong></td>
                </tr>
                @forelse ($arusOperasi['items'] as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['deskripsi'] }}</td>
                        <td class="text-right" style="width: 25%">{{ 'Rp ' . number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada aktivitas operasi</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td><strong>Arus Kas Bersih dari Aktivitas Operasi</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($arusOperasi['total'], 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS INVESTASI --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Investasi</strong></td>
                </tr>
                @forelse ($arusInvestasi['items'] as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['deskripsi'] }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                     <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada aktivitas investasi</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td><strong>Arus Kas Bersih dari Aktivitas Investasi</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($arusInvestasi['total'], 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS PENDANAAN --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Pendanaan</strong></td>
                </tr>
                @forelse ($arusPendanaan['items'] as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['deskripsi'] }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada aktivitas pendanaan</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light" style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                    <td><strong>Arus Kas Bersih dari Aktivitas Pendanaan</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($arusPendanaan['total'], 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- REKONSILIASI KAS --}}
                <tr style="border-top: 2px solid #6c757d;">
                    <td><strong>Kenaikan (Penurunan) Bersih Kas</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($kenaikanPenurunanKas, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Saldo Kas Awal Periode</td>
                    <td class="text-right">{{ 'Rp ' . number_format($saldoKasAwal, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-success" style="border-top: 1px solid #6c757d;">
                    <td><strong>Saldo Kas Akhir Periode</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($saldoKasAkhir, 0, ',', '.') }}</strong></td>
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
