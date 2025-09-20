@extends('adminlte::page')

@section('title', 'Buat Jurnal Baru')

@section('content_header')
    <h1>Buat Jurnal Umum Baru</h1>
@stop

@section('content')
<form action="{{ route('jurnal-manual.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Jurnal</h3>
            <div class="card-tools">
                <button type="button" id="tambah-baris" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="row">
                {{-- Tanggal Transaksi --}}
                <div class="form-group col-md-4">
                    <label>Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="tanggal_transaksi"
                           value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" required>
                </div>

                {{-- Unit Usaha --}}
                <div class="form-group col-md-4">
                    <label>Untuk Unit Usaha</label>
                    @php 
                        $user = auth()->user();
                        $firstUnit = $unitUsahas->first(); 
                    @endphp
                    @if($user->hasAnyRole(['bendahara_bumdes','admin_bumdes', 'direktur_bumdes', 'sekretaris_bumdes']))
                        <select name="unit_usaha_id" class="form-control">
                            <option value="">-- BUMDes Pusat --</option>
                            @foreach($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}"
                                    {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    @elseif($unitUsahas->count() > 1)
                        <select name="unit_usaha_id" class="form-control" required>
                            <option value="">-- Pilih Unit Usaha --</option>
                            @foreach($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}"
                                    {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $firstUnit->nama_unit ?? 'Tidak Punya Unit Usaha' }}" disabled>
                        <input type="hidden" name="unit_usaha_id" value="{{ $firstUnit->unit_usaha_id ?? '' }}">
                    @endif
                </div>

                {{-- Deskripsi --}}
                <div class="form-group col-md-4">
                    <label>Deskripsi Utama</label>
                    <input type="text" class="form-control" name="deskripsi"
                           placeholder="Deskripsi atau keterangan jurnal"
                           value="{{ old('deskripsi') }}" required>
                </div>
            </div>
            <hr>

            {{-- Detail Jurnal --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 35%">Akun</th>
                        <th style="width: 20%">Keterangan</th>
                        <th style="width: 15%">Debit</th>
                        <th style="width: 15%">Kredit</th>
                        <th style="width: 5%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="jurnal-details"></tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-right">Total</th>
                        <th><span id="total-debit">Rp 0</span></th>
                        <th><span id="total-kredit">Rp 0</span></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-right">Status</th>
                        <th colspan="2">
                            <span id="status-jurnal" class="badge badge-danger">Tidak Seimbang</span>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" id="simpan-jurnal" class="btn btn-primary" disabled>Simpan Jurnal</button>
        </div>
    </div>
</form>
@stop

@section('plugins.Select2', true)
@section('js')
<script>
$(document).ready(function() {
    let rowIndex = 0;
    // Menyimpan data akun dari PHP ke variabel JS
    const akunsData = @json($akuns->keyBy('akun_id'));

    function addRow() {
        let optionsHtml = '<option value="">-- Pilih Akun --</option>';
        @foreach($akuns as $akun)
            // --- PERUBAHAN TAMPILAN DIMULAI DI SINI ---
            // Menambahkan tipe akun sebagai petunjuk
            optionsHtml += `<option value="{{ $akun->akun_id }}" data-tipe="{{ $akun->tipe_akun }}">
                                [ {{ $akun->kode_akun }} ] {{ $akun->nama_akun }} - ({{ $akun->tipe_akun }})
                            </option>`;
            // --- AKHIR PERUBAHAN TAMPILAN ---
        @endforeach

        let newRow = `
            <tr id="row-${rowIndex}">
                <td>
                    <select name="details[${rowIndex}][akun_id]" class="form-control akun-select" required>${optionsHtml}</select>
                </td>
                <td><input type="text" name="details[${rowIndex}][keterangan]" class="form-control" placeholder="Ket. baris (opsional)"></td>
                <td><input type="text" name="details[${rowIndex}][debit]" class="form-control debit" value="0"></td>
                <td><input type="text" name="details[${rowIndex}][kredit]" class="form-control kredit" value="0"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button>
                </td>
            </tr>
        `;
        $('#jurnal-details').append(newRow);
        $('#row-' + rowIndex + ' .akun-select').select2({
            placeholder: '-- Pilih Akun --'
        });
        rowIndex++;
    }

    $('#tambah-baris').on('click', addRow);
    
    // Memuat ulang baris lama jika ada validation error
    let oldDetails = @json(old('details', []));
    if (oldDetails.length > 0) {
        oldDetails.forEach(function(detail, index) {
            addRow();
            let lastRow = $('#jurnal-details tr').last();
            lastRow.find('.akun-select').val(detail.akun_id).trigger('change');
            lastRow.find('[name$="[keterangan]"]').val(detail.keterangan);
            lastRow.find('.debit').val(detail.debit);
            lastRow.find('.kredit').val(detail.kredit);
        });
    } else {
        // Mulai dengan dua baris jika tidak ada data lama
        addRow();
        addRow();
    }


    $(document).on('click', '.hapus-baris', function() {
        if ($('#jurnal-details tr').length > 2) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            alert('Jurnal harus memiliki minimal 2 baris detail.');
        }
    });

    function parseNumber(value) {
        return parseInt(String(value).replace(/[^\d]/g, '')) || 0;
    }

    function calculateTotals() {
        let totalDebit = 0, totalKredit = 0;
        $('#jurnal-details tr').each(function() {
            totalDebit += parseNumber($(this).find('.debit').val());
            totalKredit += parseNumber($(this).find('.kredit').val());
        });

        $('#total-debit').text('Rp ' + totalDebit.toLocaleString('id-ID'));
        $('#total-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID'));

        if (totalDebit === totalKredit && totalDebit > 0) {
            $('#status-jurnal').removeClass('badge-danger').addClass('badge-success').text('Seimbang');
            $('#simpan-jurnal').prop('disabled', false);
        } else {
            $('#status-jurnal').removeClass('badge-success').addClass('badge-danger').text('Tidak Seimbang');
            $('#simpan-jurnal').prop('disabled', true);
        }
    }

    function formatInput(input) {
        let value = input.value.replace(/\D/g, '');
        input.value = value ? parseInt(value).toLocaleString('id-ID') : '0';
    }

    $(document).on('input', '.debit, .kredit', function() {
        formatInput(this);
        let row = $(this).closest('tr');
        if ($(this).hasClass('debit') && parseNumber($(this).val()) > 0) {
            row.find('.kredit').val('0');
        }
        if ($(this).hasClass('kredit') && parseNumber($(this).val()) > 0) {
            row.find('.debit').val('0');
        }
        calculateTotals();
    });

    $('form').on('submit', function() {
        $('.debit, .kredit').each(function() {
            this.value = parseNumber(this.value);
        });
    });

    calculateTotals();
});
</script>
@stop
