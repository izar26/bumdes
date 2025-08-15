@extends('adminlte::page')

@section('title', 'Laporan Perubahan Ekuitas')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop

@section('content')
<div class="card">
    <div class="card-body">

        <div class="kop">
            @if(optional($bumdes)->logo)
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo">
            @else
                <img src="https://placehold.co/75x75/008080/FFFFFF?text=Logo" alt="Logo">
            @endif
            <div class="kop-text">
                <h1>{{ optional($bumdes)->nama_bumdes ?? 'BUMDes Anda' }}</h1>
                <h2>{{ optional($bumdes)->alamat ?? 'Alamat BUMDes Anda' }}</h2>
                <p>Email: {{ optional($bumdes)->email ?? '-' }} | Telp: {{ optional($bumdes)->telepon ?? '-' }}</p>
            </div>
        </div>
        <div class="garis-pembatas"></div>

        <div class="judul">
            <h3>Laporan Perubahan Ekuitas</h3>
            <p>Untuk Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</strong></p>
        </div>

        <table class="table-laporan table-laba-rugi">
            <tbody>
                <tr class="header-row"><td colspan="2"><strong>Modal Awal</strong></td></tr>
                <tr>
                    <td class="item-name">Modal Desa</td>
                    <td class="item-value">Rp {{ number_format($modalDesaAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="item-name">Modal Masyarakat</td>
                    <td class="item-value">Rp {{ number_format($modalMasyarakatAwal, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Modal Disetor Awal</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($modalDesaAwal + $modalMasyarakatAwal, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Perubahan Modal</strong></td></tr>
                <tr>
                    <td class="item-name">Penambahan Modal Desa</td>
                    <td class="item-value">Rp {{ number_format($penambahanModalDesa, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="item-name">Penambahan Modal Masyarakat</td>
                    <td class="item-value">Rp {{ number_format($penambahanModalMasyarakat, 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Saldo Laba</strong></td></tr>
                <tr>
                    <td class="item-name">Saldo Laba Awal</td>
                    <td class="item-value">Rp {{ number_format($saldoLabaAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    @php $labaRugiLabel = $labaRugiPeriodeIni >= 0 ? 'Laba Bersih' : 'Rugi Bersih'; @endphp
                    <td class="item-name">{{ $labaRugiLabel }} Periode Ini</td>
                    <td class="item-value">Rp {{ number_format($labaRugiPeriodeIni, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    @php $bagiHasilDesaLabel = $bagiHasilDesa >= 0 ? 'Penambahan Bagi Hasil Desa' : 'Pengurangan Bagi Hasil Desa'; @endphp
                    <td class="item-name">{{ $bagiHasilDesaLabel }}</td>
                    <td class="item-value">Rp {{ number_format($bagiHasilDesa, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    @php $bagiHasilMasyarakatLabel = $bagiHasilMasyarakat >= 0 ? 'Penambahan Bagi Hasil Masyarakat' : 'Pengurangan Bagi Hasil Masyarakat'; @endphp
                    <td class="item-name">{{ $bagiHasilMasyarakatLabel }}</td>
                    <td class="item-value">Rp {{ number_format($bagiHasilMasyarakat, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Saldo Laba Akhir</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($saldoLabaAkhir, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Modal Donasi</strong></td></tr>
                <tr>
                    <td class="item-name">Modal Donasi/Sumbangan</td>
                    <td class="item-value">Rp {{ number_format($modalDonasiAkhir, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Modal Donasi Akhir</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($modalDonasiAkhir, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                <tr class="grand-total-row">
                    <td><strong>Total Ekuitas Akhir</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($ekuitasAkhir, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    {{ optional($bumdes)->kota ?? 'Kota Anda' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>Menyetujui,</td>
                <td></td>
            </tr>
            <tr>
                <td><strong>Direktur</strong></td>
                <td><strong>Bendahara</strong></td>
            </tr>
            <tr class="ttd-space">
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="nama-terang">(.........................................)</td>
                <td class="nama-terang">(.........................................)</td>
            </tr>
        </table>

        <div class="mt-4 text-right no-print">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .kop { display: flex; align-items: center; }
    .kop img { height: 75px; margin-right: 15px; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h1 { margin: 0; font-size: 22px; text-transform: uppercase; color: #006666; }
    .kop-text h2 { margin: 2px 0 0; font-size: 14px; font-weight: normal; color: #333; }
    .kop-text p { margin: 2px 0; font-size: 12px; }
    .garis-pembatas { border-top: 3px solid #000; border-bottom: 1px solid #000; height: 4px; margin-top: 5px; margin-bottom: 20px; }
    .judul { text-align: center; margin-bottom: 20px; }
    .judul h3 { margin: 5px 0; font-size: 16px; text-transform: uppercase; }
    .judul p { margin: 2px 0; }
    .table-laporan { width: 100%; }
    .table-laba-rugi, .table-laba-rugi td { border: none !important; }
    .table-laba-rugi .header-row td { font-size: 14px; padding-top: 15px; }
    .table-laba-rugi .item-name { padding-left: 30px !important; }
    .table-laba-rugi .item-value { text-align: right; width: 30%; }
    .table-laba-rugi .total-row td { border-top: 1px solid #000 !important; padding-top: 5px; }
    .table-laba-rugi .grand-total-row td { border-top: 3px double #000 !important; font-size: 14px; padding-top: 8px; font-weight: bold; }
    .table-laba-rugi .spacer { border: none; padding: 10px; }
    .footer { margin-top: 50px; width: 100%; border: none; }
    .footer td { border: none; padding: 5px; text-align: center; }
    .ttd-space { height: 70px; }
    .nama-terang { text-decoration: underline; font-weight: bold; }
    @media print {
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer { display: none !important; }
        .content-wrapper, .content, .card, .card-body { margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
        .table-laporan th, .kop, .badge-balance { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@stop
