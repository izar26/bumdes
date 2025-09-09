@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Safeguards untuk memastikan variabel ada dan memiliki struktur default
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
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Laba Rugi</h5>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm">
             <tbody>
                {{-- PENDAPATAN --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Pendapatan</strong></td>
                </tr>
                @forelse ($pendapatans as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['nama_akun'] }}</td>
                        <td class="text-right" style="width: 25%">{{ 'Rp ' . number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada pendapatan</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light">
                    <td><strong>Total Pendapatan</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- HPP --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Harga Pokok Penjualan (HPP)</strong></td>
                </tr>
                 @forelse ($hpps as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['nama_akun'] }}</td>
                        <td class="text-right">({{ 'Rp ' . number_format($item['total'], 0, ',', '.') }})</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada HPP</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light">
                    <td><strong>Total HPP</strong></td>
                    <td class="text-right"><strong>({{ 'Rp ' . number_format($totalHpp, 0, ',', '.') }})</strong></td>
                </tr>

                {{-- LABA KOTOR --}}
                <tr class="table-secondary font-weight-bold">
                    <td>LABA KOTOR</td>
                    <td class="text-right">{{ 'Rp ' . number_format($labaKotor, 0, ',', '.') }}</td>
                </tr>
                 <tr><td colspan="2">&nbsp;</td></tr>

                {{-- BEBAN --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Beban Operasional</strong></td>
                </tr>
                @forelse ($bebans as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['nama_akun'] }}</td>
                        <td class="text-right">({{ 'Rp ' . number_format($item['total'], 0, ',', '.') }})</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada beban</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endforelse
                <tr class="bg-light">
                    <td><strong>Total Beban Operasional</strong></td>
                    <td class="text-right"><strong>({{ 'Rp ' . number_format($totalBeban, 0, ',', '.') }})</strong></td>
                </tr>

                 {{-- LABA OPERASIONAL --}}
                <tr class="table-secondary font-weight-bold">
                    <td>LABA OPERASIONAL</td>
                    <td class="text-right">{{ 'Rp ' . number_format($labaOperasional, 0, ',', '.') }}</td>
                </tr>
                 <tr><td colspan="2">&nbsp;</td></tr>

                {{-- PENDAPATAN & BEBAN LAIN --}}
                 <tr class="table-active">
                    <td colspan="2"><strong>Pendapatan & Beban Lain-lain</strong></td>
                </tr>
                {{-- --- PERBAIKAN SINTAKS DIMULAI DI SINI --- --}}
                @forelse ($pendapatanLains as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['nama_akun'] }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    {{-- Dikosongkan agar tidak ada pesan duplikat --}}
                @endforelse
                @forelse ($bebanLains as $item)
                    <tr>
                        <td style="padding-left: 30px;">{{ $item['nama_akun'] }}</td>
                        <td class="text-right">({{ 'Rp ' . number_format($item['total'], 0, ',', '.') }})</td>
                    </tr>
                @empty
                    {{-- Dikosongkan agar tidak ada pesan duplikat --}}
                @endforelse
                @if(empty($pendapatanLains) && empty($bebanLains))
                     <tr>
                        <td style="padding-left: 30px;" class="text-muted"><em>Tidak ada pendapatan atau beban lain-lain</em></td>
                        <td class="text-right"></td>
                    </tr>
                @endif
                {{-- --- AKHIR PERBAIKAN SINTAKS --- --}}
                
                {{-- LABA BERSIH --}}
                <tr class="table-success font-weight-bold" style="border-top: 3px double #000;">
                    <td>LABA BERSIH</td>
                    <td class="text-right">{{ 'Rp ' . number_format($labaRugiBersih, 0, ',', '.') }}</td>
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

