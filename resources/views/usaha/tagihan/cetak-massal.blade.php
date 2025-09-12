<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan Massal</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; }
        .tombol-cetak { display: block; width: 200px; margin: 20px auto; padding: 10px; background-color: #4CAF50; color: white; text-align: center; border: none; cursor: pointer; font-size: 16px; font-family: Arial, sans-serif; }
        .struk-container { width: 800px; background-color: #fff; padding: 15px; margin: 20px auto; border: 1px solid #ccc; }

        @media print {
            body { background-color: #fff; }
            .tombol-cetak { display: none; }
            .struk-container {
                width: 100%;
                margin: 0 0 20px 0;
                padding: 0;
                border: none;
                page-break-after: avoid; /* Menghindari ganti halaman jika tidak perlu */
                border-bottom: 1px dashed #ccc;
                padding-bottom: 20px;
            }
            .struk-container:last-child {
                border-bottom: none;
                page-break-after: avoid;
            }
        }

        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .header p { margin: 2px 0; font-weight: normal; }
        .content { display: flex; justify-content: space-between; }
        .arsip { width: 35%; position: relative; border-right: 1px dashed #000; padding-right: 15px; }
        .arsip::after { content: 'X'; position: absolute; top: 5%; left: 0; right: 0; bottom: 5%; font-size: 150px; color: rgba(0, 0, 0, 0.15); display: flex; align-items: center; justify-content: center; font-weight: lighter; z-index: 1;}
        .bukti { width: 65%; padding-left: 15px; display: flex; }
        .bukti-kiri { width: 50%; }
        .bukti-kanan { width: 50%; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 0.5px 2px; vertical-align: top; }
        .info-table td:first-child { width: 50%; white-space: nowrap; }
        .rincian-table td { padding: 0.5px 3px; }
        .total-merah { color: red; font-weight: bold; }
        .judul-bagian { font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .logo { font-weight: bold; border: 1px solid #000; padding: 5px; display: inline-block; margin-bottom: 5px; font-size: 9px; }
    </style>
</head>
<body>
    <button class="tombol-cetak" onclick="window.print()">Cetak Semua Tagihan</button>

    @forelse ($semua_tagihan as $tagihan)
        <div class="struk-container">
            <div class="header">
                <p>BADAN USAHA MILIK DESA</p>
                <p>UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA</p>
                <p>DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR</p>
            </div>
            <div class="content">
                {{-- BAGIAN KIRI (ARSIP) --}}
                <div class="arsip">
                    <div class="judul-bagian">ARSIP BUKTI TAGIHAN UNTUK PELANGGAN</div>
                    <table class="info-table">
                        <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                        <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                        <tr><td>Penggunaan Air di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->isoFormat('MMMM YYYY')) }}</td></tr>
                        <tr><td>Dibayarkan di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->addMonth()->isoFormat('MMMM YYYY')) }}</td></tr>
                        <tr><td>Jumlah Air Yang di Pakai</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        <tr><td>Jumlah Air Yg Harus Dibayarkan</td><td>: Rp. {{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</td></tr>
                        <tr><td>Jumlah Tunggakan sd Bulan Ini</td><td>: Rp. {{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                        <tr><td class="total-merah">Jumlah Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                    </table>
                    <br><br>
                    <p>Petugas Penagihan<br>{{ $tagihan->petugas->nama_petugas ?? 'N/A' }}</p>
                </div>

                {{-- BAGIAN KANAN (BUKTI PEMBAYARAN & RINCIAN) --}}
                <div class="bukti">
                    <div class="bukti-kiri">
                        <div class="logo">TIRTA SARANA<br>SEJAHTERA</div>
                        <div class="judul-bagian">BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</div>
                         <table class="info-table">
                            <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                            <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                            <tr><td>Penggunaan Air di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->isoFormat('MMMM YYYY')) }}</td></tr>
                            <tr><td>Dibayarkan di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->addMonth()->isoFormat('MMMM YYYY')) }}</td></tr>
                            <tr><td>Jml Angka Meter Akhir</td><td>: {{ number_format($tagihan->meter_akhir, 0, ',', '.') }} m³</td></tr>
                            <tr><td>Jml Angka Meter Awal</td><td>: {{ number_format($tagihan->meter_awal, 0, ',', '.') }} m³</td></tr>
                            <tr><td>Jml Pemakaian Air</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        </table>
                        <br>
                        <p>{{ $tagihan->tanggal_cetak ? 'Sindangraja ' . $tagihan->tanggal_cetak->isoFormat('DD MMMM YYYY') : '' }}<br>Petugas Penagihan<br>{{ $tagihan->petugas->nama_petugas ?? 'N/A' }}</p>
                    </div>
                    <div class="bukti-kanan">
                        <div class="judul-bagian">RINCIAN PEMBAYARAN AIR</div>
                        <p>Pembayaran bulan ini</p>
                        <table class="rincian-table">
                            @php
                                $rincian_pemakaian = $tagihan->rincian->where('jenis_tarif', 'pemakaian')->sortBy('batas_bawah');
                                $rincian_biaya_tetap = $tagihan->rincian->where('jenis_tarif', 'biaya_tetap')->sortBy('deskripsi');
                                $counter = 1;
                            @endphp
                            @foreach ($rincian_pemakaian as $item)
                                <tr>
                                    <td>{{ $counter++ }}. {{ explode('(', $item->deskripsi)[0] }}</td>
                                    <td style="text-align:right;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td style="text-align:right;">{{ $item->kuantitas }} :</td>
                                    <td>Rp.</td>
                                    <td style="text-align:right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @foreach ($rincian_biaya_tetap as $item)
                                <tr>
                                    <td>{{ $counter++ }}. {{ $item->deskripsi }}</td>
                                    <td style="text-align:right;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td></td>
                                    <td>Rp.</td>
                                    <td style="text-align:right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3" style="text-align:left;"><b>Jml</b></td>
                                <td><b>Rp.</b></td>
                                <td style="text-align:right;"><b>{{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</b></td>
                            </tr>
                        </table>
                        <br>
                        <p>Tunggakan dan Denda</p>
                        <table class="rincian-table">
                             <tr><td>1. Sisa Tunggakan Bulan Lalu</td><td>: Rp.</td><td style="text-align:right;">{{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                             <tr><td>2. Denda</td><td>: Rp.</td><td style="text-align:right;">{{ number_format($tagihan->denda, 0, ',', '.') }}</td></tr>
                             <tr><td><b>Jumlah Tunggakan + Denda</b></td><td><b>: Rp.</b></td><td style="text-align:right;"><b>{{ number_format($tagihan->tunggakan + $tagihan->denda, 0, ',', '.') }}</b></td></tr>
                        </table>
                        <table class="rincian-table" style="margin-top:5px;">
                             <tr><td>1. Jml Pembayaran Bulan Ini</td><td>: Rp.</td><td style="text-align:right;">{{ number_format($tagihan->subtotal_pemakaian + $tagihan->biaya_lainnya, 0, ',', '.') }}</td></tr>
                             <tr><td>2. Tunggakan s/d Bulan Ini</td><td>: Rp.</td><td style="text-align:right;">{{ number_format($tagihan->tunggakan, 0, ',', '.') }}</td></tr>
                             <tr><td class="total-merah"><b>Jml Total yg Harus di Bayar</b></td><td class="total-merah"><b>: Rp.</b></td><td class="total-merah" style="text-align:right;"><b>{{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</b></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="struk-container" style="text-align: center; font-family: Arial, sans-serif;">
            <h2>Tidak Ada Data</h2>
            <p>Tidak ada tagihan yang ditemukan untuk dicetak.</p>
        </div>
    @endforelse
</body>
</html>
