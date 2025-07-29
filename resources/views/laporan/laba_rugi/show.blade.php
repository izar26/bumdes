@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center">
            <h3>Laporan Laba Rugi</h3>
            <h5>BUMDes Anda</h5>
            <p><strong>Untuk Periode yang Berakhir pada {{ $endDate->format('d F Y') }}</strong></p>
        </div>
        <hr>
        
        <table class="table table-borderless">
            <tbody>
                {{-- Bagian Pendapatan --}}
                <tr>
                    <td colspan="2"><strong>Pendapatan</strong></td>
                </tr>
                @forelse ($pendapatans as $pendapatan)
                    <tr>
                        <td style="padding-left: 30px;">{{ $pendapatan['nama_akun'] }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($pendapatan['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;">Tidak ada pendapatan</td>
                        <td class="text-right">Rp 0,00</td>
                    </tr>
                @endforelse
                <tr class="table-active">
                    <td><strong>Total Pendapatan</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalPendapatan, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Bagian Beban --}}
                <tr>
                    <td colspan="2"><strong>Beban Operasional</strong></td>
                </tr>
                @forelse ($bebans as $beban)
                    <tr>
                        <td style="padding-left: 30px;">{{ $beban['nama_akun'] }}</td>
                        <td class="text-right">{{ 'Rp ' . number_format($beban['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;">Tidak ada beban</td>
                        <td class="text-right">Rp 0,00</td>
                    </tr>
                @endforelse
                <tr class="table-active">
                    <td><strong>Total Beban</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalBeban, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Bagian Laba Rugi Bersih --}}
                @php
                    $labaRugiClass = $labaRugi >= 0 ? 'text-success' : 'text-danger';
                    $labaRugiLabel = $labaRugi >= 0 ? 'Laba Bersih' : 'Rugi Bersih';
                @endphp
                <tr class="bg-light">
                    <td><h4><strong>{{ $labaRugiLabel }}</strong></h4></td>
                    <td class="text-right"><h4 class="{{ $labaRugiClass }}"><strong>{{ 'Rp ' . number_format($labaRugi, 2, ',', '.') }}</strong></h4></td>
                </tr>

            </tbody>
        </table>
        
        <div class="mt-4 text-right no-print">
            <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print"></i> Cetak</button>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- CSS khusus untuk halaman cetak agar rapi --}}
    <style>
        @media print {
            .main-sidebar, .main-header, .btn, .content-header, .no-print {
                display: none !important;
            }
            .content-wrapper, .content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@stop