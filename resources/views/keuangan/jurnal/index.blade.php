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
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th>Keterangan / Akun</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Kredit</th>
                    <th class="text-center" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jurnals as $jurnal)
                    {{-- Baris Header per Jurnal --}}
                    <tr class="table-active">
                        <td><strong>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d M Y') }}</strong></td>
                        <td><strong>{{ $jurnal->deskripsi }}</strong></td>
                        <td></td>
                        <td></td>
                        {{-- ================== PERUBAHAN DI SINI ================== --}}
                        {{-- Tombol aksi sekarang ada di baris header --}}
                        <td class="text-center">
                            <a href="{{ route('jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('jurnal-umum.destroy', $jurnal->jurnal_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jurnal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" title="Hapus Jurnal"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                        {{-- ========================================================= --}}
                    </tr>
                    
                    {{-- Baris Detail per Jurnal --}}
                    @foreach ($jurnal->detailJurnals as $detail)
                        <tr>
                            <td></td>
                            <td style="{{ $detail->kredit > 0 ? 'padding-left: 30px;' : '' }}">
                                [{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}
                                {{-- TAMPILKAN KETERANGAN BARIS --}}
                                @if($detail->keterangan)
                                    <br><small class="text-muted"><em>{{ $detail->keterangan }}</em></small>
                                @endif
                            </td>
                            <td class="text-right">{{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 2, ',', '.') : '' }}</td>
                            <td class="text-right">{{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 2, ',', '.') : '' }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data jurnal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop