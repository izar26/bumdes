{{-- FILE: resources/views/usaha/tagihan/rekap-cetak.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Tagihan Periode {{ $nama_bulan[$bulan_terpilih] }} {{ $tahun_terpilih }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 10px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 3px 5px; }
        th { background-color: #f7ca4a; text-align: center; font-weight: bold; }
        td { vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        tfoot td { font-weight: bold; background-color: #fbe89d; }
        tfoot td:last-child { color: red; }
        h4, h5 { text-align: center; margin: 4px 0; }
        @media print {
            .d-print-none { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <h4>REKAPITULASI TAGIHAN SPAMDes</h4>
    <h5>PERIODE: {{ strtoupper($nama_bulan[$bulan_terpilih]) }} {{ $tahun_terpilih }}</h5>

    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NOMOR ID</th>
                <th>NAMA</th>
                <th>LOKASI</th>
                <th>KET</th>
                <th>AGTS</th>
                <th>SEP</th>
                <th>JML</th>
                <th>ADM</th>
                <th>PML</th>
                <th>TAGIHAN</th>
                <th>TUNGGAKAN</th>
                <th>DENDA</th>
                <th>JML DENDA</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($semua_tagihan as $index => $tagihan)
                @php
                    $adm = $tagihan->rincian->where('deskripsi', 'Biaya Administrasi')->sum('subtotal');
                    $pml = $tagihan->rincian->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal');
                    $jml_denda = $tagihan->tunggakan + $tagihan->denda;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">000{{ $tagihan->pelanggan_id ?? 'N/A' }}</td>
                    <td>{{ $tagihan->pelanggan->nama ?? 'N/A' }}</td>
                    <td>{{ $tagihan->pelanggan->alamat ?? '' }}</td>
                    <td class="text-center">{{ $tagihan->pelanggan->golongan ?? 'R' }}</td>
                    <td class="text-right">{{ number_format($tagihan->meter_awal) }}</td>
                    <td class="text-right">{{ number_format($tagihan->meter_akhir) }}</td>
                    <td class="text-right">{{ $tagihan->total_pemakaian_m3 }}</td>
                    <td class="text-right">{{ number_format($adm) }}</td>
                    <td class="text-right">{{ number_format($pml) }}</td>
                    <td class="text-right">{{ number_format($tagihan->subtotal_pemakaian) }}</td>
                    <td class="text-right">{{ number_format($tagihan->tunggakan) }}</td>
                    <td class="text-right">{{ number_format($tagihan->denda) }}</td>
                    <td class="text-right">{{ number_format($jml_denda) }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($tagihan->total_harus_dibayar) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($semua_tagihan->sum('total_pemakaian_m3')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Administrasi')->sum('subtotal')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->sum('subtotal_pemakaian')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->sum('denda')) }}</td>
                <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan') + $semua_tagihan->sum('denda')) }}</td>
                <td class="text-right" style="color:red;">{{ number_format($semua_tagihan->sum('total_harus_dibayar')) }}</td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table style="border:none; width:40%; font-size:9px;">
        {{-- <tr><td>R itu keterangan</td><td>: Rumahan</td></tr>
        <tr><td>U itu keterangan</td><td>: Usaha</td></tr>
        <tr><td>S itu keterangan</td><td>: Sosial</td></tr> --}}
        <tr><td>adm</td><td>: administrasi</td></tr>
        <tr><td>pml</td><td>: pemeliharaan</td></tr>
    </table>
</body>
</html>
