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
                <div class="form-group"><label for="periode_bulan" class="mr-2">Periode:</label><select name="periode_bulan" id="periode_bulan" class="form-control"> @php $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $bulan_terpilih == $i ? 'selected' : '' }}>{{ $nama_bulan[$i] }}</option> @endfor </select></div>
                <div class="form-group mx-2"><select name="periode_tahun" id="periode_tahun" class="form-control"> @for ($tahun = date('Y'); $tahun >= date('Y') - 3; $tahun--) <option value="{{ $tahun }}" {{ $tahun_terpilih == $tahun ? 'selected' : '' }}>{{ $tahun }}</option> @endfor </select></div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                <a href="{{ route('usaha.tagihan.cetak-massal', ['periode_bulan' => $bulan_terpilih, 'periode_tahun' => $tahun_terpilih]) }}" target="_blank" class="btn btn-default ml-2"><i class="fa fa-print"></i> Cetak Semua Bulan Ini</a>
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
                <div class="d-flex align-items-center flex-wrap">
                    <label for="petugas_id" class="mr-2 mb-0">Petugas:</label>
                    <select name="petugas_id" id="petugas_id" class="form-control form-control-sm @error('petugas_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($semua_petugas as $petugas) <option value="{{ $petugas->id }}">{{ $petugas->nama_petugas }}</option> @endforeach
                    </select>
                    @error('petugas_id')<div class="invalid-feedback d-block ml-2">{{$message}}</div>@enderror
                </div>
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
                <table class="table table-bordered" id="tagihan-table">
                    <thead class="thead-light text-center">
                        <tr>
                            <th><input type="checkbox" id="select-all-checkbox"></th>
                            <th>No.</th>
                            <th>Pelanggan</th>
                            <th>Meter Awal</th>
                            <th>Meter Akhir</th>
                            <th>Pemakaian</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_tabel as $index => $baris)
                            <tr class="{{ $baris->tagihan && $baris->tagihan->status_pembayaran == 'Lunas' ? 'table-success' : '' }}" data-pelanggan-id="{{ $baris->pelanggan->id }}">
                                <td class="text-center">@if($baris->tagihan)<input type="checkbox" class="tagihan-checkbox" value="{{ $baris->tagihan->id }}">@endif</td>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $baris->pelanggan->nama }}</td>
                                <td class="text-center meter-awal-cell" data-original-value="{{ $baris->meter_awal }}"><span class="cell-text">{{ $baris->meter_awal }}</span></td>
                                <td class="text-center meter-akhir-cell" data-original-value="{{ $baris->tagihan->meter_akhir ?? '' }}"><span class="cell-text">{{ $baris->tagihan->meter_akhir ?? '-' }}</span></td>
                                <td class="text-center pemakaian-cell">{{ $baris->tagihan->total_pemakaian_m3 ?? '-' }}</td>
                                <td class="text-right">{{ $baris->tagihan ? 'Rp ' . number_format($baris->tagihan->total_harus_dibayar, 0, ',', '.') : '-' }}</td>
                                <td class="text-center status-cell">
                                    @if($baris->tagihan) <span class="badge {{ $baris->tagihan->status_pembayaran == 'Lunas' ? 'badge-success' : 'badge-warning' }}">{{ $baris->tagihan->status_pembayaran }}</span>
                                    @else <span class="badge badge-secondary">Belum Dibuat</span> @endif
                                </td>
                                <td class="text-center action-cell">
                                    @if($baris->tagihan)
                                        <div class="btn-group">
                                            @if($baris->tagihan->status_pembayaran == 'Belum Lunas')
                                                <button type="button" class="btn btn-xs btn-success tandai-lunas-btn" data-id="{{ $baris->tagihan->id }}" title="Tandai Sudah Lunas"><i class="fas fa-check"></i></button>
                                            @endif
                                            <a href="{{ route('usaha.tagihan.show', $baris->tagihan->id) }}" class="btn btn-xs btn-info" title="Lihat/Cetak Struk" target="_blank"><i class="fa fa-print"></i></a>
                                            <button type="button" class="btn btn-xs btn-danger" onclick="if(confirm('Yakin hapus tagihan ini?')) { document.getElementById('delete-form-{{$baris->tagihan->id}}').submit(); }" title="Hapus"><i class="fa fa-trash"></i></button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if($baris->tagihan)
                            <form id="delete-form-{{$baris->tagihan->id}}" action="{{ route('usaha.tagihan.destroy', $baris->tagihan->id) }}" method="POST" style="display: none;"> @csrf @method('DELETE') </form>
                            <form id="lunas-form-{{$baris->tagihan->id}}" action="{{ route('usaha.tagihan.tandaiLunas', $baris->tagihan->id) }}" method="POST" style="display: none;"> @csrf @method('PUT') </form>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
