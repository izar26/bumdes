@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content')
<div class="card">
    <div class="card-body">
        {{-- Header dengan logo dan info BUMDes --}}
        <div class="text-center mb-4">
            @if(!empty($bumdes->logo) && file_exists(public_path('storage/' . $bumdes->logo)))
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo BUMDes" style="height: 80px;">
            @endif
            <h3 class="mt-2 mb-0">{{ $bumdes->nama ?? 'Nama BUMDes' }}</h3>
            <p class="mb-0">{{ $bumdes->alamat ?? 'Alamat BUMDes' }}</p>
            <p><strong>Per Tanggal {{ isset($reportDate) ? $reportDate->format('d F Y') : date('d F Y') }}</strong></p>
        </div>

        <hr>

        @php
            $laporanData = $laporanData ?? [];
            $totalDebit = $totalDebit ?? collect($laporanData)->sum('debit');
            $totalKredit = $totalKredit ?? collect($laporanData)->sum('kredit');
        @endphp

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
                @forelse ($laporanData as $data)
                    <tr>
                        <td class="text-center">{{ $data['kode_akun'] ?? '' }}</td>
                        <td>{{ $data['nama_akun'] ?? '' }}</td>
                        <td class="text-right">{{ ($data['debit'] ?? 0) > 0 ? 'Rp ' . number_format($data['debit'], 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ ($data['kredit'] ?? 0) > 0 ? 'Rp ' . number_format($data['kredit'], 2, ',', '.') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
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

        {{-- Tanda tangan --}}
        <div class="row mt-5">
            <div class="col text-center">
                <p>{{ $bumdes->nama_desa ?? 'Nama Desa' }}, {{ date('d F Y') }}</p>
                <p>Kepala BUMDes</p>
                <br><br><br>
                <p><strong>{{ $bumdes->kepala ?? 'Nama Kepala' }}</strong></p>
            </div>
        </div>

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
