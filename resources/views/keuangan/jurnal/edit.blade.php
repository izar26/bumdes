@extends('adminlte::page')

@section('title', 'Edit Jurnal')

@section('content_header')
    <h1>Edit Jurnal Umum</h1>
@stop

@section('content')
<form action="{{ route('jurnal-umum.update', $jurnal->jurnal_id) }}" method="POST">
    @csrf
    @method('PUT')
    {{-- Kartu atas sekarang kita kosongkan dan bisa dihapus --}}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Jurnal</h3>
            <div class="card-tools">
                <button type="button" id="tambah-baris" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Baris</button>
            </div>
        </div>
        <div class="card-body">
            {{-- Isian Tanggal dan Deskripsi dipindah ke sini --}}
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="tanggal_transaksi">Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', \Carbon\Carbon::parse($jurnal->tanggal_transaksi)->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="deskripsi">Deskripsi Utama</label>
                    <input type="text" class="form-control" name="deskripsi" placeholder="Deskripsi atau keterangan jurnal" value="{{ old('deskripsi', $jurnal->deskripsi) }}" required>
                </div>
            </div>
            <hr>

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
                <tbody id="jurnal-details">
                    {{-- Baris akan diisi oleh JavaScript dari data yang ada --}}
                </tbody>
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

{{-- Bagian @section('js') tidak ada perubahan, jadi tidak perlu disalin ulang --}}
@section('plugins.Select2', true)
@section('js')
<script>
$(document).ready(function() {
    let rowIndex = 0;
    let oldDetails = @json($jurnal->detailJurnals->toArray());

    function addRow(detail = null) {
        let akunId = detail ? detail.akun_id : '';
        let debit = detail ? detail.debit : 0;
        let kredit = detail ? detail.kredit : 0;
        let keterangan = detail && detail.keterangan ? detail.keterangan : '';

        let newRow = `
            <tr id="row-${rowIndex}">
                <td>
                    <select name="details[${rowIndex}][akun_id]" class="form-control akun-select" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($akuns as $akun)
                        <option value="{{ $akun->akun_id }}">[ {{ $akun->kode_akun }} ] {{ $akun->nama_akun }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="details[${rowIndex}][keterangan]" class="form-control" placeholder="Ket. baris (opsional)" value="${keterangan}"></td>
                <td><input type="number" name="details[${rowIndex}][debit]" class="form-control debit" value="${debit}" min="0"></td>
                <td><input type="number" name="details[${rowIndex}][kredit]" class="form-control kredit" value="${kredit}" min="0"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button></td>
            </tr>
        `;
        $('#jurnal-details').append(newRow);
        
        let selectElement = $('#row-' + rowIndex + ' .akun-select');
        selectElement.select2();
        if (akunId) {
            selectElement.val(akunId).trigger('change');
        }
        rowIndex++;
    }

    $('#tambah-baris').on('click', function() { addRow(); });
    if (oldDetails.length > 0) { oldDetails.forEach(function(detail) { addRow(detail); }); } else { addRow(); addRow(); }
    calculateTotals();
    $(document).on('click', '.hapus-baris', function() { $(this).closest('tr').remove(); calculateTotals(); });
    function calculateTotals() {
        let totalDebit = 0; let totalKredit = 0;
        $('#jurnal-details tr').each(function() {
            totalDebit += parseFloat($(this).find('.debit').val()) || 0;
            totalKredit += parseFloat($(this).find('.kredit').val()) || 0;
        });
        $('#total-debit').text('Rp ' + totalDebit.toLocaleString('id-ID'));
        $('#total-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID'));
        let statusBadge = $('#status-jurnal'); let saveButton = $('#simpan-jurnal');
        if (totalDebit === totalKredit && totalDebit > 0) {
            statusBadge.removeClass('badge-danger').addClass('badge-success').text('Seimbang');
            saveButton.prop('disabled', false);
        } else {
            statusBadge.removeClass('badge-success').addClass('badge-danger').text('Tidak Seimbang');
            saveButton.prop('disabled', true);
        }
    }
    $(document).on('input', '.debit, .kredit', function() {
        let row = $(this).closest('tr');
        let debitInput = row.find('.debit'); let kreditInput = row.find('.kredit');
        if ($(this).hasClass('debit') && $(this).val() > 0) {
            kreditInput.val(0);
        } else if ($(this).hasClass('kredit') && $(this).val() > 0) {
            debitInput.val(0);
        }
        calculateTotals();
    });
});
</script>
@stop