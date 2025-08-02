@extends('adminlte::page')

@section('title', 'Buat Jurnal Baru')

@section('content_header')
    <h1>Buat Jurnal Umum Manual</h1>
@stop

@section('content')
<form action="{{ route('jurnal-manual.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Jurnal</h3>
            <div class="card-tools">
                <button type="button" id="tambah-baris" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Baris</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" required>
                </div>

                {{-- LOGIKA BARU UNTUK UNIT USAHA BERDASARKAN PERAN --}}
                <div class="form-group col-md-4">
                    <label>Untuk Unit Usaha</label>
                    <select name="unit_usaha_id" class="form-control" {{ auth()->user()->hasRole('bendahara_bumdes') ? '' : 'readonly' }}>
                        @if(auth()->user()->hasRole('bendahara_bumdes'))
                            {{-- Bendahara bisa memilih semua unit atau BUMDes Pusat --}}
                            <option value="">-- BUMDes Pusat --</option>
                            @foreach ($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        @else
                            {{-- Peran lain hanya bisa memilih unit usahanya sendiri --}}
                            @forelse ($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}" selected>{{ $unit->nama_unit }}</option>
                            @empty
                                <option value="">-- Anda tidak terhubung ke Unit Usaha --</option>
                            @endforelse
                        @endif
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Deskripsi Utama</label>
                    <input type="text" class="form-control" name="deskripsi" placeholder="Deskripsi atau keterangan jurnal" required>
                </div>
            </div>
            <hr>

            <table class="table table-bordered">
                {{-- ... (<thead> dan <tfoot> tabel tidak berubah) ... --}}
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" id="simpan-jurnal" class="btn btn-primary" disabled>Simpan Jurnal</button>
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

    $('#tambah-baris').on('click', function() {
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
                <td><input type="text" name="details[${rowIndex}][keterangan]" class="form-control" placeholder="Ket. baris (opsional)"></td>
                <td><input type="number" name="details[${rowIndex}][debit]" class="form-control debit" value="0" min="0"></td>
                <td><input type="number" name="details[${rowIndex}][kredit]" class="form-control kredit" value="0" min="0"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm hapus-baris">Hapus</button></td>
            </tr>
        `;
        $('#jurnal-details').append(newRow);
        $('#row-' + rowIndex + ' .akun-select').select2();
        rowIndex++;
    });

    $('#tambah-baris').click();
    $('#tambah-baris').click();

    $(document).on('click', '.hapus-baris', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    function calculateTotals() {
        let totalDebit = 0;
        let totalKredit = 0;
        $('#jurnal-details tr').each(function() {
            totalDebit += parseFloat($(this).find('.debit').val()) || 0;
            totalKredit += parseFloat($(this).find('.kredit').val()) || 0;
        });
        $('#total-debit').text('Rp ' + totalDebit.toLocaleString('id-ID'));
        $('#total-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID'));
        let statusBadge = $('#status-jurnal');
        let saveButton = $('#simpan-jurnal');
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
        let debitInput = row.find('.debit');
        let kreditInput = row.find('.kredit');
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