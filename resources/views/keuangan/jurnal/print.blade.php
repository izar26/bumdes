<!DOCTYPE html>
<html>
<head>
    <title>Cetak Jurnal Umum</title>
    <style>
        /* CSS Anda sebagian besar sudah sangat baik, jadi saya pertahankan */
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        /* KOP Surat */
        .kop {
            display: flex;
            align-items: center;
            padding: 10px;
            background: linear-gradient(to right, #e0f7f7, #ffffff);
        }
        .kop img {
            height: 75px;
            margin-right: 15px;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            color: #006666;
        }
        .kop-text h2 {
            margin: 2px 0 0;
            font-size: 14px;
            font-weight: normal;
            color: #333;
        }
        .kop-text p {
            margin: 2px 0;
            font-size: 12px;
        }

        /* Garis Ganda */
        .garis-pembatas {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* Judul Laporan */
        .judul {
            text-align: center;
            margin-bottom: 10px;
        }
        .judul h3 {
            margin: 5px 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .judul p {
            margin: 2px 0;
        }

        /* Table */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            font-size: 12px;
        }
        th {
            background: #008080;
            color: white;
            border: 1px solid #006666;
            padding: 8px; /* Sedikit padding tambahan untuk kenyamanan */
            text-align: center;
        }
        td {
            border: 1px solid #ccc;
            padding: 6px; /* Sedikit padding tambahan */
            vertical-align: top;
        }
        tr.jurnal-main {
            background: #f2f9f9; /* Warna sedikit diubah agar lebih soft */
            font-weight: bold;
        }
        tr.total-row {
            background: #e6f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* Footer tanda tangan */
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .footer td {
            border: none;
            padding: 5px;
            text-align: center;
        }
        .ttd-space {
            height: 70px; /* Ruang lebih untuk tanda tangan */
        }
        .nama-terang {
            text-decoration: underline;
            font-weight: bold;
        }

        /* Cetak */
        @media print {
            body { margin: 0; }
            .kop, .garis-pembatas, th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    {{-- KOP Surat --}}
    <div class="kop">
        @if($bumdes && $bumdes->logo)
            {{-- Pastikan path ke logo benar, public_path mungkin memerlukan penyesuaian tergantung setup Anda --}}
            <img src="{{ public_path('storage/' . $bumdes->logo) }}" alt="Logo">
        @endif
        <div class="kop-text">
            <h1>{{ $bumdes->nama_bumdes ?? 'BUMDes Anda' }}</h1>
            <h2>{{ $bumdes->alamat ?? 'Alamat BUMDes Anda' }}</h2>
            <p>Email: {{ $bumdes->email ?? '-' }} | Telp: {{ $bumdes->telepon ?? '-' }}</p>
        </div>
    </div>
    <div class="garis-pembatas"></div>

    {{-- Judul --}}
    <div class="judul">
        <h3>Laporan Jurnal Umum</h3>
        <p>Periode Tahun: <strong>{{ $tahun }}</strong> | Status: <strong>{{ ucfirst($statusJurnal) }}</strong></p>
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th>Keterangan / Akun</th>
                <th style="width: 20%;">Debit</th>
                <th style="width: 20%;">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebitAll = 0;
                $totalKreditAll = 0;
            @endphp
            @forelse ($jurnals as $jurnal)
                @php
                    $totalDebitAll += $jurnal->total_debit;
                    $totalKreditAll += $jurnal->total_kredit;
                @endphp
                {{-- PERBAIKAN 1: Baris utama jurnal lebih ringkas --}}
                <tr class="jurnal-main">
                    <td class="text-center">{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d/m/Y') }}</td>
                    <td colspan="3">{{ $jurnal->deskripsi }}</td>
                </tr>
                @foreach ($jurnal->detailJurnals as $detail)
                    <tr>
                        <td></td>
                        {{-- PERBAIKAN 2: Indentasi untuk akun kredit --}}
                        <td style="{{ $detail->kredit > 0 ? 'padding-left: 30px;' : '' }}">
                            [{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}
                        </td>
                        <td class="text-right">{{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 2, ',', '.') : '' }}</td>
                        <td class="text-right">{{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 2, ',', '.') : '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data jurnal untuk ditampilkan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalDebitAll, 2, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalKreditAll, 2, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- PERBAIKAN 3: Area Tanda Tangan Dinamis --}}
    <table class="footer">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                {{  'Cianjur' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td>Menyetujui,</td>
        </tr>
        <tr>
            <td>
                <strong>Direktur'</strong>
            </td>
            <td>
                <strong>Bendahara</strong>
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

    <script>
        // Script untuk langsung memicu dialog cetak saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
