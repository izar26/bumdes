@extends('adminlte::page')

@section('title', 'Approval Jurnal')

@section('content_header')
    <h1>Approval Jurnal</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>Approval Jurnal (Menunggu)</strong>
        </div>
        <div>
            {{-- reset / filter bisa ditambahkan --}}
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Unit Usaha</th>
                    <th class="text-right">Total Debit</th>
                    <th class="text-right">Total Kredit</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurnals as $jurnal)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('d M Y') }}</td>
                        <td>
                            {{ $jurnal->deskripsi }}
                            <br>
                            <small class="text-muted">oleh: {{ $jurnal->user->name ?? '–' }} ({{ $jurnal->user->role ?? '–' }})</small>
                            @if($jurnal->status === 'ditolak' && $jurnal->rejected_reason)
                                <br><small class="text-danger">Alasan tolak: {{ $jurnal->rejected_reason }}</small>
                            @endif
                        </td>
                        <td>{{ $jurnal->unitUsaha->nama_unit ?? 'BUMDes Pusat' }}</td>
                        <td class="text-right">Rp {{ number_format($jurnal->total_debit, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($jurnal->total_kredit, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge badge-warning">Menunggu</span>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('approval-jurnal.approve', $jurnal->jurnal_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                            </form>

                            <!-- Button modal -->
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal-{{ $jurnal->jurnal_id }}">
                                <i class="fas fa-times"></i> Reject
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="rejectModal-{{ $jurnal->jurnal_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <form method="POST" action="{{ route('approval-jurnal.reject', $jurnal->jurnal_id) }}">
                                  @csrf
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title">Tolak Jurnal</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                      <div class="form-group">
                                        <label>Alasan Penolakan</label>
                                        <textarea name="rejected_reason" class="form-control" required></textarea>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="submit" class="btn btn-danger">Tolak</button>
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    </div>
                                  </div>
                                </form>
                              </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Tidak ada jurnal menunggu approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $jurnals->links() }}
    </div>
</div>
@stop
