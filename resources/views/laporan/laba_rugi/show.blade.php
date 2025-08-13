@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="card">
    <div class="card-body">
        {{-- Header dengan logo dan identitas BUMDes --}}
        <div class="d-flex align-items-center mb-3">
            @if($bumdes && $bumdes->logo)
                <img src="{{ public_path('storage/' . $bumdes->logo) }}" alt="Logo" style="height:70px; margin-right:15px;">
            @endif
            <div class="text-center flex-fill">
                <h3 class="mb-0">Laporan Laba Rugi</h3>
                <h5 class="mb-0">{{ $bumdes->nama_bumdes ?? 'BUMDes Anda' }}</h5>
                <p class="mb-0"><strong>{{ $bumdes->alamat ?? '' }}</strong></p>
                <p><strong>Periode Berakhir {{ $endDate->format('d F Y') }}</strong></p>
            </div>
        </div>
        <hr style="border-top: 3px solid #000;">

        <table class="table table-borderless">
            <tbody>
                {{-- Pendapatan --}}
                <tr>
                    <td colspan="2"><strong>Pendapatan</strong></td>
                </tr>
                @forelse ($pendapatans as $pendapatan)
                    <tr>
                        <td style="padding-left: 30px; border-bottom: 1px solid #ddd;">{{ $pendapatan['nama_akun'] }}</td>
                        <td class="text-right" style="border-bottom: 1px solid #ddd;">{{ 'Rp ' . number_format($pendapatan['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;">Tidak ada pendapatan</td>
                        <td class="text-right">Rp 0,00</td>
                    </tr>
                @endforelse
                <tr class="table-active" style="border-top: 2px solid #000;">
                    <td><strong>Total Pendapatan</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalPendapatan, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Beban --}}
                <tr>
                    <td colspan="2"><strong>Beban Operasional</strong></td>
                </tr>
                @forelse ($bebans as $beban)
                    <tr>
                        <td style="padding-left: 30px; border-bottom: 1px solid #ddd;">{{ $beban['nama_akun'] }}</td>
                        <td class="text-right" style="border-bottom: 1px solid #ddd;">{{ 'Rp ' . number_format($beban['total'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 30px;">Tidak ada beban</td>
                        <td class="text-right">Rp 0,00</td>
                    </tr>
                @endforelse
                <tr class="table-active" style="border-top: 2px solid #000;">
                    <td><strong>Total Beban</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalBeban, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Laba/Rugi Bersih --}}
                @php
                    $labaRugiClass = $labaRugi >= 0 ? 'text-success' : 'text-danger';
                    $labaRugiLabel = $labaRugi >= 0 ? 'Laba Bersih' : 'Rugi Bersih';
                @endphp
                <tr class="bg-light" style="border-top: 3px double #000;">
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
    <style>
        @media print {
            .main-sidebar, .main-header, .btn, .content-header, .no-print {
                display: none !important;
            }
            .content-wrapper, .content {
                margin: 0 !important;
                padding: 0 !important;
            }
            table {
                font-size: 14px;
            }
        }
    </style>
@stop
