@extends('adminlte::page')

@section('title', 'Edit Jurnal')

@section('content_header')
    <h1>Edit Jurnal Umum</h1>
@stop

@section('content')
<form action="{{ route('jurnal-umum.update', $jurnal->jurnal_id) }}" method="POST">
    @csrf
    @method('PUT')
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
            <div class="row">
                {{-- Tanggal Transaksi --}}
                <div class="form-group col-md-4">
                    <label>Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="tanggal_transaksi" 
                        value="{{ old('tanggal_transaksi', \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('Y-m-d')) }}" required>
                </div>

                {{-- Unit Usaha --}}
                <div class="form-group col-md-4">
                    <label>Untuk Unit Usaha</label>
                    @php $user = auth()->user(); @endphp
                    @if($user->hasRole(['bendahara_bumdes','admin_bumdes']))
                        <select name="unit_usaha_id" class="form-control">
                            <option value="">-- BUMDes Pusat --</option>
                            @foreach($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}" 
                                    {{ old('unit_usaha_id', $jurnal->unit_usaha_id) == $unit->unit_usaha_id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $jurnal->unitUsaha->nama_unit ?? 'BUMDes Pusat' }}" disabled>
                        <input type="hidden" name="unit_usaha_id" value="{{ $jurnal->unit_usaha_id }}">
                    @endif
                </div>

                {{-- Deskripsi --}}
                <div class="form-group col-md-4">
                    <label>Deskripsi Utama</label>
                    <input type="text" class="form-control" name="deskripsi" 
                        placeholder="Deskripsi atau keterangan jurnal" 
                        value="{{ old('deskripsi', $jurnal->deskripsi) }}" required>
                </div>
            </div>
            <hr>

            {{-- Detail Jurnal --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 30%">Akun</th>
                        <th style="width: 25%">Keterangan</th>
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
                        <th colspan="2"><span id="status-jurnal" class="badge badge-danger">Tidak Seimbang</span></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" id="simpan-jurnal" class="btn btn-primary" disabled>Update Jurnal</button>
        </div>
    </div>
</form>
@stop

@section('plugins.Select2', true)
@section('js')
<script>
$(document).ready(function() {
    let rowIndex = 0;
    let oldDetails = @json($jurnal->detailJurnals->toArray());
    let akunOptions = @json(
        $akuns->map(fn($a) => [
            'id' => $a->akun_id,
            'text' => "[ {$a->kode_akun} ] {$a->nama_akun}"
        ])
    );

    function generateAkunOptions(selectedId = null) {
        return akunOptions.map(opt => 
            `<option value="${opt.id}" ${selectedId == opt.id ? 'selected' : ''}>${opt.text}</option>`
        ).join('');
    }

    function addRow(detail = null) {
        let akunId = detail ? detail.akun_id : '';
        let debit = detail ? detail.debit : 0;
        let kredit = detail ? detail.kredit : 0;
        let keterangan = detail?.keterangan ?? '';

        let newRow = `
            <tr id="row-${rowIndex}">
                <td>
                    <select name="details[${rowIndex}][akun_id]" class="form-control akun-select" required>
                        <option value="">-- Pilih Akun --</option>
                        ${generateAkunOptions(akunId)}
                    </select>
                </td>
                <td><input type="text" name="details[${rowIndex}][keterangan]" class="form-control" value="${keterangan}"></td>
                <td><input type="text" name="details[${rowIndex}][debit]" class="form-control debit" value="${debit}"></td>
                <td><input type="text" name="details[${rowIndex}][kredit]" class="form-control kredit" value="${kredit}"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button></td>
            </tr>
        `;
        $('#jurnal-details').append(newRow);
        $('#row-' + rowIndex + ' .akun-select').select2();
        formatInput($('#row-' + rowIndex + ' .debit')[0]);
        formatInput($('#row-' + rowIndex + ' .kredit')[0]);
        rowIndex++;
    }

    $('#tambah-baris').on('click', function() { addRow(); });

    if (oldDetails.length > 0) {
        oldDetails.forEach(function(detail) { addRow(detail); });
    } else {
        addRow(); addRow();
    }

    calculateTotals();

    $(document).on('click', '.hapus-baris', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    function parseNumber(value) {
        return parseInt(value.replace(/\./g, '')) || 0;
    }

    function calculateTotals() {
        let totalDebit = 0, totalKredit = 0;
        $('#jurnal-details tr').each(function() {
            totalDebit += parseNumber($(this).find('.debit').val());
            totalKredit += parseNumber($(this).find('.kredit').val());
        });
        $('#total-debit').text('Rp ' + totalDebit.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#total-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID', { minimumFractionDigits: 0 }));

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
        input.value = value ? parseInt(value).toLocaleString('id-ID') : '';
    }

    $(document).on('input', '.debit, .kredit', function() {
        formatInput(this);
        let row = $(this).closest('tr');
        if ($(this).hasClass('debit') && parseNumber($(this).val()) > 0) row.find('.kredit').val('');
        if ($(this).hasClass('kredit') && parseNumber($(this).val()) > 0) row.find('.debit').val('');
        calculateTotals();
    });

    $('form').on('submit', function() {
        $('.debit, .kredit').each(function() {
            this.value = parseNumber(this.value);
        });
    });
});
</script>
@stop
