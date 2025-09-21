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

        {{-- KOP SURAT --}}
        <div class="text-center" style="border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px;">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo" style="width: 80px; position: absolute; left: 40px; top: 30px;">
            @endif
            <h4 class="font-weight-bold mb-1">{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h4>
            <p class="mb-1">{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</p>
            <h5 class="font-weight-bold mt-3 mb-1">Neraca Saldo</h5>
            <p>Per <strong>{{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>

        {{-- TABEL DATA --}}
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center table-active">
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

        {{-- TANDA TANGAN --}}
        <table style="margin-top: 60px; width: 100%;" class="table-borderless">
            <tr>
                <td style="text-align: center; width: 50%;">Mengetahui,</td>
                <td style="text-align: center; width: 50%;">
                    {{ optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes' }}, {{ $tanggalCetak->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                 <td style="text-align: center;"><strong>{{ $penandaTangan2['jabatan'] ?? 'Bendahara' }}</strong></td>
                <td style="text-align: center;">Menyetujui,</td>
            </tr>
            <tr>
               <td></td>
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