@stop
@section('js')
<script>
$(document).ready(function() {
    // ## Inisialisasi DataTables ##
    const dataTable = $('#tagihan-table').DataTable({
        "paging": true, "lengthChange": true, "searching": true,
        "ordering": true, "info": true, "autoWidth": false, "responsive": true,
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" },
        "columnDefs": [ { "orderable": false, "targets": [0, 8] } ]
    });

    // ## Logika untuk Mode Edit Massal ##
    $('#edit-semua-btn').on('click', function() {
        $(this).hide();
        $('.btn-group').hide();
        $('#lunas-pilihan-btn').hide();
        $('#simpan-semua-btn, #batal-btn').show();
        $('#petugas_id').prop('disabled', false);

        if ($.fn.DataTable.isDataTable('#tagihan-table')) {
            dataTable.destroy();
        }

        $('#tagihan-table tbody tr').each(function() {
            const $row = $(this);
            const pelangganId = $row.data('pelanggan-id');

            // Edit Meter Awal
            const $cellAwal = $row.find('.meter-awal-cell');
            const originalValueAwal = $cellAwal.data('original-value');
            $cellAwal.html(`<input type="number" step="1" name="tagihan[${pelangganId}][meter_awal]" class="form-control form-control-sm text-center" value="${originalValueAwal}" required>`);

            // Edit Meter Akhir
            const $cellAkhir = $row.find('.meter-akhir-cell');
            const originalValueAkhir = $cellAkhir.data('original-value');
            $cellAkhir.html(`<input type="number" step="1" min="${originalValueAwal}" name="tagihan[${pelangganId}][meter_akhir]" class="form-control form-control-sm text-center" value="${originalValueAkhir}">`);
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

    $('#cetak-halaman-btn').on('click', function() {
        const visibleIds = [];
        dataTable.rows({ page: 'current' }).nodes().to$().find('.tagihan-checkbox').each(function() { visibleIds.push($(this).val()); });
        submitPrintForm(visibleIds);
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

    // ## Logika untuk "Tandai Lunas" Per Baris ##
    $('#tagihan-table').on('click', '.tandai-lunas-btn', function() {
        const $button = $(this);
        const tagihanId = $button.data('id');

        if (confirm('Apakah Anda yakin ingin menandai tagihan ini LUNAS?')) {
            const formId = `#lunas-form-${tagihanId}`;
            const url = $(formId).attr('action');

            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: url,
                type: 'POST',
                data: $(formId).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        const $row = $button.closest('tr');

                        // Cara update DataTables yang benar
                        const dataTableRow = dataTable.row($row);
                        const rowData = dataTableRow.data();
                        rowData[7] = '<span class="badge badge-success">Lunas</span>'; // Update kolom status
                        dataTableRow.data(rowData).draw(false);

                        $button.remove(); // Hapus tombol lunas
                        $row.addClass('table-success');
                    } else {
                        toastr.error('Gagal memperbarui status.');
                        $button.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan. Silakan coba lagi.');
                    $button.prop('disabled', false).html('<i class="fas fa-check"></i>');
                }
            });
        }
    });
});
</script>
@stop
