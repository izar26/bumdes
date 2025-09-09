<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan Massal</title>
    <style>
        /* Gaya umum untuk tampilan di layar */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px; /* Ukuran font sedikit dikecilkan agar pas */
            background-color: #f4f4f4;
            margin: 0;
            color: #000;
        }
        .tombol-cetak {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }
        .struk-container {
            width: 800px;
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border: 1px solid #ccc;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        /* --- CSS KHUSUS UNTUK PRINT --- */
        @media print {
            body { background-color: #fff; }
            .tombol-cetak { display: none; }
            .struk-container {
                width: 100%;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                page-break-after: always; /* INI KUNCINYA: setiap struk mulai di halaman baru */
            }
        }

        /* --- Gaya Spesifik Struk --- */
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .header h4, .header p { margin: 2px 0; font-weight: normal; }
        .content { display: flex; justify-content: space-between; }
        .arsip { width: 35%; position: relative; border-right: 1px dashed #000; padding-right: 15px; }
        .arsip::after { content: 'X'; position: absolute; top: 5%; left: 0; right: 0; bottom: 5%; font-size: 150px; color: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; font-weight: lighter; z-index: 1;}
        .bukti { width: 65%; padding-left: 15px; display: flex; }
        .bukti-kiri { width: 50%; }
        .bukti-kanan { width: 50%; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 2px; vertical-align: top; }
        .info-table td:first-child { width: 50%; white-space: nowrap; }
        .rincian-table td:nth-child(2), .rincian-table td:nth-child(3), .rincian-table td:nth-child(4) { text-align: right; }
        .total-merah { color: red; font-weight: bold; }
        .judul-bagian { font-weight: bold; text-decoration: underline; margin-bottom: 8px; }
        .logo { font-weight: bold; border: 1px solid #000; padding: 5px; display: inline-block; margin-bottom: 5px; font-size: 9px; }
    </style>
</head>
<body>
    {{-- Tombol ini hanya akan tampil di browser, tidak akan ikut tercetak --}}
    <button class="tombol-cetak" onclick="window.print()">Cetak Semua Tagihan</button>

    {{--
        Loop @forelse akan mengulang untuk setiap tagihan yang ditemukan.
        Jika tidak ada tagihan sama sekali, bagian @empty akan dijalankan.
    --}}
    @forelse ($semua_tagihan as $tagihan)
        <div class="struk-container">
            <div class="content">
                <div class="arsip">
                    <div class="header">
                        <h4>BADAN USAHA MILIK DESA</h4>
                        <p>UNIT SPAM DESA KSM TIRTA SARANA SEJAHTERA</p>
                        <p>DESA SINDANGRAJA KEC. SUKALUYU KAB. CIANJUR</p>
                    </div>
                    <div class="judul-bagian">ARSIP BUKTI TAGIHAN AIR UNTUK PELANGGAN</div>
                    <table class="info-table">
                        <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                        <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                        <tr><td>Penggunaan Air di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->isoFormat('MMMM YYYY')) }}</td></tr>
                        <tr><td>Dibayarkan di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->addMonth()->isoFormat('MMMM YYYY')) }}</td></tr>
                        <tr><td>Jumlah Air yang di Pakai</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        <tr><td>Jumlah Air Yg Harus Dibayarkan</td><td>: Rp. {{ number_format($tagihan->rincian->sum('subtotal'), 0, ',', '.') }}</td></tr>
                        <tr><td>Jumlah Tunggakan sd Bulan Ini</td><td>: Rp. 0</td></tr>
                        <tr><td class="total-merah">Jumlah Total Harus Dibayar</td><td class="total-merah">: Rp. {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                    </table>
                    <br>
                    <p>Petugas Penagihan<br>{{ $tagihan->petugas->nama_petugas ?? 'N/A' }}</p>
                </div>

                <div class="bukti">
                    <div class="bukti-kiri">
                        <div class="header">
                            <div class="logo">TIRTA SARANA<br>SEJAHTERA</div>
                            <h4>BUKTI PEMBAYARAN TAGIHAN AIR BERSIH</h4>
                        </div>
                        <table class="info-table">
                            <tr><td>Nama</td><td>: {{ $tagihan->pelanggan->nama }}</td></tr>
                            <tr><td>Alamat</td><td>: {{ $tagihan->pelanggan->alamat }}</td></tr>
                            <tr><td>Penggunaan Air di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->isoFormat('MMMM YYYY')) }}</td></tr>
                            <tr><td>Dibayarkan di Bulan</td><td>: {{ strtoupper($tagihan->periode_tagihan->addMonth()->isoFormat('MMMM YYYY')) }}</td></tr>
                            <tr><td>Jml Angka Meter Akhir</td><td>: {{ $tagihan->meter_akhir }} m³</td></tr>
                            <tr><td>Jml Angka Meter Awal</td><td>: {{ $tagihan->meter_awal }} m³</td></tr>
                            <tr><td>Jml Pemakaian Air</td><td>: {{ $tagihan->total_pemakaian_m3 }} m³</td></tr>
                        </table>
                        <br>
                        <p>Sindangraja {{ $tagihan->tanggal_cetak->isoFormat('DD MMMM YY') }}<br>Petugas Penagihan<br>{{ $tagihan->petugas->nama_petugas ?? 'N/A' }}</p>
                    </div>
                    <div class="bukti-kanan">
                        <div class="judul-bagian">RINCIAN PEMBAYARAN AIR</div>
                        <p>Pembayaran bulan ini</p>
                        <table class="rincian-table">
                            {{-- Loop untuk setiap item rincian dari tagihan ini --}}
                            @foreach ($tagihan->rincian as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $item->deskripsi }}</td>
                                    <td>{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td>{{ $item->kuantitas }} :</td>
                                    <td>Rp. {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr><td colspan="3" style="text-align:left;"><b>Jml</b></td><td><b>Rp. {{ number_format($tagihan->rincian->sum('subtotal'), 0, ',', '.') }}</b></td></tr>
                        </table>
                        <br>
                        <p>Tunggakan dan Denda</p>
                        <table class="rincian-table">
                            {{-- Note: Data tunggakan dan denda masih statis, perlu dilogikakan jika ada --}}
                             <tr><td>1 Sisa Tunggakan Bulan Lalu</td><td>Rp.</td><td>0</td></tr>
                             <tr><td>2 Denda</td><td>Rp.</td><td>0</td></tr>
                             <tr><td><b>Jumlah Tunggakan + Denda</b></td><td><b>Rp.</b></td><td><b>0</b></td></tr>
                        </table>
                         <table class="rincian-table">
                             <tr><td>1 Jml Pembayaran Bulan Ini</td><td>Rp.</td><td>{{ number_format($tagihan->rincian->sum('subtotal'), 0, ',', '.') }}</td></tr>
                             <tr><td>2 Tunggakan sd Bulan Ini</td><td>Rp.</td><td>0</td></tr>
                             <tr><td class="total-merah">Jml Total yg Harus di Bayar</td><td class="total-merah">Rp.</td><td class="total-merah">{{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        {{-- Bagian ini akan tampil jika $semua_tagihan kosong --}}
        <div class="struk-container" style="text-align: center; font-family: Arial, sans-serif;">
            <h2>Tidak Ada Data</h2>
            <p>Tidak ada tagihan yang ditemukan untuk periode yang Anda pilih. Silakan kembali dan pilih periode lain.</p>
        </div>
    @endforelse

</body>
</html>
