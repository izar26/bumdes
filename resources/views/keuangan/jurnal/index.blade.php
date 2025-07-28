@extends('adminlte::page')

@section('title', 'Jurnal Umum')

@section('content_header')
    <h1>Jurnal Umum</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Jurnal Umum</h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan / Akun</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jurnals as $jurnal)
                    {{-- Baris Header per Jurnal --}}
                    <tr classg="table-active">
                        <td style="width: 15%;">{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d M Y') }}</td>
                        <td colspan="3"><strong>{{ $jurnal->deskripsi }}</strong></td>
                    </tr>
                    
                    {{-- Baris Detail per Jurnal --}}
                    @foreach ($jurnal->detailJurnals as $detail)
                        <tr>
                            <td></td>
                            {{-- Beri indentasi untuk akun yang posisinya kredit agar mudah dibaca --}}
                            <td style="{{ $detail->kredit > 0 ? 'padding-left: 30px;' : '' }}">
                                [{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}
                            </td>
                            <td class="text-right">
                                {{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 2, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data jurnal. Silakan buat transaksi baru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
    <style>
        /* Menambahkan garis bawah untuk setiap grup jurnal agar lebih terpisah */
        tbody tr.table-active + tr + tr {
            border-top: 2px solid #dee2e6;
        }
    </style>
@stop