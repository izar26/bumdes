@extends('adminlte::page')

@section('title', 'Laporan Buku Besar')

@section('content_header')
    {{-- Dibiarkan kosong agar header default tidak mengganggu --}}
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- KOP SURAT (Menggunakan struktur baru yang standar) --}}
        <div class="kop">
            @if($bumdes && $bumdes->logo)
                <img src="{{ public_path('storage/' . $bumdes->logo) }}" alt="Logo">
            @else
                {{-- Fallback jika logo tidak ada --}}
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
            <h3>Laporan Buku Besar</h3>
            <p>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</strong></p>
        </div>

        {{-- INFORMASI AKUN --}}
        <div class="info-akun">
            <table class="table-info-akun">
                <tr>
                    <td style="width: 120px;"><strong>Kode Akun</strong></td>
                    <td>: {{ $akun->kode_akun }}</td>
                </tr>
                <tr>
                    <td><strong>Nama Akun</strong></td>
                    <td>: {{ $akun->nama_akun }}</td>
                </tr>
            </table>
        </div>

        {{-- TABEL DATA (Menggunakan styling baru) --}}
        <table class="table-laporan">
            <thead>
                <tr>
                    <th style="width: 12%;">Tanggal</th>
                    <th>Keterangan</th>
                    <th style="width: 18%;">Debit</th>
                    <th style="width: 18%;">Kredit</th>
                    <th style="width: 18%;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                {{-- Saldo Awal --}}
                <tr class="saldo-row">
                    <td colspan="4"><strong>Saldo Awal</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . $saldoAwal }}</strong></td>
                </tr>

                @php $saldoBerjalan = $saldoAwal; @endphp

                @forelse ($transaksis as $transaksi)
                    @php
                        if ($akun->tipe_akun == 'Aset' || $akun->tipe_akun == 'Beban') {
                            $saldoBerjalan += ($transaksi->debit - $transaksi->kredit);
                        } else {
                            $saldoBerjalan += ($transaksi->kredit - $transaksi->debit);
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($transaksi->jurnal->tanggal_transaksi)->format('d/m/Y') }}</td>
                        <td>{{ $transaksi->jurnal->deskripsi }}</td>
                        <td class="text-right">{{ $transaksi->debit > 0 ? 'Rp ' . $transaksi->debit : '-' }}</td>
                        <td class="text-right">{{ $transaksi->kredit > 0 ? 'Rp ' . $transaksi->kredit : '-' }}</td>
                        <td class="text-right">{{ 'Rp ' . $saldoBerjalan }}</td>
                    </tr>
                @empty
                     <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse

                {{-- Saldo Akhir --}}
                <tr class="saldo-row">
                    <td colspan="4"><strong>Saldo Akhir</strong></td>
                    <td class="text-right"><strong>{{ 'Rp ' . $saldoBerjalan }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- TANDA TANGAN (Menggunakan struktur baru yang standar) --}}
        <table class="footer">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    {{ 'Cianjur' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                 <td>Menyetujui,</td>
                {{-- <td></td> --}}
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

        {{-- TOMBOL CETAK (non-print) --}}
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
    .info-akun { margin-bottom: 15px; }
    .table-info-akun, .table-info-akun td { border: none !important; padding: 2px !important; }

    /* Styling Tabel Laporan */
    .table-laporan { border-collapse: collapse; width: 100%; font-size: 12px; }
    .table-laporan th { background: #008080; color: white; border: 1px solid #006666; padding: 8px; text-align: center; }
    .table-laporan td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
    .table-laporan tr.saldo-row { background: #e6f2f2; font-weight: bold; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    /* Styling Footer Tanda Tangan */
    .footer { margin-top: 50px; width: 100%; border: none; }
    .footer td { border: none; padding: 5px; text-align: center; }
    .ttd-space { height: 70px; }
    .nama-terang { text-decoration: underline; font-weight: bold; }

    /* Aturan untuk Mencetak */
    @media print {
        /* Sembunyikan elemen AdminLTE */
        .main-sidebar, .main-header, .content-header, .no-print, .main-footer {
            display: none !important;
        }
        /* Atur agar konten mengisi seluruh halaman */
        .content-wrapper, .content, .card, .card-body {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        /* Pastikan warna background tercetak */
        .table-laporan th, .kop {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@stop
