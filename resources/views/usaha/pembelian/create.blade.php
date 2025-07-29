@extends('adminlte::page')

@section('title', 'Transaksi Pembelian Baru')

@section('content_header')
    <h1>Transaksi Pembelian Baru</h1>
@stop

@section('content')
<form action="{{ route('pembelian.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informasi Pembelian</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="tanggal_pembelian">Tanggal Pembelian</label>
                            <input type="date" class="form-control" name="tanggal_pembelian" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="pemasok_id">Pemasok</label>
                            <select name="pemasok_id" id="pemasok-list" class="form-control" required>
                                <option value="">-- Pilih Pemasok --</option>
                                @foreach($pemasoks as $pemasok)
                                    <option value="{{ $pemasok->pemasok_id }}">{{ $pemasok->nama_pemasok }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="no_faktur">No. Faktur dari Pemasok</label>
                            <input type="text" class="form-control" name="no_faktur" placeholder="Nomor faktur (opsional)">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="status_pembelian">Status Pembelian</label>
                            <select name="status_pembelian" class="form-control" required>
                                <option value="Lunas">Lunas (Tunai)</option>
                                <option value="Belum Lunas">Belum Lunas (Kredit)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Produk</h3>
                </div>
                <div class="card-body">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-6">
                            <label>Pilih Produk</label>
                            <select id="produk-list" class="form-control">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($produks as $produk)
                                    <option value="{{ $produk->produk_id }}" data-harga-beli="{{ $produk->harga_beli }}">{{ $produk->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <button type="button" id="tambah-produk" class="btn btn-success btn-block">Tambah</button>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th style="width: 15%">Jumlah</th>
                                <th style="width: 20%">Harga Beli Satuan</th>
                                <th style="width: 20%">Subtotal</th>
                                <th style="width: 5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-pembelian">
                            {{-- Baris produk akan ditambahkan di sini oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total</th>
                                <th><span id="total-pembelian">Rp 0</span></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#pemasok-list, #produk-list').select2();

    function hitungTotal() {
        let total = 0;
        $('#detail-pembelian tr').each(function() {
            let subtotal = parseFloat($(this).find('.subtotal-text').text().replace(/[^0-9]/g, '')) || 0;
            total += subtotal;
        });
        $('#total-pembelian').text('Rp ' + total.toLocaleString('id-ID'));
    }

    $('#tambah-produk').on('click', function() {
        let produkSelect = $('#produk-list');
        let produkId = produkSelect.val();
        
        if ($('#detail-pembelian').find('input[value="' + produkId + '"]').length > 0) {
            alert('Produk sudah ada di dalam daftar.');
            return;
        }

        if (produkId) {
            let produkNama = produkSelect.find('option:selected').text();
            let produkHargaBeli = produkSelect.find('option:selected').data('harga-beli');

            let newRow = `
                <tr>
                    <td>
                        <input type="hidden" name="produk_id[]" value="${produkId}">
                        ${produkNama}
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="1"></td>
                    <td><input type="number" name="harga_unit[]" class="form-control harga-unit" value="${produkHargaBeli}"></td>
                    <td><span class="subtotal-text">Rp ${parseInt(produkHargaBeli).toLocaleString('id-ID')}</span></td>
                    <td><button type="button" class="btn btn-danger btn-sm hapus-produk">Hapus</button></td>
                </tr>
            `;
            $('#detail-pembelian').append(newRow);
            hitungTotal();
        } else {
            alert('Silakan pilih produk terlebih dahulu.');
        }
    });

    $(document).on('input', '.jumlah, .harga-unit', function() {
        let row = $(this).closest('tr');
        let jumlah = parseInt(row.find('.jumlah').val()) || 0;
        let harga = parseFloat(row.find('.harga-unit').val()) || 0;
        let subtotal = jumlah * harga;
        row.find('.subtotal-text').text('Rp ' + subtotal.toLocaleString('id-ID'));
        hitungTotal();
    });

    $(document).on('click', '.hapus-produk', function() {
        $(this).closest('tr').remove();
        hitungTotal();
    });
});
</script>
@stop