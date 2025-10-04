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
                {{-- <th>KET</th> --}}
                <th>{{Carbon\Carbon::now()->subMonth()->locale('id')->isoFormat('MMM')}}</th>
                <th>{{ Carbon\Carbon::now()->locale('id')->isoFormat('MMM') }}</th>
                <th>JML PMKN</th>
                <th>ADM</th>
                <th>PML</th>
                <th>TAGIHAN</th>
                <th>TUNGGAKAN</th>
                <th>DENDA</th>
                <th>JML DENDA</th>
                <th>TOTAL</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
    @forelse ($semua_tagihan as $index => $tagihan)
        @php
            // Kalkulasi ini sudah benar, kita hanya merapikan tampilannya
            $adm = $tagihan->rincian->where('deskripsi', 'Biaya Administrasi')->sum('subtotal');
            $pml = $tagihan->rincian->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal');
            $jml_denda = $tagihan->tunggakan + $tagihan->denda;
        @endphp
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            {{-- PERBAIKAN: Menggunakan id_pelanggan dari relasi, bukan pelanggan_id --}}
            <td class="text-center">{{ $tagihan->pelanggan->id_pelanggan ?? '' }}</td>
            <td>{{ $tagihan->pelanggan->nama ?? 'N/A' }}</td>
            <td>{{ $tagihan->pelanggan->alamat ?? '' }}</td>
            {{-- Kolom 'Ket' dihilangkan sesuai kode Anda --}}

            {{-- PERBAIKAN: Meteran dibuat rata tengah agar lebih rapi dan konsisten --}}
            <td class="text-center">{{ number_format($tagihan->meter_awal) }}</td>
            <td class="text-center">{{ number_format($tagihan->meter_akhir) }}</td>
            <td class="text-center">{{ number_format($tagihan->total_pemakaian_m3) }}</td>

            {{-- Kolom keuangan (rata kanan) sudah benar --}}
            <td class="text-right">{{ number_format($adm) }}</td>
            <td class="text-right">{{ number_format($pml) }}</td>
            <td class="text-right">{{ number_format($tagihan->subtotal_pemakaian) }}</td>
            <td class="text-right">{{ number_format($tagihan->tunggakan) }}</td>
            <td class="text-right">{{ number_format($tagihan->denda) }}</td>
            <td class="text-right">{{ number_format($jml_denda) }}</td>
            <td class="text-right font-weight-bold">{{ number_format($tagihan->total_harus_dibayar) }}</td>

            {{-- PERBAIKAN: Menambahkan kembali kolom STATUS agar jumlah kolom sesuai --}}
            <td class="text-center">
                 @if($tagihan->status_pembayaran == 'Lunas')
                    <span class="badge badge-success">Lunas</span>
                 @elseif($tagihan->status_pembayaran == 'Batal')
                    <span class="badge badge-secondary">Batal</span>
                 @elseif($tagihan->status_pembayaran == 'Cicil')
                    <span class="badge badge-info">Cicil</span>
                 @else
                    <span class="badge badge-warning">Belum Lunas</span>
                 @endif
            </td>
        </tr>
    @empty
        <tr>
            {{-- PERBAIKAN: colspan disesuaikan dengan jumlah kolom (15) --}}
            <td colspan="15" class="text-center">Tidak ada data untuk periode ini.</td>
        </tr>
    @endforelse
</tbody>
 @if($semua_tagihan->isNotEmpty())
<tfoot class="bg-light font-weight-bold">
    <tr>
        {{-- PERBAIKAN: Colspan diubah menjadi 4 agar sejajar --}}
        <td colspan="4" class="text-center">TOTAL</td>

        {{-- Kolom total untuk METER LALU --}}
        <td class="text-right">{{ number_format($semua_tagihan->sum('meter_awal')) }}</td>

        {{-- Kolom total untuk METER KINI --}}
        <td class="text-right">{{ number_format($semua_tagihan->sum('meter_akhir')) }}</td>

        <td class="text-right">{{ number_format($semua_tagihan->sum('total_pemakaian_m3')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Administrasi')->sum('subtotal')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('subtotal_pemakaian')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('denda')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan') + $semua_tagihan->sum('denda')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('total_harus_dibayar')) }}</td>

        {{-- PERBAIKAN: Kolom terakhir yang kosong untuk STATUS dihapus --}}
    </tr>
</tfoot>
@endif
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
