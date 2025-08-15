@extends('adminlte::page')

@section('title', 'Approval Jurnal')

@section('content_header')
    <h1>Approval Jurnal</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <strong>Approval Jurnal (Menunggu)</strong>
            <div class="d-flex align-items-center">
                {{-- Tombol Aksi Massal --}}
                <form id="bulk-approve-form" action="{{ route('approval-jurnal.approveSelected') }}" method="POST" class="mr-2">
                    @csrf
                    {{-- Input tersembunyi untuk ID jurnal akan ditambahkan oleh JavaScript --}}
                    <button type="submit" class="btn btn-success btn-sm" id="bulk-approve-btn" disabled>
                        <i class="fas fa-check-double"></i> Approve yang Dipilih
                    </button>
                </form>

                <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <hr class="my-2">
        {{-- Form Filter --}}
        <form method="GET" action="{{ route('approval-jurnal.index') }}" class="form-inline">
            <div class="form-group mr-2">
                <label for="unit_usaha_id" class="mr-2">Filter Unit Usaha:</label>
                <select name="unit_usaha_id" id="unit_usaha_id" class="form-control form-control-sm">
                    <option value="">-- Semua Unit Usaha --</option>
                    @foreach($unitUsahas as $unit)
                        <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                            {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('approval-jurnal.index') }}" class="btn btn-default btn-sm ml-2"><i class="fas fa-undo"></i> Reset</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 1%;"><input type="checkbox" id="select-all-checkbox"></th>
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
                            <td class="text-center">
                                <input type="checkbox" class="jurnal-checkbox" name="jurnal_ids[]" value="{{ $jurnal->jurnal_id }}" form="bulk-approve-form">
                            </td>
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
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal-{{ $jurnal->jurnal_id }}">
                                    <i class="fas fa-check"></i> Approve
                                </button>

                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal-{{ $jurnal->jurnal_id }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>

                                <div class="modal fade" id="approveModal-{{ $jurnal->jurnal_id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel-{{ $jurnal->jurnal_id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $jurnal->jurnal_id }}">Konfirmasi Approve Jurnal</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-left">
                                                Apakah Anda yakin ingin menyetujui jurnal dengan deskripsi: <strong>"{{ $jurnal->deskripsi }}"</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('approval-jurnal.approve', $jurnal->jurnal_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Ya, Setujui</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="modal fade" id="rejectModal-{{ $jurnal->jurnal_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form method="POST" action="{{ route('approval-jurnal.reject', $jurnal->jurnal_id) }}">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Jurnal</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body text-left">
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
                        <tr><td colspan="8" class="text-center">Tidak ada jurnal menunggu approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $jurnals->appends(request()->query())->links() }}
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Fungsi untuk mengupdate status tombol 'Approve yang Dipilih'
    function updateBulkApproveButton() {
        const checkedCount = $('.jurnal-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulk-approve-btn').prop('disabled', false);
        } else {
            $('#bulk-approve-btn').prop('disabled', true);
        }
    }

    // Checkbox 'pilih semua'
    $('#select-all-checkbox').on('click', function() {
        $('.jurnal-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkApproveButton();
    });

    // Checkbox per baris
    $('.jurnal-checkbox').on('click', function() {
        if ($('.jurnal-checkbox:checked').length === $('.jurnal-checkbox').length) {
            $('#select-all-checkbox').prop('checked', true);
        } else {
            $('#select-all-checkbox').prop('checked', false);
        }
        updateBulkApproveButton();
    });

    // Konfirmasi sebelum submit form bulk approve
    $('#bulk-approve-form').on('submit', function(e) {
        e.preventDefault(); // Mencegah form submit secara default

        const selectedCount = $('.jurnal-checkbox:checked').length;
        if (selectedCount > 0) {
            if (confirm(`Apakah Anda yakin ingin menyetujui ${selectedCount} jurnal yang dipilih?`)) {
                this.submit(); // Lanjutkan submit jika dikonfirmasi
            }
        } else {
            alert('Silakan pilih setidaknya satu jurnal untuk disetujui.');
        }
    });

    // Menambahkan query string filter saat paginasi
    // Modifikasi pada blade: {{ $jurnals->appends(request()->query())->links() }}
    // Ini memastikan filter tetap aktif saat berpindah halaman
});
</script>
@stop
