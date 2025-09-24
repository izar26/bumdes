<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan Air</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1;
        }
        .struk-container {
            width: 800px;
            margin: 5px auto;
            padding: 4px;
            box-sizing: border-box;
            border: 1px solid #ccc;
        }
        .header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
            font-size: 10px;
            line-height: 1;
        }
        .content {
            display: flex;
            justify-content: space-between;
        }
        .arsip, .bukti {
            padding: 2px;
            box-sizing: border-box;
        }
        .arsip {
            width: 38%;
            border-right: 1px dashed #000;
        }
        .bukti {
            width: 62%;
            padding-left: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        td {
            padding: 0;
            vertical-align: top;
        }
        .judul-bagian {
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
            font-size: 11px;
        }
        .logo {
            text-align: center;
            height: 25px;
            margin: -2px 0 2px 0;
        }
        .logo img {
            height: 100%;
        }
        .total-merah {
            color: red;
            font-weight: bold;
        }

        .rincian-table td {
    font-size: 9px;
    padding: 0;
}
.rincian-table .description {
    width: 40%; /* Tambah lebar kolom deskripsi */
    text-align: left; /* Ratakan teks ke kiri */
}
.rincian-table .rate {
    width: 20%; /* Lebar harga satuan */
    text-align: right;
    padding-left: 4px;
}
.rincian-table .qty {
    width: 15%; /* Lebar kuantitas */
    text-align: center; /* Ratakan ke tengah */
    padding-left: 4px;
}
.rincian-table .amount {
    width: 25%; /* Tambah sedikit lebar untuk jumlah */
    text-align: right;
}
        .tunggakan-table {
            font-size: 9px;
        }
        .tunggakan-table td {
            padding: 0;
        }
        .tunggakan-table .description {
            width: 80%;
        }
        .tunggakan-table .amount {
            width: 20%;
            text-align: right;
        }
        .text-right {
            text-align: right;
        }
        .signature {
            line-height: 1;
            margin-top: 3px;
        }
        .compact-section {
            margin-bottom: 3px;
            font-size: 9px
        }
        .flex-container {
            display: flex;
            justify-content: space-between;
        }
        .divider {
            border-top: 1px dashed #ccc;
            margin: 2px 0;
        }
        .no-margin {
            margin: 0;
        }
        .small-text {
            font-size: 8px;
        }

        @media print {
            .tombol-cetak,
            .btn,
            .content-header,
            .d-print-none {
                display: none !important;
            }
            .struk-container {
                border: none;
                width: 100%;
                margin: 0;
                padding: 2px;
            }
            body {
                font-size: 9px;
                background: white;
                margin: 0;
                padding: 0;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            table td,
            table th {
                padding: 1px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="struk-container">
        <div class="content">
            <div class="arsip">
                <div class="header compact-section no-margin">
                    BADAN USAHA MILIK DESA<br>
                    UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA<br>
                    DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                </div>
                <br>
                <div class="judul-bagian compact-section no-margin">ARSIP BUKTI TAGIHAN AIR UNTUK PELANGGAN</div>
                <br>
                <table class="compact-section no-margin">
                    <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan_id ?? 'N/A' }}</td></tr>
                    <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                    <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                    <tr><td>Periode</td><td>: {{ Carbon\Carbon::parse($tagihan->periode_tagihan)->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Dibayar di</td><td>: {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Pemakaian</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                    <tr><td>Jumlah Bayar</td><td>: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                    <tr><td>Tunggakan</td><td>: Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                    <tr><td class="total-merah">Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                </table>

                <div class="signature compact-section no-margin">
                    Petugas Penagihan<br>
                    Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                </div>
            </div>

            <div class="bukti">
                <div class="header compact-section no-margin">
                    BADAN USAHA MILIK DESA UNIT SPAM KSM TIRTA SARANA SEJAHTERA<br>
                    DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                </div>
                <br>
                <div class="flex-container compact-section no-margin">
                    <div class="logo">
                        <img src="{{ asset('pam.jpeg')}}" alt="Logo Tirta Sarana Sejahtera">
                    </div>
                    <div style="flex-grow: 1; text-align: center;">
                        <div class="judul-bagian no-margin">BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</div>
                    </div>
                    <div style="width: 40%; text-align: center;">
                        <div class="judul-bagian no-margin">RINCIAN PEMBAYARAN</div>
                        <br>
                        <div class="small-text">Pembayaran untuk Pemakaian</div>
                    </div>
                </div>

                <div class="flex-container compact-section no-margin">
                    <div style="width: 48%;">
                        <table>
                            <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan->id ?? 'N/A' }}</td></tr>
                            <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                            <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                            <tr><td>Periode</td><td>: {{ Carbon\Carbon::parse($tagihan->periode_tagihan)->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Dibayar di</td><td>: {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Meter Akhir</td><td>: {{ $tagihan->meter_akhir }} m³</td></tr>
                            <tr><td>Meter Awal</td><td>: {{ $tagihan->meter_awal }} m³</td></tr>
                            <tr><td>Pemakaian</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        </table>
                    </div>
                    <div style="width: 52%;">
                        <table class="rincian-table">
                            <thead>
                                <tr>
                                    <th class="description">Deskripsi</th>
                                    <th class="rate">Harga Satuan</th>
                                    <th class="qty">Qty</th>
                                    <th class="amount">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tagihan->rincian as $rincian)
                                <tr>
                                    <td class="description">{{ $rincian->deskripsi }}</td>
                                    <td class="rate">{{ number_format($rincian->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="qty">{{ number_format($rincian->kuantitas, 0, ',', '.') }}</td>
                                    <td class="amount">Rp. {{ number_format($rincian->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex-container compact-section no-margin">
                    <div style="width: 48%;">
                        <div class="judul-bagian no-margin" style="text-align: left; margin-top: 3px;">{{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMMM Y') }}</div>
                        <div class="small-text" style="line-height: 1; margin-top: 2px;">
                            Sindangraja {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('DD MMM YY') }}<br>
                            Petugas Penagihan<br>
                            Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="width: 52%;">
                        <br>
                        <div class="small-text" style="text-align: center; margin-bottom: 1px;">Tunggakan dan Denda</div>
                        <br>
                        <table class="tunggakan-table">
                            <tr><td class="description">Sisa Tunggakan Bulan Lalu</td><td class="amount">Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                            <tr><td class="description">Denda</td><td class="amount">Rp. {{ number_format($tagihan->denda, 0, ',', '.') }}</td></tr>
                            <tr><td class="description"><b>Jumlah Tunggakan + Denda</b></td><td class="amount"><b>Rp. {{ number_format($tagihan->tunggakan + $tagihan->denda, 0, ',', '.') }}</b></td></tr>
                        </table>

                        <div class="divider"></div>

                        <table class="tunggakan-table">
                            <tr><td class="description">Pembayaran Pemakaian Bln Lalu</td><td class="amount">Rp. {{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</td></tr>
                            <tr><td class="description">Tunggakan sd Bulan Ini</td><td class="amount">Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                            <tr class="total-merah">
                                <td class="description">Total yg Harus di Bayar</td>
                                <td class="amount">Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
