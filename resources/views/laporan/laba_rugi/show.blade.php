@extends('adminlte::page')

@section('title', 'Laporan Laba Rugi')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <div class="kop">
            @if($bumdes && $bumdes->logo)
                <img src="{{ asset('storage/' . $bumdes->logo) }}" alt="Logo">
            @else
                <img src="https://placehold.co/75x75/008080/FFFFFF?text=Logo" alt="Logo">
            @endif
            <div class="kop-text">
                <h1>{{ $bumdes->nama_bumdes ?? 'BUMDes Anda' }}</h1>
                <h2>{{ $bumdes->alamat ?? 'Alamat BUMDes Anda' }}</h2>
                <p>Email: {{ $bumdes->email ?? '-' }} | Telp: {{ $bumdes->telepon ?? '-' }}</p>
            </div>
        </div>
        <div class="garis-pembatas"></div>

        <div class="judul">
            <h3>Laporan Laba Rugi</h3>
            <p>Untuk Periode yang Berakhir pada <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong></p>
        </div>

        <table class="table-laporan table-laba-rugi">
            <tbody>
                <tr class="header-row"><td colspan="2"><strong>Pendapatan</strong></td></tr>
                @forelse ($pendapatans as $pendapatan)
                    <tr>
                        <td class="item-name">{{ $pendapatan['nama_akun'] }}</td>
                        <td class="item-value">Rp {{ number_format($pendapatan['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada data pendapatan</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Total Pendapatan</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Beban Operasional</strong></td></tr>
                @forelse ($bebans as $beban)
                    <tr>
                        <td class="item-name">{{ $beban['nama_akun'] }}</td>
                        <td class="item-value">(Rp {{ number_format($beban['total'], 0, ',', '.') }})</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada data beban</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Total Beban Operasional</strong></td>
                    <td class="item-value"><strong>(Rp {{ number_format($totalBeban, 0, ',', '.') }})</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                @php
                    $labaRugiLabel = $labaRugi >= 0 ? 'Laba Bersih Operasional' : 'Rugi Bersih Operasional';
                @endphp
                <tr class="grand-total-row">
                    <td><strong>{{ $labaRugiLabel }}</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">{{ $bumdes->kota ?? 'Kota Anda' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Menyetujui,</td>
                <td></td>
            </tr>
            <tr>
                <td><strong>{{ $penandaTangan1['jabatan'] }}</strong></td>
                <td><strong>{{ $penandaTangan2['jabatan'] }}</strong></td>
            </tr>
            <tr class="ttd-space"><td></td><td></td></tr>
            <tr>
                <td class="nama-terang">({{ $penandaTangan1['nama'] }})</td>
                <td class="nama-terang">({{ $penandaTangan2['nama'] }})</td>
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
    /* Styling Laporan Standar */
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

    /* == PERUBAHAN DI SINI == */
    /* Styling Tabel Laporan Laba Rugi */
    .table-laporan { width: 100%; } /* Memastikan tabel memenuhi lebar kontainer */
    .table-laba-rugi, .table-laba-rugi td { border: none !important; }
    .table-laba-rugi .header-row td { font-size: 14px; padding-top: 15px; }
    .table-laba-rugi .item-name { padding-left: 30px !important; }
    .table-laba-rugi .item-value { text-align: right; width: 30%; } /* Memberi lebar tetap pada kolom nilai */
    .table-laba-rugi .total-row td { border-top: 1px solid #000 !important; padding-top: 5px; }
    .table-laba-rugi .grand-total-row td { border-top: 3px double #000 !important; font-size: 14px; padding-top: 8px; font-weight: bold; }
    .table-laba-rugi .spacer { border: none; padding: 10px; }
    /* == AKHIR PERUBAHAN == */

    /* Styling Footer Tanda Tangan */
    .footer { margin-top: 50px; width: 100%; border: none; }
    .footer td { border: none; padding: 5px; text-align: center; }
    .ttd-space { height: 70px; }
    .nama-terang { text-decoration: underline; font-weight: bold; }

    /* Aturan untuk Mencetak */
    @media print {
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer { display: none !important; }
        .content-wrapper, .content, .card, .card-body { margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
        .kop { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@stop
