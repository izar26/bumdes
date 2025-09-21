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

        {{-- KOP SURAT --}}
        <div class="text-center" style="border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px;">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo" style="width: 80px; position: absolute; left: 40px; top: 30px;">
            @endif
            <h4 class="font-weight-bold mb-1">{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h4>
            <p class="mb-1">{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</p>
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Perubahan Ekuitas</h5>
            <p>Untuk Periode <strong>{{ $startDate->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-borderless table-sm">
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
