<!DOCTYPE html>
<html>
<head>
    <title>Cetak Jurnal Umum</title>
    <style>
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
            padding: 6px;
            text-align: center;
        }
        td {
            border: 1px solid #ccc;
            padding: 5px;
            vertical-align: top;
        }
        tr.jurnal-main {
            background: #f9f9f9;
            font-weight: bold;
        }
        tr.total-row {
            background: #e6f2f2;
            font-weight: bold;
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
            height: 60px;
        }

        /* Cetak */
        @media print {
            body { margin: 0; }
            .kop, .garis-pembatas, th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    {{-- KOP Surat --}}
    <div class="kop">
        @if($bumdes && $bumdes->logo)
            <img src="{{ public_path('storage/' . $bumdes->logo) }}" alt="Logo">
        @endif
        <div class="kop-text">
            <h1>{{ $bumdes->nama_bumdes ?? 'BUMDes' }}</h1>
            <h2>{{ $bumdes->alamat ?? '' }}</h2>
            <p>Email: {{ $bumdes->email ?? '-' }} | Telp: {{ $bumdes->telepon ?? '-' }}</p>
        </div>
    </div>
    <div class="garis-pembatas"></div>

    {{-- Judul --}}
    <div class="judul">
        <h3>Laporan Jurnal Umum - Tahun {{ $tahun }}</h3>
        <p>Status: <strong>{{ ucfirst($statusJurnal) }}</strong></p>
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
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
            @foreach ($jurnals as $jurnal)
                @php
                    $totalDebitAll += $jurnal->total_debit;
                    $totalKreditAll += $jurnal->total_kredit;
                @endphp
                <tr class="jurnal-main">
                    <td>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d/m/Y') }}</td>
                    <td>{{ $jurnal->deskripsi }}</td>
                    <td>Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($jurnal->total_kredit, 0, ',', '.') }}</td>
                </tr>
                @foreach ($jurnal->detailJurnals as $detail)
                    <tr>
                        <td></td>
                        <td>[{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}</td>
                        <td>{{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 0, ',', '.') : '' }}</td>
                        <td>{{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 0, ',', '.') : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">TOTAL</td>
                <td>Rp {{ number_format($totalDebitAll, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($totalKreditAll, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <table class="footer" style="margin-top: 60px; width: 100%;">
    <tr>
        <td style="text-align: center; width: 50%;">
            {{ $bumdes->alamat ?? '' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
        </td>
        <td style="text-align: center; width: 50%;"></td>
    </tr>
    <tr>
        <td style="text-align: center;">Direktur</td>
        <td style="text-align: center;">Bendahara</td>
    </tr>
    <tr>
        <td style="height: 80px;"></td>
        <td style="height: 80px;"></td>
    </tr>
    <tr>
        <td style="text-align: center;">(___________________)</td>
        <td style="text-align: center;">(___________________)</td>
    </tr>
</table>

    <script>
        window.print();
    </script>
</body>
</html>
