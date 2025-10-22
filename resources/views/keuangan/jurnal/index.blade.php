@extends('adminlte::page')

@section('title', 'Jurnal Umum')

@section('content_header')
    <h1>Jurnal Umum</h1>
@stop

@section('content')
@php
    $isBalanced = abs($totalDebitAll - $totalKreditAll) < 0.01;
    $statusAll = $isBalanced && $totalDebitAll > 0 ? 'Seimbang' : 'Tidak Seimbang';
    $badgeClassAll = $isBalanced ? 'badge-success' : 'badge-danger';
@endphp
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" id="jurnalHeader">
        <div>
            <h3 class="card-title mb-0"><i class="fas fa-book"></i> Riwayat Jurnal Umum</h3>
        </div>
        <div class="small mt-2">
    <br>
    <strong>Total Debit (Hasil Filter):</strong> Rp {{ number_format($totalDebitAll, 0, ',', '.') }} |
    <strong>Total Kredit (Hasil Filter):</strong> Rp {{ number_format($totalKreditAll, 0, ',', '.') }}
</div>
<span class="badge {{ $badgeClassAll }} p-2" id="statusBadge">Status Keseluruhan: {{ $statusAll }}</span>
    </div>

    <div class="card-body">
        {{-- Filter Section --}}
        <form id="filterForm" method="GET" class="mb-4">
            <div class="row g-2 align-items-end">
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari Deskripsi Jurnal</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Contoh: Pembelian ATK" value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-control">
                        <option value="semua">Semua Tahun</option>
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
                        <option value="semua" {{ $statusJurnal == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="menunggu" {{ $statusJurnal == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ $statusJurnal == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $statusJurnal == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                @if(auth()->user()->hasAnyRole(['admin_bumdes','bendahara_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']))
                <div class="col-md-3">
                    <label class="form-label">Unit Usaha</label>
                    <select name="unit_usaha_id" class="form-control">
                        <option value="">Semua (Gabungan)</option>
                        <option value="pusat" {{ request('unit_usaha_id') == 'pusat' ? 'selected' : '' }}>-- Hanya BUMDes Pusat --</option>
                        @foreach($unitUsahas as $unit)
                            <option value="{{ $unit->unit_usaha_id }}" {{ request('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                {{ $unit->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2" title="Cari"><i class="fas fa-search"></i></button>
                    <a href="{{ route('jurnal-umum.index') }}" class="btn btn-secondary w-100 mx-2" title="Refresh"><i class="fas fa-sync"></i></a>
                    <button type="button" class="btn btn-success w-100" title="Cetak Laporan" data-toggle="modal" data-target="#printModal">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
             <div class="row g-2 mt-2">
                <div class="col-md-3">
                     <label class="form-label">Tanggal Mulai</label>
                     <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                 </div>
                 <div class="col-md-3">
                     <label class="form-label">Tanggal Akhir</label>
                     <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                 </div>
            </div>
        </form>

        {{-- Kontainer untuk memuat hasil AJAX --}}
        <div id="jurnalTableContainer">
            {{-- Muat data tabel awal saat halaman pertama kali dibuka --}}
            @include('keuangan.jurnal._jurnal_table', ['jurnals' => $jurnals, 'totalDebitAll' => $totalDebitAll, 'totalKreditAll' => $totalKreditAll])
        </div>
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
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
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
    // Kode untuk modal delete
    $('[data-toggle="tooltip"]').tooltip();
    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var jurnalId = button.data('id');
        var form = $(this).find('#deleteForm');
        var actionUrl = '{{ route("jurnal-umum.destroy", ":jurnal_id") }}';
        actionUrl = actionUrl.replace(':jurnal_id', jurnalId);
        form.attr('action', actionUrl);
    });

    // --- KODE AJAX UNTUK FILTER REAL-TIME ---

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function fetchJurnal(page = 1) {
        let formData = $('#filterForm').serialize();
        formData = formData.replace(/&?page=\d+/, '');
        
        let url = '{{ route("jurnal-umum.index") }}';
        let fullUrl = url + '?page=' + page + '&' + formData;

        $('#jurnalTableContainer').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Memuat data...</p></div>');

        $.ajax({
            url: url,
            type: 'GET',
            data: 'page=' + page + '&' + formData,
            dataType: 'html',
            success: function(response) {
                $('#jurnalTableContainer').html(response);
                
                let headerInfo = $(response).filter('.small').clone();
                let headerBadge = $(response).filter('#statusBadge').clone();
                
                $('#jurnalHeader .small, #jurnalHeader .badge').remove();
                $('#jurnalHeader').append(headerInfo).append(headerBadge);

                history.pushState(null, '', fullUrl);
            },
            error: function(xhr) {
                $('#jurnalTableContainer').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                console.log(xhr.responseText);
            }
        });
    }

    $('#filterForm select, #filterForm input[type=date]').on('change', function() {
        fetchJurnal();
    });

    $('#filterForm input[name=search]').on('keyup', debounce(function() {
        fetchJurnal();
    }, 500));

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        fetchJurnal();
    });

    $(document).on('click', '#jurnalTableContainer .pagination a', function(e) {
        e.preventDefault();
        let pageUrl = $(this).attr('href');
        let page = new URL(pageUrl).searchParams.get("page");
        fetchJurnal(page);
    });

    // ==========================================================
    // ===== TAMBAHAN KODE PERBAIKAN UNTUK MODAL PRINT =====
    // ==========================================================
    // Event ini berjalan SETIAP KALI modal print akan ditampilkan
    $('#printModal').on('show.bs.modal', function () {
        
        // 1. Ambil nilai TERBARU dari form filter (#filterForm)
        var search = $('#filterForm input[name="search"]').val();
        var year = $('#filterForm select[name="year"]').val();
        var status = $('#filterForm select[name="approval_status"]').val();
        var startDate = $('#filterForm input[name="start_date"]').val();
        var endDate = $('#filterForm input[name="end_date"]').val();
        
        // Cek apakah elemen unit_usaha_id ada
        var unitUsahaEl = $('#filterForm select[name="unit_usaha_id"]');
        var unitUsaha = unitUsahaEl.length ? unitUsahaEl.val() : '';

        // 2. Temukan modal ini
        var modal = $(this);

        // 3. Masukkan nilai terbaru itu ke hidden input di DALAM MODAL
        modal.find('input[name="search"]').val(search);
        modal.find('input[name="year"]').val(year);
        modal.find('input[name="approval_status"]').val(status);
        modal.find('input[name="start_date"]').val(startDate);
        modal.find('input[name="end_date"]').val(endDate);
        modal.find('input[name="unit_usaha_id"]').val(unitUsaha);
   });
    // ==========================================================
    // ============ BATAS KODE PERBAIKAN ============
    // ==========================================================
});
</script>
@stop