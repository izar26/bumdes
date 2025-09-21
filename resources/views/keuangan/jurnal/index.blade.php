@extends('adminlte::page')

@section('title', 'Jurnal Umum')

@section('content_header')
    <h1>Jurnal Umum</h1>
@stop

@section('content')
@php
    // Perhitungan total bisa tetap di sini atau dipindahkan ke controller
    $totalDebitAll = $jurnals->sum('total_debit');
    $totalKreditAll = $jurnals->sum('total_kredit');
    $isBalanced = abs($totalDebitAll - $totalKreditAll) < 0.01;
    $statusAll = $isBalanced && $totalDebitAll > 0 ? 'Seimbang' : 'Tidak Seimbang';
    $badgeClassAll = $isBalanced ? 'badge-success' : 'badge-danger';
@endphp

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0"><i class="fas fa-book"></i> Riwayat Jurnal Umum</h3>
            <br>
             <div class="small mt-2">
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

                @if(auth()->user()->hasAnyRole(['admin_bumdes','bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']))
                <div class="col-md-2">
                    <label class="form-label">Unit Usaha</label>
                    {{-- --- PERBAIKAN TAMPILAN FILTER DIMULAI DI SINI --- --}}
                    <select name="unit_usaha_id" class="form-control">
                        <option value="">Semua (Gabungan)</option>
                        <option value="pusat" {{ request('unit_usaha_id') == 'pusat' ? 'selected' : '' }}>-- Hanya BUMDes Pusat --</option>
                        @foreach($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    {{-- --- AKHIR PERBAIKAN TAMPILAN FILTER --- --}}
                </div>
                @endif

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-80 me-2 mx-2" title="Filter"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('jurnal-umum.index') }}" class="btn btn-secondary w-80 me-2 mx-2" title="Refresh"><i class="fas fa-sync"></i></a>
                    <button type="button" class="btn btn-success" title="Cetak Laporan" data-toggle="modal" data-target="#printModal">
                        <i class="fas fa-print"></i>
                    </button>
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
                                @if($jurnal->unitUsaha)
                                    <span class="badge badge-info">{{ $jurnal->unitUsaha->nama_unit }}</span>
                                @else
                                    <span class="badge badge-secondary">BUMDes Pusat</span>
                                @endif
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
                            <td class="text-center">
                                @php
                                    $canEditOrDelete = auth()->user()->hasAnyRole(['admin_bumdes', 'bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']) ||
                                                       (auth()->user()->hasAnyRole(['admin_unit_usaha', 'manajer_unit_usaha']) &&
                                                        auth()->user()->unitUsahas->pluck('unit_usaha_id')->contains($jurnal->unit_usaha_id));
                                @endphp
                                @if($canEditOrDelete && $jurnal->status != 'disetujui')
                                    <a href="{{ route('jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-xs" title="Hapus Jurnal" data-toggle="modal" data-target="#deleteModal" data-id="{{ $jurnal->jurnal_id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @elseif ($jurnal->status == 'disetujui' && auth()->user()->hasAnyRole(['admin_bumdes', 'bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']))
                                     <a href="{{ route('jurnal-umum.edit', $jurnal->jurnal_id) }}" class="btn btn-info btn-xs" title="Edit Jurnal (akan mereset status)">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
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
        <div class="mt-3">{{ $jurnals->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
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
        Yakin ingin menghapus jurnal ini? Tindakan ini tidak dapat dibatalkan.
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

{{-- Print Modal --}}
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="printModalLabel">Atur Tanggal Cetak Laporan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="printForm" action="{{ route('jurnal-umum.show', 'print') }}" method="GET" target="_blank">
        <div class="modal-body">
            <p>Laporan akan dicetak sesuai dengan filter yang sedang aktif. Silakan tentukan tanggal yang akan tertera pada laporan.</p>
            <div class="form-group">
                <label for="tanggal_cetak">Tanggal Cetak</label>
                <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" value="{{ date('Y-m-d') }}" required>
            </div>

            {{-- Hidden inputs to carry over current filters --}}
            <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
            <input type="hidden" name="approval_status" value="{{ request('approval_status', 'disetujui') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <input type="hidden" name="unit_usaha_id" value="{{ request('unit_usaha_id') }}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-print"></i> Cetak Sekarang</button>
        </div>
      </form>
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
