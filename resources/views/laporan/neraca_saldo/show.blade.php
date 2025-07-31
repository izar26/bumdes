@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center">
            <h3>Neraca Saldo</h3>
            <h5>BUMDes Anda</h5>
            <p><strong>Per Tanggal {{ $reportDate->format('d F Y') }}</strong></p>
        </div>
        <hr>

        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center table-active">
                    <th style="width: 15%">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th style="width: 20%">Debit</th>
                    <th style="width: 20%">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanData as $data)
                    <tr>
                        <td class="text-center">{{ $data['kode_akun'] }}</td>
                        <td>{{ $data['nama_akun'] }}</td>
                        <td class="text-right">{{ $data['debit'] > 0 ? 'Rp ' . number_format($data['debit'], 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $data['kredit'] > 0 ? 'Rp ' . number_format($data['kredit'], 2, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <th colspan="2" class="text-right">Total</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalDebit, 2, ',', '.') }}</th>
                    <th class="text-right">{{ 'Rp ' . number_format($totalKredit, 2, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="2" class="text-right">Status</th>
                    <th colspan="2" class="text-center">
                        @if (round($totalDebit, 2) == round($totalKredit, 2))
                            <span class="badge badge-success">Seimbang (Balanced)</span>
                        @else
                            <span class="badge badge-danger">Tidak Seimbang (Unbalanced)</span>
                        @endif
                    </th>
                </tr>
            </tfoot>
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