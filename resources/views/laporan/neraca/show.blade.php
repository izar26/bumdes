@extends('adminlte::page')

@section('title', 'Laporan Neraca')

@section('content')
@php
    $bumdes = \App\Models\Bungdes::first();
@endphp
<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px;">
                <img src="{{ asset('images/logo-bumdes.png') }}" alt="Logo BUMDes" style="height: 80px;">
                <div>
                    <h3 style="margin: 0;">Laporan Posisi Keuangan (Neraca)</h3>
                    <h5 style="margin: 0;">{{ $bumdes->nama_bumdes ?? 'BUMDes Anda' }}</h5>
                    <p style="margin: 0;">{{ $bumdes->alamat ?? 'Alamat BUMDes' }}</p>
                    <p><strong>Per Tanggal {{ $reportDate->format('d F Y') }}</strong></p>
                </div>
            </div>
        </div>
        <hr>

        <div class="row">
            {{-- KOLOM KIRI: ASET --}}
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="table-active">
                            <th>ASET</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($asets as $aset)
                        <tr>
                            <td style="padding-left: 20px;">{{ $aset['nama_akun'] }}</td>
                            <td class="text-right">{{ 'Rp ' . number_format($aset['total'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">Tidak ada data aset.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th>TOTAL ASET</th>
                            <th class="text-right">{{ 'Rp ' . number_format($totalAset, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- KOLOM KANAN: KEWAJIBAN & EKUITAS --}}
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="table-active">
                            <th>KEWAJIBAN DAN EKUITAS</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Kewajiban --}}
                        <tr>
                            <td colspan="2"><strong>Kewajiban</strong></td>
                        </tr>
                        @forelse ($kewajibans as $kewajiban)
                        <tr>
                            <td style="padding-left: 20px;">{{ $kewajiban['nama_akun'] }}</td>
                            <td class="text-right">{{ 'Rp ' . number_format($kewajiban['total'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">Tidak ada data kewajiban.</td>
                        </tr>
                        @endforelse
                        <tr class="table-active">
                            <td><strong>Total Kewajiban</strong></td>
                            <td class="text-right"><strong>{{ 'Rp ' . number_format($totalKewajiban, 2, ',', '.') }}</strong></td>
                        </tr>
                        
                        {{-- Ekuitas --}}
                        <tr>
                            <td colspan="2"><strong>Ekuitas</strong></td>
                        </tr>
                        @foreach ($ekuitas as $modal)
                        <tr>
                            <td style="padding-left: 20px;">{{ $modal['nama_akun'] }}</td>
                            <td class="text-right">{{ 'Rp ' . number_format($modal['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td style="padding-left: 20px;">Laba (Rugi) Ditahan</td>
                            <td class="text-right">{{ 'Rp ' . number_format($labaDitahan, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Total Ekuitas</strong></td>
                            <td class="text-right"><strong>{{ 'Rp ' . number_format($totalEkuitas, 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th>TOTAL KEWAJIBAN DAN EKUITAS</th>
                            <th class="text-right">{{ 'Rp ' . number_format($totalKewajiban + $totalEkuitas, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
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
