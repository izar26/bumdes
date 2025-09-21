@extends('adminlte::page')

@section('title', 'Input & Daftar Tagihan')

@section('plugins.DataTables', true)
@section('plugins.Toastr', true)

@section('content_header')
    <h1 class="m-0 text-dark">Input & Daftar Tagihan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('usaha.tagihan.index') }}" method="GET" class="form-inline">
                <div class="form-group">
                    <label for="periode_bulan" class="mr-2">Periode:</label>
                    <select name="periode_bulan" id="periode_bulan" class="form-control">
                        @php $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan_terpilih == $i ? 'selected' : '' }}>{{ $nama_bulan[$i] }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mx-2">
                    <select name="periode_tahun" id="periode_tahun" class="form-control">
                        @for ($tahun = date('Y'); $tahun >= date('Y') - 3; $tahun--)
                            <option value="{{ $tahun }}" {{ $tahun_terpilih == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group mr-2">
                    <label for="petugas_id" class="mr-2">Petugas:</label>
                    <select name="petugas_id" id="petugas_id" class="form-control">
                        <option value="">-- Semua Petugas --</option>
                        @foreach ($semua_petugas as $petugas)
                            <option value="{{ $petugas->id }}" {{ $petugas_terpilih == $petugas->id ? 'selected' : '' }}>{{ $petugas->nama_petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
            </form>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif
    @if(session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif

    <form action="{{ route('usaha.tagihan.simpanSemuaMassal') }}" method="POST">
        @csrf
        <input type="hidden" name="periode_tagihan" value="{{ \Carbon\Carbon::create($tahun_terpilih, $bulan_terpilih, 1)->toDateString() }}">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div id="action-buttons" class="mt-2 mt-md-0">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-info" id="cetak-pilihan-btn"><i class="fas fa-check-square"></i> Cetak Pilihan</button>
                        <button type="button" class="btn btn-sm btn-success" id="lunas-pilihan-btn"><i class="fas fa-money-check-alt"></i> Lunas Pilihan</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-warning ml-2" id="edit-semua-btn"><i class="fas fa-edit"></i> Edit Semua</button>
                    <button type="submit" class="btn btn-sm btn-success" id="simpan-semua-btn" style="display: none;"><i class="fas fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="batal-btn" style="display: none;"><i class="fas fa-times"></i> Batal</button>
                </div>
            </div>
            <div class="card-body">
                {{-- Dropdown untuk Petugas Massal --}}
                <div id="mass-edit-petugas-container" style="display: none;" class="mb-3">
                    <div class="form-group row mb-0">
                        <label for="petugas_massal_id" class="col-form-label col-sm-2">Petugas Pengisi:</label>
                        <div class="col-sm-4">
                            <select name="petugas_massal_id" id="petugas_massal_id" class="form-control form-control-sm" required>
                                <option value="">-- Pilih Petugas --</option>
                                @foreach ($semua_petugas as $petugas)
                                    <option value="{{ $petugas->id }}" {{ $petugas_terpilih == $petugas->id ? 'selected' : '' }}>{{ $petugas->nama_petugas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{-- Akhir Dropdown untuk Petugas Massal --}}

                <table class="table table-bordered" id="tagihan-table">
                    <thead class="thead-light text-center">
                        <tr>
                            <th><input type="checkbox" id="select-all-checkbox"></th>
                            <th>No.</th>
                            <th>Pelanggan</th>
                            <th class="petugas-header">Petugas</th>
                            <th>Meter Awal</th>
                            <th>Meter Akhir</th>
                            <th>Pemakaian</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_tabel as $index => $baris)
                            @php
                                $statusClass = '';
                                if ($baris->tagihan) {
                                    if ($baris->tagihan->status_pembayaran == 'Lunas') {
                                        $statusClass = 'table-success';
                                    } elseif ($baris->tagihan->status_pembayaran == 'Batal') {
                                        $statusClass = 'table-secondary';
                                    }
                                }
                            @endphp
                            <tr class="{{ $statusClass }}" data-pelanggan-id="{{ $baris->pelanggan->id }}">
                                <td class="text-center">@if($baris->tagihan && $baris->tagihan->status_pembayaran != 'Batal')<input type="checkbox" class="tagihan-checkbox" value="{{ $baris->tagihan->id }}">@endif</td>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $baris->pelanggan->nama }}</td>
                                <td class="petugas-cell">
                                    <span class="cell-text">
                                        {{ $baris->tagihan->petugas->nama_petugas ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-center meter-awal-cell" data-original-value="{{ $baris->meter_awal }}"><span class="cell-text">{{ $baris->meter_awal }}</span></td>
                                <td class="text-center meter-akhir-cell" data-original-value="{{ $baris->tagihan->meter_akhir ?? '' }}"><span class="cell-text">{{ $baris->tagihan->meter_akhir ?? '-' }}</span></td>
                                <td class="text-center pemakaian-cell">{{ $baris->tagihan->total_pemakaian_m3 ?? '-' }}</td>
                                <td class="text-right">{{ $baris->tagihan ? 'Rp ' . number_format($baris->tagihan->total_harus_dibayar, 0, ',', '.') : '-' }}</td>
                                <td class="text-center status-cell">
                                    @if($baris->tagihan)
                                        @if($baris->tagihan->status_pembayaran == 'Lunas')
                                            <span class="badge badge-success">Lunas</span>
                                        @elseif($baris->tagihan->status_pembayaran == 'Batal')
                                            <span class="badge badge-secondary">Dibatalkan</span>
                                        @else
                                            <span class="badge badge-warning">Belum Lunas</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">Belum Dibuat</span>
                                    @endif
                                </td>
                               <td class="text-center action-cell">
                                   @if($baris->tagihan)
                                       <div class="btn-group">
                                           @if($baris->tagihan->status_pembayaran == 'Belum Lunas')
                                               <button type="button" class="btn btn-xs btn-success tandai-lunas-btn"
                                                       data-toggle="modal"
                                                       data-target="#confirmModal"
                                                       data-title="Konfirmasi Pelunasan Tagihan"
                                                       data-body="Apakah Anda yakin ingin menandai tagihan untuk **{{ $baris->pelanggan->nama }}** sebagai **LUNAS**?"
                                                       data-form-id="lunas-form-{{$baris->tagihan->id}}"
                                                       data-button-text="Tandai Lunas"
                                                       data-button-class="btn-success"
                                                       title="Tandai Sudah Lunas">
                                                   <i class="fas fa-check"></i>
                                               </button>
                                           @endif
                                           <a href="{{ route('usaha.tagihan.show', $baris->tagihan->id) }}" class="btn btn-xs btn-info" title="Lihat/Cetak Struk" target="_blank"><i class="fa fa-print"></i></a>

                                           {{-- TOMBOL BARU UNTUK MEMBATALKAN TAGIHAN --}}
                                           @if($baris->tagihan->status_pembayaran != 'Lunas' && $baris->tagihan->status_pembayaran != 'Batal')
                                           <button type="button" class="btn btn-xs btn-warning batal-btn"
                                                   data-toggle="modal"
                                                   data-target="#confirmModal"
                                                   data-title="Konfirmasi Pembatalan Tagihan"
                                                   data-body="Apakah Anda yakin ingin **membatalkan** tagihan untuk **{{ $baris->pelanggan->nama }}**? Tagihan ini tidak akan masuk dalam laporan keuangan."
                                                   data-form-id="batal-form-{{$baris->tagihan->id}}"
                                                   data-button-text="Batalkan Tagihan"
                                                   data-button-class="btn-warning"
                                                   title="Batalkan Tagihan">
                                               <i class="fa fa-times"></i>
                                           </button>
                                           @endif
                                           {{-- AKHIR TOMBOL BARU --}}

                                           <button type="button" class="btn btn-xs btn-danger delete-btn"
                                                   data-toggle="modal"
                                                   data-target="#confirmModal"
                                                   data-title="Konfirmasi Penghapusan Tagihan"
                                                   data-body="Apakah Anda yakin ingin menghapus tagihan untuk **{{ $baris->pelanggan->nama }}** secara permanen? Aksi ini tidak bisa dikembalikan."
                                                   data-form-id="delete-form-{{$baris->tagihan->id}}"
                                                   data-button-text="Hapus Permanen"
                                                   data-button-class="btn-danger"
                                                   title="Hapus">
                                               <i class="fa fa-trash"></i>
                                           </button>
                                       </div>
                                   @endif
                               </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- MODAL DAN FORM AKSI --}}
    @foreach($data_tabel as $baris)
        @if($baris->tagihan)
            <form id="delete-form-{{$baris->tagihan->id}}" action="{{ route('usaha.tagihan.destroy', $baris->tagihan->id) }}" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
            <form id="lunas-form-{{$baris->tagihan->id}}" action="{{ route('usaha.tagihan.tandaiLunas', $baris->tagihan->id) }}" method="POST" style="display:none;">
                @csrf
                @method('PUT')
            </form>
            <form id="batal-form-{{$baris->tagihan->id}}" action="{{ route('usaha.tagihan.batalkanTagihan', $baris->tagihan->id) }}" method="POST" style="display:none;">
                @csrf
                @method('PUT')
            </form>
        @endif
    @endforeach

    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger confirm-action-btn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // ## Inisialisasi DataTables ##
        const dataTable = $('#tagihan-table').DataTable({
            "paging": true, "lengthChange": true, "searching": true,
            "ordering": true, "info": true, "autoWidth": false, "responsive": true,
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" },
            "columnDefs": [ { "orderable": false, "targets": [0, 9] } ]
        });

        // ## Logika untuk Modal Konfirmasi (Hapus, Lunas, dan Batal) ##
        $('#confirmModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            const title = button.data('title') || 'Konfirmasi';
            const body = button.data('body') || 'Apakah Anda yakin?';
            const formId = button.data('form-id');
            const confirmButtonText = button.data('button-text') || 'Ya';
            const confirmButtonClass = button.data('button-class') || 'btn-danger';

            modal.find('.modal-title').html(title);
            modal.find('.modal-body').html(body);

            const confirmButton = modal.find('.confirm-action-btn');
            confirmButton.attr('class', 'btn ' + confirmButtonClass + ' confirm-action-btn');
            confirmButton.text(confirmButtonText);

            confirmButton.off('click').on('click', function(e) {
                e.preventDefault();
                const $form = $('#' + formId);
                if ($form.length === 0) {
                    toastr.error('Form tidak ditemukan.');
                    modal.modal('hide');
                    return;
                }
                $form.submit();
            });
        });

        // ## Logika untuk Mode Edit Massal ##
        $('#edit-semua-btn').on('click', function() {
            $(this).hide();
            $('.btn-group').hide();
            $('#lunas-pilihan-btn').hide();
            $('#simpan-semua-btn, #batal-btn, #mass-edit-petugas-container').show();

            // Destroy DataTables instance to allow editing
            if ($.fn.DataTable.isDataTable('#tagihan-table')) {
                dataTable.destroy();
            }

            $('#tagihan-table tbody tr').each(function() {
                const $row = $(this);
                const pelangganId = $row.data('pelanggan-id');
                const originalValueAwal = $row.find('.meter-awal-cell').data('original-value');
                const originalValueAkhir = $row.find('.meter-akhir-cell').data('original-value');

                // Hide petugas cell text
                $row.find('.petugas-cell .cell-text').hide();

                // Edit Meter Awal
                $row.find('.meter-awal-cell').html(`<input type="number" step="1" name="tagihan[${pelangganId}][meter_awal]" class="form-control form-control-sm text-center" value="${originalValueAwal}" required>`);

                // Edit Meter Akhir
                $row.find('.meter-akhir-cell').html(`<input type="number" step="1" min="${originalValueAwal}" name="tagihan[${pelangganId}][meter_akhir]" class="form-control form-control-sm text-center" value="${originalValueAkhir}">`);
            });
        });

        $('#batal-btn').on('click', function() { location.reload(); });

        // ## Logika untuk Cetak Selektif ##
        function submitPrintForm(ids) {
            if (ids.length === 0) {
                toastr.error('Tidak ada tagihan yang dipilih untuk dicetak.');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("usaha.tagihan.cetak-selektif") }}';
            form.target = '_blank';
            form.innerHTML = `@csrf ${ids.map(id => `<input type="hidden" name="tagihan_ids[]" value="${id}">`).join('')}`;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        $('#cetak-pilihan-btn').on('click', function() {
            const selectedIds = [];
            $('.tagihan-checkbox:checked').each(function() { selectedIds.push($(this).val()); });
            submitPrintForm(selectedIds);
        });

        $('#select-all-checkbox').on('click', function() {
            const isChecked = this.checked;
            dataTable.rows({ page: 'current' }).nodes().to$().find('.tagihan-checkbox').prop('checked', isChecked);
        });

        // ## Logika untuk Tandai Lunas Selektif ##
        $('#lunas-pilihan-btn').on('click', function() {
            const selectedIds = [];
            $('.tagihan-checkbox:checked').each(function() { selectedIds.push($(this).val()); });
            if (selectedIds.length === 0) {
                toastr.error('Tidak ada tagihan yang dipilih.');
                return;
            }
            if (confirm(`Apakah Anda yakin ingin menandai ${selectedIds.length} tagihan sebagai LUNAS?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("usaha.tagihan.tandaiLunasSelektif") }}';
                form.innerHTML = `@csrf ${selectedIds.map(id => `<input type="hidden" name="tagihan_ids[]" value="${id}">`).join('')}`;
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        });
    });
</script>
@stop
