<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan Air</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
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
            page-break-inside: avoid;
        }
        .header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
            font-size: 11px;
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
            font-size: 10px;
        }
        td {
            padding: 0;
            vertical-align: top;
        }
        .judul-bagian {
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
            font-size: 10px;
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
        .rincian-table .description {
            width: 40%;
            text-align: left;
        }
        .rincian-table .rate {
            width: 20%;
            text-align: right;
            padding-left: 4px;
        }
        .rincian-table .qty {
            width: 15%;
            text-align: center;
            padding-left: 4px;
        }
        .rincian-table .amount {
            width: 25%;
            text-align: right;
        }
        .currency-cell {
            display: flex;
            justify-content: space-between;
            white-space: nowrap;
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
            font-size: 9px;
        }

        /* --- KESALAHAN ADA DI SINI SEBELUMNYA --- */
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
                margin: 0; /* Baris ini direset, jadi kita perlu tambahkan margin-bottom di sini */
                padding: 2px;
                margin-bottom: 30px; /* <-- PERBAIKAN DITERAPKAN DI SINI */
            }
            body {
                font-size: 10px;
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
            <!-- Arsip -->
            <div class="arsip">
                <div class="header compact-section no-margin">
                    BADAN USAHA MILIK DESA<br>
                    UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA<br>
                    DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                </div>
                <br>
                <div class="judul-bagian compact-section no-margin">ARSIP BUKTI TAGIHAN AIR UNTUK PELANGGAN</div>
                <br>
                <table>
                    <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan_id }}</td></tr>
                    <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                    <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                    <tr><td>Periode</td><td>: {{ Carbon\Carbon::now()->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Dibayar di</td><td>: {{ Carbon\Carbon::now()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Pemakaian</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                    <tr><td>Jumlah Bayar</td><td>: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                    <tr><td>Tunggakan</td><td>: Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                    <tr><td class="total-merah">Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                </table>

                <div class="signature">
                    Petugas Penagihan<br>
                    Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                </div>
            </div>

            <!-- Bukti -->
            <div class="bukti">
                <div class="header compact-section no-margin">
                    BADAN USAHA MILIK DESA UNIT SPAM KSM TIRTA SARANA SEJAHTERA<br>
                    DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                </div>
                <br>
                <div class="flex-container">
                    <div class="logo">
                        <img src="{{ asset('pam.jpeg')}}" alt="Logo Tirta Sarana Sejahtera">
                    </div>
                    <div style="flex-grow: 1; text-align: center;">
                        <div class="judul-bagian">BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</div>
                    </div>
                    <div style="width: 40%; text-align: center;">
                        <div class="judul-bagian">RINCIAN PEMBAYARAN</div>
                        <br>
                        <div class="small-text">Pembayaran untuk Pemakaian</div>
                        <br>
                    </div>
                </div>

                <div class="flex-container">
                    <div style="width: 48%;">
                        <table>
                            <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan->id }}</td></tr>
                            <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                            <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                            <tr><td>Periode</td><td>: {{ Carbon\Carbon::now()->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Dibayar di</td><td>: {{ Carbon\Carbon::now()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Meter Akhir</td><td>: {{ $tagihan->meter_akhir }} m³</td></tr>
                            <tr><td>Meter Awal</td><td>: {{ $tagihan->meter_awal }} m³</td></tr>
                            <tr><td>Pemakaian</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        </table>
                    </div>
                    <div style="width: 52%;">
                        <table class="rincian-table">
                            <thead>
                                <tr>
                                    <th>Deskripsi</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tagihan->rincian as $rincian)
                                <tr>
                                    <td>{{ $rincian->deskripsi }}</td>
                                    <td>{{ number_format($rincian->harga_satuan, 0, ',', '.') }}</td>
                                    <td>{{ number_format($rincian->kuantitas, 0, ',', '.') }}</td>
                                    <td>Rp. {{ number_format($rincian->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex-container">
                    <div style="width: 48%;">
                        <div class="judul-bagian" style="text-align: left;">{{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMMM Y') }}</div>
                        <div class="small-text">
                            Sindangraja {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('DD MMM YY') }}<br>
                            Petugas Penagihan<br>
                            Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="width: 52%;">
                        <div class="small-text" style="text-align: center;">Tunggakan dan Denda</div>
                        <table class="tunggakan-table">
                            <tr><td>Sisa Tunggakan Bulan Lalu</td><td class="amount">Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                            <tr><td>Denda</td><td class="amount">Rp. {{ number_format($tagihan->denda, 0, ',', '.') }}</td></tr>
                            <tr><td><b>Jumlah Tunggakan + Denda</b></td><td class="amount"><b>Rp. {{ number_format($tagihan->tunggakan + $tagihan->denda, 0, ',', '.') }}</b></td></tr>
                        </table>

                        <div class="divider"></div>

                        <table class="tunggakan-table">
                            <tr><td>Pembayaran Pemakaian Bln Lalu</td><td class="amount">Rp. {{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</td></tr>
                            <tr><td>Tunggakan sd Bulan Ini</td><td class="amount">Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                            <tr class="total-merah">
                                <td>Total yg Harus di Bayar</td>
                                <td>Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
