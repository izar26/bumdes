@extends('adminlte::page')

@section('title', 'Transaksi Penjualan Baru')

@section('content_header')
    <h1>Transaksi Penjualan Baru</h1>
@stop

@section('content')
<form action="{{ route('usaha.penjualan.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informasi Penjualan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="tanggal_penjualan">Tanggal Penjualan</label>
                            <input type="date" class="form-control" name="tanggal_penjualan" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="nama_pelanggan">Nama Pelanggan</label>
                            <input type="text" class="form-control" name="nama_pelanggan" placeholder="Nama pelanggan (opsional)">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="status_penjualan">Status Penjualan</label>
                            <select name="status_penjualan" class="form-control" required>
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
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Pilih Produk</label>
                            <select id="produk-list" class="form-control">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($produks as $produk)
                                    <option value="{{ $produk->produk_id }}" data-harga="{{ $produk->harga_jual }}">{{ $produk->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" id="tambah-produk" class="btn btn-success btn-block">Tambah</button>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th style="width: 15%">Jumlah</th>
                                <th style="width: 20%">Harga Satuan</th>
                                <th style="width: 20%">Subtotal</th>
                                <th style="width: 5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-penjualan">
                            {{-- Baris produk akan ditambahkan di sini oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total</th>
                                <th><span id="total-penjualan">Rp 0</span></th>
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

@section('plugins.Select2', false)

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#produk-list').select2();

    // Fungsi untuk menghitung total keseluruhan
    function hitungTotal() {
        let total = 0;
        $('#detail-penjualan tr').each(function() {
            let subtotal = parseFloat($(this).find('.subtotal-text').text().replace(/[^0-9]/g, '')) || 0;
            total += subtotal;
        });
        $('#total-penjualan').text('Rp ' + total.toLocaleString('id-ID'));
    }

    // Event handler saat tombol "Tambah" di klik
    $('#tambah-produk').on('click', function() {
        let produkSelect = $('#produk-list');
        let produkId = produkSelect.val();

        if ($('#detail-penjualan').find('input[value="' + produkId + '"]').length > 0) {
            alert('Produk sudah ada di dalam daftar.');
            return;
        }

        if (produkId) {
            let produkNama = produkSelect.find('option:selected').text();
            let produkHarga = produkSelect.find('option:selected').data('harga'); // Variabel didefinisikan sebagai produkHarga (k kecil)

            // Di bawah ini adalah bagian yang diperbaiki
            let newRow = `
                <tr>
                    <td>
                        <input type="hidden" name="produk_id[]" value="${produkId}">
                        ${produkNama}
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="1"></td>
                    <td><input type="text" name="harga_unit[]" class="form-control harga-unit" value="${parseInt(produkHarga)}" readonly></td>
                    <td><span class="subtotal-text">Rp ${parseInt(produkHarga).toLocaleString('id-ID')}</span></td>
                    <td><button type="button" class="btn btn-danger btn-sm hapus-produk">Hapus</button></td>
                </tr>
            `;
            $('#detail-penjualan').append(newRow);
            hitungTotal();
        } else {
            alert('Silakan pilih produk terlebih dahulu.');
        }
    });

    // Event handler untuk input jumlah (menghitung subtotal)
    $(document).on('input', '.jumlah', function() {
        let row = $(this).closest('tr');
        let jumlah = parseInt($(this).val()) || 0;
        let harga = parseFloat(row.find('.harga-unit').val()) || 0;
        let subtotal = jumlah * harga;
        row.find('.subtotal-text').text('Rp ' + subtotal.toLocaleString('id-ID'));
        hitungTotal();
    });

    // Event handler untuk tombol hapus
    $(document).on('click', '.hapus-produk', function() {
        $(this).closest('tr').remove();
        hitungTotal();
    });
});
</script>
@stop
