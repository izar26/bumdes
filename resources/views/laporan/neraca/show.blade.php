@extends('adminlte::page')

@section('title', 'Laporan Neraca Komparatif')

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
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Posisi Keuangan Komparatif</h5>
            <p>Membandingkan Posisi Keuangan per <strong>{{ $startDate->copy()->subDay()->isoFormat('D MMMM Y') }}</strong> dan <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center table-active">
                    <th>Keterangan</th>
                    <th style="width: 25%">Saldo per {{ $startDate->copy()->subDay()->isoFormat('D MMM Y') }} (Awal)</th>
                    <th style="width: 25%">Saldo per {{ $endDate->isoFormat('D MMM Y') }} (Akhir)</th>
                </tr>
            </thead>
            <tbody>
                {{-- ASET --}}
                <tr class="bg-light font-weight-bold"><td colspan="3">ASET</td></tr>
                @foreach ($allAsetNames as $namaAkun)
                    <tr>
                        <td style="padding-left: 30px;">{{ $namaAkun }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAwal['Aset'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAkhir['Aset'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="font-weight-bold table-secondary">
                    <td>TOTAL ASET</td>
                    <td class="text-right">Rp {{ number_format(array_sum($dataAwal['Aset']), 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format(array_sum($dataAkhir['Aset']), 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>

                {{-- KEWAJIBAN & EKUITAS --}}
                <tr class="bg-light font-weight-bold"><td colspan="3">KEWAJIBAN DAN EKUITAS</td></tr>
                
                {{-- KEWAJIBAN --}}
                @foreach ($allKewajibanNames as $namaAkun)
                     <tr>
                        <td style="padding-left: 30px;">{{ $namaAkun }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAwal['Kewajiban'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAkhir['Kewajiban'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="font-weight-bold">
                    <td style="padding-left: 30px;"><strong>Total Kewajiban</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format(array_sum($dataAwal['Kewajiban']), 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format(array_sum($dataAkhir['Kewajiban']), 0, ',', '.') }}</strong></td>
                </tr>

                {{-- EKUITAS --}}
                @foreach ($allEkuitasNames as $namaAkun)
                     <tr>
                        <td style="padding-left: 30px;">{{ $namaAkun }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAwal['Ekuitas'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($dataAkhir['Ekuitas'][$namaAkun] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="padding-left: 30px;">Laba (Rugi) Tahun Berjalan</td>
                    <td class="text-right">{{ 'Rp ' . number_format($dataAwal['labaDitahan'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ 'Rp ' . number_format($dataAkhir['labaDitahan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="font-weight-bold">
                    <td style="padding-left: 30px;"><strong>Total Ekuitas</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($dataAwal['totalEkuitas'] ?? 0, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($dataAkhir['totalEkuitas'] ?? 0, 0, ',', '.') }}</strong></td>
                </tr>

                <tr class="font-weight-bold table-secondary">
                    <td>TOTAL KEWAJIBAN DAN EKUITAS</td>
                    <td class="text-right">Rp {{ number_format((array_sum($dataAwal['Kewajiban']) + ($dataAwal['totalEkuitas'] ?? 0)), 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((array_sum($dataAkhir['Kewajiban']) + ($dataAkhir['totalEkuitas'] ?? 0)), 0, ',', '.') }}</td>
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

        {{-- TOMBOL CETAK --}}
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

