@extends('adminlte::page')
@section('title', 'Buat Transaksi Pembelian')

@section('content_header')
    <h1>Buat Transaksi Pembelian Baru</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Pembelian</h3>
    </div>
    <form action="{{ route('usaha.pembelian.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="tanggal_pembelian">Tanggal Pembelian</label>
                    <input type="date" name="tanggal_pembelian" class="form-control" value="{{ old('tanggal_pembelian', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="pemasok_id">Pemasok</label>
                    <select name="pemasok_id" id="pemasok_id" class="form-control select2" required>
                        <option value="">-- Pilih Pemasok --</option>
                        @foreach($pemasoks as $pemasok)
                            <option value="{{ $pemasok->pemasok_id }}" data-unit-usaha-id="{{ $pemasok->unit_usaha_id }}">
                                {{ $pemasok->nama_pemasok }} ({{ $pemasok->unitUsaha->nama_unit ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status_pembelian">Status Pembelian</label>
                    <select name="status_pembelian" id="status_pembelian" class="form-control" required>
                        <option value="Lunas">Lunas</option>
                        <option value="Belum Lunas">Belum Lunas</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="no_faktur">Nomor Faktur (Opsional)</label>
                <input type="text" name="no_faktur" class="form-control" value="{{ old('no_faktur') }}">
            </div>

            <hr>

            <h4 class="mb-3">Detail Produk</h4>
            <table class="table table-bordered table-striped" id="detail-produk-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Produk</th>
                        <th style="width: 15%;">Jumlah</th>
                        <th style="width: 20%;">Harga Unit</th>
                        <th style="width: 20%;">Subtotal</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="produk-details">
                    </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total Pembelian</strong></td>
                        <td class="text-right"><strong id="total-pembelian">Rp 0</strong></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" id="tambah-produk-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Input tersembunyi untuk unit_usaha_id yang akan divalidasi oleh controller --}}
            <input type="hidden" name="unit_usaha_id" id="unit_usaha_id_hidden">
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            <a href="{{ route('usaha.pembelian.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', false)
@section('js')
<script>
    $(document).ready(function() {
        let produkData = @json($produks->keyBy('produk_id'));
        let rowIndex = 0;

        function addProductRow() {
            let rowId = 'row-' + rowIndex;
            let newRow = `
                <tr id="${rowId}">
                    <td>
                        <select name="produk_id[]" class="form-control produk-select" data-row-index="${rowIndex}" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $produk)
                                <option value="{{ $produk->produk_id }}" data-unit-id="{{ $produk->unit_usaha_id }}">
                                    {{ $produk->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="harga_unit[]" class="form-control harga-unit-input" value="0" required>
                    </td>
                    <td>
                        <span class="subtotal-span">Rp 0</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm hapus-produk-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#produk-details').append(newRow);
            $('#' + rowId + ' .produk-select').select2();
            rowIndex++;
            updateTotals();
        }

        $('#tambah-produk-btn').on('click', function() {
            addProductRow();
        });

        $(document).on('click', '.hapus-produk-btn', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        function formatRupiah(number) {
            return 'Rp ' + Math.round(number).toLocaleString('id-ID');
        }

        function updateTotals() {
            let total = 0;
            let subtotalElement = $('#produk-details').find('.subtotal-span');

            $('#produk-details tr').each(function() {
                let jumlah = $(this).find('.jumlah-input').val();
                let harga = parseNumber($(this).find('.harga-unit-input').val());
                let subtotal = jumlah * harga;
                $(this).find('.subtotal-span').text(formatRupiah(subtotal));
                total += subtotal;
            });

            $('#total-pembelian').text(formatRupiah(total));
        }

        function parseNumber(value) {
            return parseInt(value.replace(/\D/g, '')) || 0;
        }

        $(document).on('input', '.jumlah-input, .harga-unit-input', function() {
            let row = $(this).closest('tr');
            let jumlah = row.find('.jumlah-input').val();
            let harga = row.find('.harga-unit-input').val();

            // Format input harga
            if ($(this).hasClass('harga-unit-input')) {
                let value = $(this).val().replace(/\D/g, '');
                $(this).val(value ? parseInt(value).toLocaleString('id-ID') : '');
            }

            updateTotals();
        });

        // Event listener untuk Pemasok
        $('#pemasok_id').on('change', function() {
            let selectedPemasok = $(this).find('option:selected');
            let unitUsahaId = selectedPemasok.data('unit-usaha-id');
            $('#unit_usaha_id_hidden').val(unitUsahaId);

            // Logika untuk menampilkan produk yang relevan dengan unit usaha terpilih
            let filteredProduks = @json($produks); // Semua produk dari controller

            // Ambil semua produk dan saring berdasarkan unit_usaha_id
            let availableProduks = filteredProduks.filter(function(p) {
                return p.unit_usaha_id == unitUsahaId;
            });

            $('.produk-select').each(function() {
                let currentSelect = $(this);
                currentSelect.find('option').not(':first').remove();
                availableProduks.forEach(function(produk) {
                    currentSelect.append(new Option(produk.nama_produk, produk.produk_id));
                });
            });
        });

        addProductRow();

        // Bersihkan input sebelum submit
        $('form').on('submit', function() {
            $('.harga-unit-input').each(function() {
                this.value = parseNumber(this.value);
            });
            $('.jumlah-input').each(function() {
                this.value = parseInt(this.value);
            });
        });
    });
</script>
@stop
