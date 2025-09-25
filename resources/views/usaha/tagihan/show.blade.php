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
            page-break-inside: avoid;
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
            font-size: 8px;
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
                    <tr><td>Pelanggan ID</td><td>: 00015</td></tr>
                    <tr><td>Nama</td><td>: ASEP TASRIPIN</td></tr>
                    <tr><td>Alamat</td><td>: PABRIK 2</td></tr>
                    <tr><td>Periode</td><td>: Agt 2025</td></tr>
                    <tr><td>Dibayar di</td><td>: Sep 2025</td></tr>
                    <tr><td>Pemakaian</td><td>: 22.00 m³</td></tr>
                    <tr><td>Jumlah Bayar</td><td>: Rp. 82.820</td></tr>
                    <tr><td>Tunggakan</td><td>: Rp. 0</td></tr>
                    <tr><td class="total-merah">Total Harus Dibayar</td><td class="total-merah">: Rp. 82.820</td></tr>
                </table>
                <div class="signature compact-section no-margin">
                    Petugas Penagihan<br>
                    Bpk OBI
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
                        </div>
                    <div style="flex-grow: 1; text-align: center;">
                        <div class="judul-bagian no-margin">BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</div>
                    </div>
                    <div style="width: 40%; text-align: center;">
                        <div class="judul-bagian no-margin">RINCIAN PEMBAYARAN</div>
                        <br>
                        <div class="small-text">Pembayaran untuk Pemakaian</div>
                        <br>
                    </div>
                </div>
                <div class="flex-container compact-section no-margin">
                    <div style="width: 48%;">
                        <table>
                           <tr><td>Pelanggan ID</td><td>: 00015</td></tr>
                           <tr><td>Nama</td><td>: ASEP TASRIPIN</td></tr>
                           <tr><td>Alamat</td><td>: PABRIK 2</td></tr>
                           <tr><td>Periode</td><td>: Agt 2025</td></tr>
                           <tr><td>Dibayar di</td><td>: Sep 2025</td></tr>
                           <tr><td>Meter Akhir</td><td>: 780.00 m³</td></tr>
                           <tr><td>Meter Awal</td><td>: 758.00 m³</td></tr>
                           <tr><td>Pemakaian</td><td>: 22.00 m³</td></tr>
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
                               <tr>
                                   <td class="description">Pemakaian Blok 1 (0-5 m³)</td>
                                   <td class="rate">3.000</td>
                                   <td class="qty">5</td>
                                   <td class="amount currency-cell"><span>Rp.</span><span>15.000</span></td>
                               </tr>
                               <tr>
                                   <td class="description">Pemakaian Blok 2 (6-15 m³)</td>
                                   <td class="rate">3.350</td>
                                   <td class="qty">10</td>
                                   <td class="amount currency-cell"><span>Rp.</span><span>33.500</span></td>
                               </tr>
                               <tr>
                                   <td class="description">Pemakaian Blok 3 (16-25 m³)</td>
                                   <td class="rate">3.760</td>
                                   <td class="qty">7</td>
                                   <td class="amount currency-cell"><span>Rp.</span><span>26.320</span></td>
                               </tr>
                               <tr>
                                   <td class="description">Biaya Pemeliharaan</td>
                                   <td class="rate">4.500</td>
                                   <td class="qty">1</td>
                                   <td class="amount currency-cell"><span>Rp.</span><span>4.500</span></td>
                               </tr>
                               <tr>
                                   <td class="description">Biaya Administrasi</td>
                                   <td class="rate">3.500</td>
                                   <td class="qty">1</td>
                                   <td class="amount currency-cell"><span>Rp.</span><span>3.500</span></td>
                               </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex-container compact-section no-margin">
                    <div style="width: 48%;">
                        <div class="judul-bagian no-margin" style="text-align: left; margin-top: 3px;">September 2025</div>
                        <div class="small-text" style="line-height: 1; margin-top: 2px;">
                            Sindangraja 25 Sep 25<br>
                            Petugas Penagihan<br>
                            Bpk OBI
                        </div>
                    </div>
                    <div style="width: 52%;">
                        <div class="small-text" style="text-align: center; margin-top: 5px; font-weight: bold;">Tunggakan dan Denda</div>
                        <table class="rincian-table">
                            <tr>
                                <td class="description">Sisa Tunggakan Bulan Lalu</td>
                                <td class="rate"></td>
                                <td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>0</span></td>
                            </tr>
                            <tr>
                                <td class="description">Denda</td>
                                <td class="rate"></td>
                                <td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>0</span></td>
                            </tr>
                            <tr>
                                <td class="description" style="border-top: 1px dashed #333;"><b>Jumlah Tunggakan + Denda</b></td>
                                <td class="rate" style="border-top: 1px dashed #333;"></td>
                                <td class="qty" style="border-top: 1px dashed #333;"></td>
                                <td class="amount currency-cell" style="border-top: 1px dashed #333;"><b><span>Rp.</span><span>0</span></b></td>
                            </tr>
                        </table>
                        <div class="divider"></div>
                        <table class="rincian-table">
                            <tr>
                                <td class="description">Pembayaran Pemakaian Bln Ini</td>
                                <td class="rate"></td>
                                <td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>82.820</span></td>
                            </tr>
                            <tr>
                                <td class="description">Tunggakan s/d Bulan Ini</td>
                                <td class="rate"></td>
                                <td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>0</span></td>
                            </tr>
                            <tr class="total-merah">
                                <td class="description">Total yg Harus di Bayar</td>
                                <td class="rate"></td>
                                <td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>82.820</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>