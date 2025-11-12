<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan Air</title>
    <style>
        /* CSS INI SAMA PERSIS DENGAN VERSI CETAK MASSAL UNTUK KONSISTENSI */
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
            page-break-inside: avoid; /* Menjaga agar struk tidak terpotong di tengah halaman */
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

        /* --- Print Styles --- */
        @media print {
            .tombol-cetak, .btn, .content-header, .d-print-none {
                display: none !important;
            }
            .struk-container {
                border: none;
                width: 100%;
                margin: 0 0 30px 0; /* Memberi jarak bawah antar struk saat print */
                padding: 2px;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    {{-- Diasumsikan controller mengirimkan satu objek $tagihan --}}
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
                <table class="compact-section no-margin">
                    <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan_id ?? 'N/A' }}</td></tr>
                    <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama ?? 'N/A' }}</td></tr>
                    <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat ?? 'N/A' }}</td></tr>
                    <tr><td>Periode</td><td>: {{ \Carbon\Carbon::parse($tagihan->periode_tagihan)->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Dibayar di</td><td>: {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMM Y') }}</td></tr>
                    <tr><td>Pemakaian</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                    <tr><td>Jumlah Bayar</td><td>: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                    <tr><td>Tunggakan</td><td>: Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                    <tr><td class="total-merah">Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                </table>

                <div class="signature">
                    Petugas Penagihan<br>
                    Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                    Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                </div>
            </div>

            <div class="bukti">
                <div class="header compact-section no-margin">
                    BADAN USAHA MILIK DESA UNIT SPAM KSM TIRTA SARANA SEJAHTERA<br>
                    DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR
                </div>
                <br>
                <div class="flex-container">
                    <div class="logo">
                        {{-- Ganti dengan path logo yang benar jika ada --}}
                        {{-- <img src="{{ asset('pam.jpeg')}}" alt="Logo"> --}}
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

                <div class="flex-container compact-section no-margin">
                    <div style="width: 48%;">
                        <table>
                            <tr><td>Pelanggan ID</td><td>: 000{{ $tagihan->pelanggan->id ?? 'N/A' }}</td></tr>
                            <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama ?? 'N/A' }}</td></tr>
                            <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat ?? 'N/A' }}</td></tr>
                            <tr><td>Periode</td><td>: {{ \Carbon\Carbon::parse($tagihan->periode_tagihan)->subMonth()->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Dibayar di</td><td>: {{ $tagihan->tanggal_cetak->locale('id')->isoFormat('MMM Y') }}</td></tr>
                            <tr><td>Meter Akhir</td><td>: {{ number_format($tagihan->meter_akhir, 2, ',', '.') }} m³</td></tr>
                            <tr><td>Meter Awal</td><td>: {{ number_format($tagihan->meter_awal, 2, ',', '.') }} m³</td></tr>
                            <tr><td>Pemakaian</td><td>: {{ number_format($tagihan->total_pemakaian_m3, 2, ',', '.') }} m³</td></tr>
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
                                    <td class="description">{{ $rincian->deskripsi }}</td>
                                    <td class="rate">{{ number_format($rincian->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="qty">{{ number_format($rincian->kuantitas, 0, ',', '.') }}</td>
                                    <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($rincian->subtotal, 0, ',', '.') }}</span></td>
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
                            Bpk {{ $tagihan->petugas->nama_petugas ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="width: 52%;">
                        <div class="small-text" style="text-align: center; margin-top: 5px; font-weight: bold;">Tunggakan dan Denda</div>
                        <table class="rincian-table">
                            <tr>
                                <td class="description">Sisa Tunggakan Bulan Lalu</td>
                                <td class="rate"></td><td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($tagihan->tunggakan, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td class="description">Denda</td>
                                <td class="rate"></td><td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($tagihan->denda, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td class="description" style="border-top: 1px dashed #333;"><b>Jumlah Tunggakan + Denda</b></td>
                                <td class="rate" style="border-top: 1px dashed #333;"></td><td class="qty" style="border-top: 1px dashed #333;"></td>
                                <td class="amount currency-cell" style="border-top: 1px dashed #333;"><b><span>Rp.</span><span>{{ number_format($tagihan->tunggakan + $tagihan->denda, 0, ',', '.') }}</span></b></td>
                            </tr>
                        </table>

                        <div class="divider"></div>
                        <table class="rincian-table">
                            <tr>
                                <td class="description">Pembayaran Pemakaian Bln Ini</td>
                                <td class="rate"></td><td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td class="description">Tunggakan s/d Bulan Ini</td>
                                <td class="rate"></td><td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($tagihan->tunggakan, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr class="total-merah">
                                <td class="description">Total yg Harus di Bayar</td>
                                <td class="rate"></td><td class="qty"></td>
                                <td class="amount currency-cell"><span>Rp.</span><span>{{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>