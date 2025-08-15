@extends('adminlte::page')

@section('title', 'Laporan Posisi Keuangan (Neraca)')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT --}}
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

        {{-- JUDUL LAPORAN --}}
        <div class="judul">
            <h3>Laporan Posisi Keuangan (Neraca)</h3>
            <p>Per Tanggal: <strong>{{ \Carbon\Carbon::parse($reportDate)->format('d F Y') }}</strong></p>
        </div>

        {{-- TABEL DATA (Format Staffel - Atas Bawah) --}}
        <table class="table-laporan table-neraca">
            <tbody>
                {{-- ASET --}}
                <tr class="header-section">
                    <td colspan="2"><strong>ASET</strong></td>
                </tr>
                @forelse ($asets as $aset)
                    <tr>
                        <td class="item-name">{{ $aset['nama_akun'] }}</td>
                        <td class="item-value">Rp {{ number_format($aset['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada data aset</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-section">
                    <td><strong>TOTAL ASET</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($totalAset, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer"></td></tr>

                {{-- KEWAJIBAN --}}
                <tr class="header-section">
                    <td colspan="2"><strong>KEWAJIBAN</strong></td>
                </tr>
                @forelse ($kewajibans as $kewajiban)
                    <tr>
                        <td class="item-name">{{ $kewajiban['nama_akun'] }}</td>
                        <td class="item-value">Rp {{ number_format($kewajiban['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="item-name">Tidak ada data kewajiban</td>
                        <td class="item-value">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td><strong>Total Kewajiban</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer-small"></td></tr>

                {{-- EKUITAS --}}
                <tr class="header-section">
                    <td colspan="2"><strong>EKUITAS</strong></td>
                </tr>
                @foreach ($ekuitas as $modal)
                    <tr>
                        <td class="item-name">{{ $modal['nama_akun'] }}</td>
                        <td class="item-value">Rp {{ number_format($modal['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if ($labaDitahan != 0)
                    <tr>
                        <td class="item-name">Laba (Rugi) Ditahan</td>
                        <td class="item-value">Rp {{ number_format($labaDitahan, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td><strong>Total Ekuitas</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" class="spacer-small"></td></tr>

                {{-- TOTAL KEWAJIBAN + EKUITAS --}}
                <tr class="total-section">
                    <td><strong>TOTAL KEWAJIBAN DAN EKUITAS</strong></td>
                    <td class="item-value"><strong>Rp {{ number_format($totalKewajiban + $totalEkuitas, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN --}}
        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    {{ $bumdes->kota ?? 'Kota Anda' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>Menyetujui,</td>
            </tr>
            <tr>
                <td>
                    <strong>{{ $penandaTangan1['jabatan'] ?? 'Direktur' }}</strong>
                </td>
                <td>
                    <strong>{{ $penandaTangan2['jabatan'] ?? 'Bendahara' }}</strong>
                </td>
            </tr>
            <tr class="ttd-space">
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="nama-terang">
                    ({{ $penandaTangan1['nama'] ?? '.........................................' }})
                </td>
                <td class="nama-terang">
                    ({{ $penandaTangan2['nama'] ?? '.........................................' }})
                </td>
            </tr>
        </table>

        {{-- TOMBOL CETAK --}}
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
    
    /* Styling Tabel Laporan Neraca */
    .table-laporan { width: 100%; }
    .table-neraca, .table-neraca td { border: none !important; }
    .table-neraca .header-section td { font-size: 14px; text-transform: uppercase; padding-top: 15px; border-bottom: 1px solid #333 !important; }
    .table-neraca .item-name { padding-left: 30px !important; }
    .table-neraca .item-value { text-align: right; width: 30%; }
    .table-neraca .total-row td { border-top: 1px solid #ccc !important; padding-top: 5px; }
    .table-neraca .total-section td { border-top: 3px double #000 !important; font-size: 14px; padding-top: 8px; font-weight: bold; }
    .table-neraca .spacer { border: none; padding: 15px; }
    .table-neraca .spacer-small { border: none; padding: 5px; }

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
