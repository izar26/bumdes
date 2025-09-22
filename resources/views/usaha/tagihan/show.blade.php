<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Tagihan #{{ $tagihan->id }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1;
        }
        .tombol-cetak {
            display: block;
            width: 200px;
            margin: 10px auto;
            padding: 8px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        .struk-container {
            width: 700px;
            margin: 10px auto;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 3px;
            font-size: 13px;
            line-height: 1.1;
        }
        .content {
            display: flex;
            justify-content: space-between;
        }
        .arsip, .bukti {
            padding: 5px;
            box-sizing: border-box;
        }
        .arsip {
            width: 38%;
            border-right: 1px dashed #000;
        }
        .bukti {
            width: 62%;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        td {
            padding: 1px 0;
            vertical-align: top;
        }
        .judul-bagian {
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .logo {
            text-align: center;
            height: 25px;
            margin: -3px 0 3px 0;
        }
        .logo img {
            height: 100%;
        }
        .total-merah {
            color: red;
            font-weight: bold;
        }
        .rincian-table td {
            font-size: 8px;
            padding: 1px 0;
        }
        .rincian-table .description {
            width: 30%;
        }
        .rincian-table .rate {
            width: 15%;
            text-align: right;
            padding-right: 5px;
        }
        .rincian-table .qty {
            width: 10%;
            padding-left: 5px;
        }
        .rincian-table .currency {
            width: 5%;
        }
        .rincian-table .amount {
            width: 10%;
            text-align: right;
        }
        .tunggakan-table {
            font-size: 10px;
        }
        .tunggakan-table td {
            padding: 1px 0;
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
            margin-top: 5px;
        }
        .compact-section {
            margin-bottom: 5px;
        }
        .flex-container {
            display: flex;
            justify-content: space-between;
        }
        .divider {
            border-top: 1px dashed #ccc;
            margin: 5px 0;
        }
        @media print {
            .tombol-cetak, .btn, .content-header, .d-print-none {
                display: none !important;
            }
            .struk-container {
                border: none;
                width: 100%;
                margin: 0;
                padding: 5px;
                page-break-after: always;
            }
            .struk-container:last-child {
                page-break-after: avoid;
            }
            body {
                font-size: 9px;
                background: white;
                margin: 0;
                padding: 0;
            }
            body * {
                visibility: hidden;
            }
            .struk-container, .struk-container * {
                visibility: visible;
            }
            .struk-container {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <button class="tombol-cetak" onclick="window.print()">Cetak Struk</button>
                        <div class="struk-container">
                            <div class="content">
                                <div class="arsip">
                                    <div class="header compact-section">
                                        BADAN USAHA MILIK DESA<br>
                                        UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA<br>
                                        DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                                    </div>
                                    <div class="judul-bagian compact-section">ARSIP BUKTI TAGIHAN AIR UNTUK PELANGGAN</div>
                                    <table class="compact-section">
                                        <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan_id ?? 'N/A' }}</td></tr>
                                        <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                                        <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                                        <tr><td>Periode</td><td>: {{ Carbon\Carbon::parse($tagihan->periode_tagihan)->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                                        <tr><td>Dibayar di</td><td>: {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMM Y') }}</td></tr>
                                        <tr><td>Jumlah Bayar</td><td>: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                                        <tr><td>Tunggakan</td><td>: Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                                        <tr><td class="total-merah">Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                                    </table>
                                    <div class="signature compact-section">
                                        Petugas Penagihan<br>
                                        Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="bukti">
                                    <div class="header compact-section">
                                        BADAN USAHA MILIK DESA UNIT SPAM KSM TIRTA SARANA SEJAHTERA<br>
                                        DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                                    </div>
                                    <div class="flex-container compact-section">
                                        <div class="logo">
                                          <img src="{{ asset('pam.jpeg')}}" alt="Logo Tirta Sarana Sejahtera">
                                        </div>
                                        <div style="flex-grow: 1; text-align: center;">
                                            <div class="judul-bagian">BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</div>
                                        </div>
                                        <div style="width: 40%; text-align: center;">
                                            <div class="judul-bagian">RINCIAN PEMBAYARAN</div>
                                            <div style="font-size: 11px;">Pembayaran bulan ini</div>
                                        </div>
                                    </div>
                                    <div class="flex-container compact-section">
                                        <div style="width: 48%;">
                                            <table>
                                                <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan_id ?? 'N/A' }}</td></tr>
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
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Deskripsi</th>
                                                        <th>Harga Satuan</th>
                                                        <th>Qty</th>
                                                        <th>Jumlah</th>
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
                                    <div class="flex-container compact-section">
                                        <div style="width: 48%;">
                                            <div class="judul-bagian" style="text-align: left; margin-top: 3px;">{{ \Carbon\Carbon::parse($tagihan->tanggal_cetak)->locale('id')->isoFormat('MMMM Y') }}</div>
                                            <div style="font-size: 11px; line-height: 1; margin-top: 3px;">
                                                Sindangraja {{ \Carbon\Carbon::parse($tagihan->tanggal_cetak)->locale('id')->isoFormat('DD MMM YY') }}<br>
                                                Petugas Penagihan<br>
                                                Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div style="width: 52%;">
                                            <div style="text-align: center; font-size: 11px; margin-bottom: 3px;">Tunggakan dan Denda</div>
                                            <table class="tunggakan-table">
                                                <tr><td class="description">Sisa Tunggakan Bulan Lalu</td><td class="amount">Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                                                <tr><td class="description">Denda</td><td class="amount">Rp. {{ number_format($tagihan->denda, 0, ',', '.') }}</td></tr>
                                                <tr><td class="description"><b>Jumlah Tunggakan + Denda</b></td><td class="amount"><b>Rp. {{ number_format($tagihan->tunggakan + $tagihan->denda, 0, ',', '.') }}</b></td></tr>
                                            </table>
                                            <div class="divider"></div>
                                            <table class="tunggakan-table">
                                                <tr><td class="description">Pembayaran Bulan Ini</td><td class="amount">Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
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
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
