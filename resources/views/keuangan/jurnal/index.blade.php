@extends('adminlte::page')

@section('title', 'Jurnal Umum')

@section('content_header')
    <h1>Jurnal Umum</h1>
@stop

@section('content')
@php
    $totalDebitAll = $jurnals->sum('total_debit');
    $totalKreditAll = $jurnals->sum('total_kredit');
    $statusAll = abs($totalDebitAll - $totalKreditAll) < 0.01 && $totalDebitAll > 0 ? 'Seimbang' : 'Tidak Seimbang';
    $badgeClassAll = $statusAll === 'Seimbang' ? 'badge-success' : 'badge-danger';
@endphp

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0"><i class="fas fa-book"></i> Riwayat Jurnal Umum</h3>
            <div class="small mt-1">
                <strong>Total Debit:</strong> Rp {{ number_format($totalDebitAll, 0, ',', '.') }} |
                <strong>Total Kredit:</strong> Rp {{ number_format($totalKreditAll, 0, ',', '.') }}
            </div>
        </div>
        <span class="badge {{ $badgeClassAll }} p-2">Status Keseluruhan: {{ $statusAll }}</span>
    </div>
    <div class="card-body">
        {{-- Filter Section --}}
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control">
                        <option value="">Semua</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status Balance</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="seimbang" {{ request('status')=='seimbang'?'selected':'' }}>Seimbang</option>
                        <option value="tidak_seimbang" {{ request('status')=='tidak_seimbang'?'selected':'' }}>Tidak Seimbang</option>
                    </select>
                </div>
                @if(auth()->user()->hasRole(['admin_bumdes','bendahara_bumdes']))
                <div class="col-md-2">
                    <label class="form-label">Unit Usaha</label>
                    <select name="unit_usaha_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id')==$unit->unit_usaha_id?'selected':'' }}>{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        {{-- Table Section --}}
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="bg-light">
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
                        <tr class="table-primary">
                            <td><strong>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d M Y') }}</strong></td>
                            <td><strong>{{ $jurnal->deskripsi }}</strong></td>
                            <td></td>
                            <td></td>
                            <td class="text-center">
                                <a href="{{ route('keuangan.jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('keuangan.jurnal-umum.destroy', $jurnal->jurnal_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jurnal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" title="Hapus Jurnal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @foreach ($jurnal->detailJurnals as $detail)
                            <tr>
                                <td></td>
                                <td style="{{ $detail->kredit > 0 ? 'padding-left: 30px;' : '' }}">
                                    [{{ $detail->akun->kode_akun }}] {{ $detail->akun->nama_akun }}
                                    @if($detail->keterangan)
                                        <br><small class="text-muted"><em>{{ $detail->keterangan }}</em></small>
                                    @endif
                                </td>
                                <td class="text-right">{{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 0, ',', '.') : '' }}</td>
                                <td class="text-right">{{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 0, ',', '.') : '' }}</td>
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
        <div class="mt-3">{{ $jurnals->links() }}</div>
    </div>
</div>
@stop

@section('js')
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
@stop
