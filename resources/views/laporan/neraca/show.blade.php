@extends('adminlte::page')

@section('title', 'Laporan Posisi Keuangan')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu saat cetak --}}
@stop

@section('content')
@php
    // Helper function untuk format Rupiah
    function format_rp($value) {
        if ($value == 0) return 'Rp 0';
        if ($value < 0) {
            return '(Rp ' . number_format(abs($value), 0, ',', '.') . ')';
        }
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
            <h5 class="font-weight-bold mt-3 mb-1">Laporan Posisi Keuangan</h5>
            <p>Untuk Posisi per <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm" style="width: 100%;">
            <thead>
                <tr class="text-center table-active">
                    <th>Keterangan</th>
                    <th style="width: 25%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                {{-- ASET --}}
                <tr class="bg-light font-weight-bold"><td colspan="2">ASET</td></tr>
                <tr><td class="pl-4"><strong>Aset Lancar</strong></td><td class="text-right"></td></tr>
                <tr><td class="pl-5">Kas</td><td class="text-right">{{ format_rp($data['kas']) }}</td></tr>
                <tr><td class="pl-5">Setara Kas</td><td class="text-right">{{ format_rp($data['setara_kas']) }}</td></tr>
                <tr><td class="pl-5">Piutang</td><td class="text-right">{{ format_rp($data['piutang']) }}</td></tr>
                <tr><td class="pl-5">Penyisihan Piutang</td><td class="text-right">{{ format_rp($data['penyisihan_piutang']) }}</td></tr>
                <tr><td class="pl-5">Persediaan</td><td class="text-right">{{ format_rp($data['persediaan']) }}</td></tr>
                <tr><td class="pl-5">Perlengkapan</td><td class="text-right">{{ format_rp($data['perlengkapan']) }}</td></tr>
                <tr><td class="pl-5">Pembayaran Dimuka</td><td class="text-right">{{ format_rp($data['pembayaran_dimuka']) }}</td></tr>
                <tr><td class="pl-5">Aset Lancar Lainnya</td><td class="text-right">{{ format_rp($data['aset_lancar_lainnya']) }}</td></tr>
                <tr class="font-weight-bold table-secondary"><td class="pl-4">Total Aset Lancar</td><td class="text-right">{{ format_rp($data['total_aset_lancar']) }}</td></tr>
                
                <tr><td class="pl-4"><strong>Investasi</strong></td><td class="text-right">{{ format_rp($data['investasi']) }}</td></tr>

                <tr><td class="pl-4"><strong>Aset Tetap</strong></td><td></td></tr>
                <tr><td class="pl-5">Tanah</td><td class="text-right">{{ format_rp($data['tanah']) }}</td></tr>
                <tr><td class="pl-5">Kendaraan</td><td class="text-right">{{ format_rp($data['kendaraan']) }}</td></tr>
                <tr><td class="pl-5">Peralatan dan Mesin</td><td class="text-right">{{ format_rp($data['peralatan']) }}</td></tr>
                <tr><td class="pl-5">Meubelair</td><td class="text-right">{{ format_rp($data['meubelair']) }}</td></tr>
                <tr><td class="pl-5">Gedung dan Bangunan</td><td class="text-right">{{ format_rp($data['gedung']) }}</td></tr>
                <tr><td class="pl-5">Akumulasi Penyusutan Aset Tetap</td><td class="text-right">{{ format_rp($data['akumulasi_penyusutan']) }}</td></tr>
                <tr class="font-weight-bold table-secondary"><td class="pl-4">Total Aset Tetap</td><td class="text-right">{{ format_rp($data['total_aset_tetap']) }}</td></tr>
                
                <tr class="font-weight-bold table-primary">
                    <td>TOTAL ASET</td><td class="text-right">{{ format_rp($data['total_aset']) }}</td>
                </tr>
                
                {{-- KEWAJIBAN & EKUITAS --}}
                <tr class="bg-light font-weight-bold"><td colspan="2">KEWAJIBAN DAN EKUITAS</td></tr>
                <tr><td class="pl-4"><strong>Kewajiban Jangka Pendek</strong></td><td></td></tr>
                <tr><td class="pl-5">Utang Usaha</td><td class="text-right">{{ format_rp($data['utang_usaha']) }}</td></tr>
                <tr><td class="pl-5">Utang Pajak</td><td class="text-right">{{ format_rp($data['utang_pajak']) }}</td></tr>
                <tr><td class="pl-5">Utang Gaji/Upah dan Tunjangan</td><td class="text-right">{{ format_rp($data['utang_gaji']) }}</td></tr>
                <tr><td class="pl-5">Utang Jangka Pendek Lainnya</td><td class="text-right">{{ format_rp($data['utang_pendek_lainnya']) }}</td></tr>
                <tr class="font-weight-bold table-secondary"><td class="pl-4">Total Kewajiban Jangka Pendek</td><td class="text-right">{{ format_rp($data['total_kewajiban_pendek']) }}</td></tr>
                
                <tr><td class="pl-4"><strong>Kewajiban Jangka Panjang</strong></td><td class="text-right">{{ format_rp($data['utang_panjang']) }}</td></tr>
                <tr class="font-weight-bold table-info"><td class="pl-4">TOTAL KEWAJIBAN</td><td class="text-right">{{ format_rp($data['total_kewajiban']) }}</td></tr>

                <tr><td class="pl-4"><strong>Ekuitas</strong></td><td></td></tr>
                <tr><td class="pl-5">Modal Disetor</td><td class="text-right">{{ format_rp($data['modal_disetor']) }}</td></tr>
                <tr><td class="pl-5">Saldo Laba</td><td class="text-right">{{ format_rp($data['saldo_laba']) }}</td></tr>
                <tr class="font-weight-bold table-info"><td class="pl-4">TOTAL EKUITAS</td><td class="text-right">{{ format_rp($data['ekuitas_akhir']) }}</td></tr>
                
                <tr class="font-weight-bold table-primary">
                    <td>TOTAL KEWAJIBAN DAN EKUITAS</td><td class="text-right">{{ format_rp($data['total_kewajiban_ekuitas']) }}</td>
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

