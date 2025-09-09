@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo Komparatif')

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
    $laporanData = $laporanData ?? [];
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
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Neraca Saldo Komparatif</h5>
            <p>Membandingkan Saldo per <strong>{{ $startDate->copy()->subDay()->isoFormat('D MMMM Y') }}</strong> dan <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center table-active">
                    <th rowspan="2" class="align-middle">Kode Akun</th>
                    <th rowspan="2" class="align-middle" style="width: 30%;">Nama Akun</th>
                    <th colspan="2">Saldo Awal</th>
                    <th colspan="2">Saldo Akhir</th>
                </tr>
                <tr class="text-center table-active">
                    <th style="width: 15%">Debit</th>
                    <th style="width: 15%">Kredit</th>
                    <th style="width: 15%">Debit</th>
                    <th style="width: 15%">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebitAwal = 0; $totalKreditAwal = 0;
                    $totalDebitAkhir = 0; $totalKreditAkhir = 0;
                @endphp
                @forelse ($laporanData as $data)
                    <tr>
                        <td class="text-center">{{ $data->kode_akun }}</td>
                        <td>{{ $data->nama_akun }}</td>
                        <td class="text-right">{{ $data->debit_awal > 0 ? 'Rp ' . number_format($data->debit_awal, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $data->kredit_awal > 0 ? 'Rp ' . number_format($data->kredit_awal, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $data->debit_akhir > 0 ? 'Rp ' . number_format($data->debit_akhir, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $data->kredit_akhir > 0 ? 'Rp ' . number_format($data->kredit_akhir, 0, ',', '.') : '-' }}</td>
                    </tr>
                    @php
                        $totalDebitAwal += $data->debit_awal; $totalKreditAwal += $data->kredit_awal;
                        $totalDebitAkhir += $data->debit_akhir; $totalKreditAkhir += $data->kredit_akhir;
                    @endphp
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-light font-weight-bold">
                    <th colspan="2" class="text-right">TOTAL</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalDebitAwal, 0, ',', '.') }}</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalKreditAwal, 0, ',', '.') }}</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalDebitAkhir, 0, ',', '.') }}</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalKreditAkhir, 0, ',', '.') }}</th>
                </tr>
                 <tr>
                    <th colspan="2" class="text-right">Status</th>
                    <th colspan="2" class="text-center">
                        @if (round($totalDebitAwal, 2) == round($totalKreditAwal, 2))
                            <span class="badge badge-success">Seimbang</span>
                        @else
                            <span class="badge badge-danger">Tidak Seimbang</span>
                        @endif
                    </th>
                    <th colspan="2" class="text-center">
                         @if (round($totalDebitAkhir, 2) == round($totalKreditAkhir, 2))
                            <span class="badge badge-success">Seimbang</span>
                        @else
                            <span class="badge badge-danger">Tidak Seimbang</span>
                        @endif
                    </th>
                </tr>
            </tfoot>
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

