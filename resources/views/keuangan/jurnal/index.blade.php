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
            <div class="small mt-2">
                <br>
                <strong>Total Debit:</strong> Rp {{ number_format($totalDebitAll, 0, ',', '.') }} |
                <strong>Total Kredit:</strong> Rp {{ number_format($totalKreditAll, 0, ',', '.') }}
            </div>
        </div>
        <span class="badge {{ $badgeClassAll }} p-2">Status Keseluruhan: {{ $statusAll }}</span>
    </div>
    <div class="card-body">
        {{-- Filter Section --}}
        <form id="filterForm" method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status Jurnal</label>
                    <select name="approval_status" class="form-control">
                        <option value="semua" {{ $statusJurnal == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="menunggu" {{ $statusJurnal == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ $statusJurnal == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $statusJurnal == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
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

                @if(auth()->user()->hasRole(['admin_bumdes','bendahara_bumdes']))
                <div class="col-md-2">
                    <label class="form-label">Unit Usaha</label>
                    <select name="unit_usaha_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-80 me-2 mx-2" title="Filter"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('jurnal-umum.index') }}" class="btn btn-secondary w-80 me-2 mx-2" title="Refresh"><i class="fas fa-sync"></i></a>
                    <a href="{{ route('jurnal-umum.show', [
    'jurnal_umum' => 'print',
    'year' => request('year'),
    'approval_status' => request('approval_status'),
    'start_date' => request('start_date'),
    'end_date' => request('end_date'),
    'unit_usaha_id' => request('unit_usaha_id')
]) }}"
    target="_blank"
    class="btn btn-success w-100"
    title="Cetak Laporan">
    <i class="fas fa-print"></i>
</a>


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
                            <td>
                                <strong>{{ $jurnal->deskripsi }}</strong>
                                <br>
                                @switch($jurnal->status)
                                    @case('menunggu')
                                        <span class="badge badge-warning">Menunggu</span>
                                        @break
                                    @case('disetujui')
                                        <span class="badge badge-success">Disetujui</span>
                                        @break
                                    @case('ditolak')
                                        <span class="badge badge-danger">Ditolak</span>
                                        @if($jurnal->rejected_reason)
                                            <div class="mt-1 text-danger small">
                                                <strong>Alasan:</strong> {{ $jurnal->rejected_reason }}
                                            </div>
                                        @endif
                                        @break
                                @endswitch
                            </td>
                            <td class="text-right"><strong>Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($jurnal->total_kredit, 0, ',', '.') }}</strong></td>
<form action="{{ route('jurnal-umum.destroy', $jurnal->jurnal_id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
</form>
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

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Yakin ingin menghapus jurnal ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <form id="deleteForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  $('#deleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var jurnalId = button.data('id');
    var form = $(this).find('#deleteForm');
    var actionUrl = '{{ route("jurnal-umum.destroy", ":jurnal_id") }}';
    actionUrl = actionUrl.replace(':jurnal_id', jurnalId);
    form.attr('action', actionUrl);
  });
})
</script>
@stop
