@extends('adminlte::page')

@section('title', 'Laporan Neraca Saldo')

@section('content')
@php
    // Safeguards agar tidak undefined
    $bumdes = $bumdes ?? \App\Models\Bungdes::first();
    $reportDate = $reportDate ?? now();
    $laporanData = (isset($laporanData) && is_iterable($laporanData)) ? $laporanData : [];

    // Hitung total jika belum disediakan dari controller
    $totalDebit = $totalDebit ?? 0;
    $totalKredit = $totalKredit ?? 0;
    if (($totalDebit === 0 && $totalKredit === 0) && !empty($laporanData)) {
        foreach ($laporanData as $row) {
            $totalDebit += (float)($row['debit'] ?? 0);
            $totalKredit += (float)($row['kredit'] ?? 0);
        }
    }
@endphp
<div class="card">
    <div class="card-body">
        {{-- Header konsisten dengan logo kiri, identitas di tengah --}}
        <div class="row align-items-center mb-3">
            <div class="col-2 text-center">
                @if(optional($bumdes)->logo)
                    <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo BUMDes" style="max-height: 80px;">
                @endif
            </div>
            <div class="col-10 text-center">
                <h3 class="mb-0">NERACA SALDO</h3>
                <h5 class="mb-0">{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h5>
                <p class="mb-0">{{ optional($bumdes)->alamat ?? '' }}</p>
                <p><strong>Per Tanggal {{ $reportDate->format('d F Y') }}</strong></p>
            </div>
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
                @forelse ($laporanData as $data)
                    @php
                        $kode = $data['kode_akun'] ?? '';
                        $nama = $data['nama_akun'] ?? '';
                        $debit = (float)($data['debit'] ?? 0);
                        $kredit = (float)($data['kredit'] ?? 0);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $kode }}</td>
                        <td>{{ $nama }}</td>
                        <td class="text-right">{{ $debit > 0 ? 'Rp ' . number_format($debit, 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $kredit > 0 ? 'Rp ' . number_format($kredit, 2, ',', '.') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data ditampilkan.</td>
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

        {{-- Tanda tangan konsisten --}}
        <div class="row mt-5">
            <div class="col-6"></div>
            <div class="col-6 text-center">
                <p>{{ optional($bumdes)->desa ?? '_________________' }}, {{ $reportDate->translatedFormat('d F Y') }}</p>
                <p><strong>{{ optional($bumdes)->jabatan_ttd ?? 'Ketua BUMDes' }}</strong></p>
                <br><br><br>
                <p><strong><u>{{ optional($bumdes)->nama_ttd ?? '_________________' }}</u></strong></p>
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
