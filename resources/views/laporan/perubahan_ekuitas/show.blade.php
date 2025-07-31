@extends('adminlte::page')

@section('title', 'Laporan Perubahan Ekuitas')

@section('content')
<div class="card">
    <div class="card-body">
        {{-- KOP SURAT --}}
        <div class="text-center">
            {{-- Ganti dengan nama BUMDes Anda --}}
            <h4>BUMDES SINDANGRAJA SEJAHTERA</h4>
            <h5>LAPORAN PERUBAHAN EKUITAS</h5>
            <p><strong>Untuk Periode yang berakhir sampai dengan {{ $endDate->isoFormat('D MMMM Y') }}</strong></p>
        </div>
        <hr>

        {{-- ISI LAPORAN --}}
        <table class="table table-sm table-borderless">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 55%;">Uraian</th>
                    <th class="text-right" colspan="2">{{ $endDate->year }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- PENYERTAAN MODAL --}}
                <tr class="table-active">
                    <td><strong>1</strong></td>
                    <td colspan="3"><strong>PENYERTAAN MODAL</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Penyertaan Modal Desa Awal</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($modalDesaAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Penyertaan Modal Masyarakat Awal</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($modalMasyarakatAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 20px;">Penambahan Investasi periode berjalan:</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 40px;">Penyertaan Modal Desa</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($penambahanModalDesa, 0, ',', '.') }}</td>
                </tr>
                 <tr>
                    <td></td>
                    <td style="padding-left: 40px;">Penyertaan Modal Masyarakat</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($penambahanModalMasyarakat, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-light">
                    <td></td>
                    <td><strong>Penyertaan Modal Akhir</strong></td>
                    <td><strong>Rp</strong></td>
                    <td class="text-right"><strong>{{ number_format($modalAkhir, 0, ',', '.') }}</strong></td>
                </tr>

                {{-- SALDO LABA --}}
                <tr class="table-active">
                    <td><strong>2</strong></td>
                    <td colspan="3"><strong>SALDO LABA</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Saldo Laba Awal</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($saldoLabaAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Laba (Rugi) periode berjalan</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($labaRugiPeriodeIni, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 20px;">Bagi Hasil Penyertaan:</td>
                    <td colspan="2"></td>
                </tr>
                 <tr>
                    <td></td>
                    <td style="padding-left: 40px;">Bagi Hasil Penyertaan Modal Desa</td>
                    <td>Rp</td>
                    <td class="text-right">({{ number_format($bagiHasilDesa, 0, ',', '.') }})</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 40px;">Bagi Hasil Penyertaan Modal Masyarakat</td>
                    <td>Rp</td>
                    <td class="text-right">({{ number_format($bagiHasilMasyarakat, 0, ',', '.') }})</td>
                </tr>
                <tr class="bg-light">
                    <td></td>
                    <td><strong>Saldo Laba Akhir</strong></td>
                    <td><strong>Rp</strong></td>
                    <td class="text-right"><strong>{{ number_format($saldoLabaAkhir, 0, ',', '.') }}</strong></td>
                </tr>

                {{-- MODAL DONASI --}}
                 <tr class="table-active">
                    <td><strong>3</strong></td>
                    <td colspan="3"><strong>MODAL DONASI/SUMBANGAN</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Modal Donasi/Sumbangan</td>
                    <td>Rp</td>
                    <td class="text-right">{{ number_format($modalDonasi, 0, ',', '.') }}</td>
                </tr>

                {{-- EKUITAS AKHIR --}}
                <tr class="table-active">
                    <td></td>
                    <td><strong>EKUITAS AKHIR</strong></td>
                    <td><strong>Rp</strong></td>
                    <td class="text-right"><strong>{{ number_format($ekuitasAkhir, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN --}}
        <div class="row mt-5">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p>CIANJUR, {{ $endDate->isoFormat('D MMMM Y') }}</p>
                <p>Direktur BUMDesa</p>
                <br><br><br>
                <p><strong>(............................)</strong></p>
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