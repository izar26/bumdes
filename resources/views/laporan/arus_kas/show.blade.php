@extends('adminlte::page')

@section('title', 'Laporan Arus Kas')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center">
            <h4>Laporan Arus Kas</h4>
            <h5>BUMDes Anda</h5>
            <p><strong>Untuk Periode yang Berakhir pada {{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>
        <hr>

        <table class="table table-sm table-borderless">
            <tbody>
                {{-- ARUS KAS DARI AKTIVITAS OPERASI --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Operasi</strong></td>
                </tr>
                @php $kasMasukOperasi = 0; $kasKeluarOperasi = 0; @endphp
                @foreach ($arusOperasi as $item)
                    <tr>
                        <td style="padding-left: 30px;">
                            {{-- Menyesuaikan nama agar lebih mudah dibaca --}}
                            @if($item['jumlah'] > 0)
                                Penerimaan dari {{ $item['nama'] }}
                            @else
                                Pembayaran untuk {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['jumlah'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="bg-light">
                    <td><strong>Arus Kas Bersih dari Aktivitas Operasi</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalOperasi, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS INVESTASI --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Investasi</strong></td>
                </tr>
                @foreach ($arusInvestasi as $item)
                    <tr>
                        <td style="padding-left: 30px;">
                             @if($item['jumlah'] > 0)
                                Penjualan {{ $item['nama'] }}
                            @else
                                Pembelian {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['jumlah'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="bg-light">
                    <td><strong>Arus Kas Bersih dari Aktivitas Investasi</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalInvestasi, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- ARUS KAS DARI AKTIVITAS PENDANAAN --}}
                <tr class="table-active">
                    <td colspan="2"><strong>Arus Kas dari Aktivitas Pendanaan</strong></td>
                </tr>
                @foreach ($arusPendanaan as $item)
                    <tr>
                        <td style="padding-left: 30px;">
                            @if($item['jumlah'] > 0)
                                Penerimaan {{ $item['nama'] }}
                            @else
                                Pembayaran {{ $item['nama'] }}
                            @endif
                        </td>
                        <td class="text-right">{{ 'Rp ' . number_format($item['jumlah'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="bg-light">
                    <td><strong>Arus Kas Bersih dari Aktivitas Pendanaan</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($totalPendanaan, 2, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2"><hr></td></tr>

                {{-- TOTAL --}}
                <tr>
                    <td><strong>Kenaikan (Penurunan) Bersih Kas</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . number_format($kenaikanPenurunanKas, 2, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Saldo Kas Awal Periode</td>
                    <td class="text-right">{{ 'Rp ' . number_format($saldoKasAwal, 2, ',', '.') }}</td>
                </tr>
                <tr class="table-active">
                    <td><h4><strong>Saldo Kas Akhir Periode</strong></h4></td>
                    <td class="text-right"><h4><strong>{{ 'Rp ' . number_format($saldoKasAkhir, 2, ',', '.') }}</strong></h4></td>
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
        }
    </style>
@stop
