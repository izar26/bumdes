@extends('adminlte::page')

@section('title', 'Tarik Tunai')

@section('content_header')
    <h1><i class="fas fa-minus-circle"></i> Catat Penarikan Tunai</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Menggunakan struktur form AJAX/Pencarian Anggota yang sama dengan Setoran --}}
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">1. Cari Anggota / Pilih Rekening</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="search_member">Cari Anggota (NIK / Nama)</label>
                        <input type="text" id="search_member" class="form-control" placeholder="Ketik NIK atau Nama Anggota...">
                    </div>
                    <div id="rekening_selection_area" class="mt-3">
                        <p class="text-muted">Setelah anggota ditemukan, pilih rekening untuk penarikan.</p>
                    </div>
                </div>
            </div>

            {{-- CARD FORM TRANSAKSI PENARIKAN --}}
            <div id="transaction_form_card" class="card card-danger" style="display: none;">
                <div class="card-header"><h3 class="card-title">2. Form Penarikan</h3></div>
                <form action="{{ route('simpanan.tarik.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="alert alert-info" id="saldo_info_display">
                           Rekening terpilih: **<span id="rekening_name_display"></span>** | Saldo Saat Ini: **Rp <span id="current_saldo_display">0</span>**
                        </div>

                        <input type="hidden" name="rekening_id" id="rekening_id_input">

                        <div class="form-group">
                            <label for="jumlah">Jumlah Penarikan (Rp)</label>
                            <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" placeholder="Pastikan tidak melebihi saldo" value="{{ old('jumlah') }}" required>
                            @error('jumlah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-arrow-up"></i> Catat Penarikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

{{-- Logika JavaScript/AJAX dan validasi untuk penarikan --}}
@section('js')
    <script>
        // --- LOGIKA JAVASCRIPT/AJAX ---

        // Asumsi: Kita sudah punya endpoint API yang mengembalikan daftar rekening anggota
        const API_ENDPOINT = '/api/simpanan/search-rekening';

        // 1. Logika untuk menampilkan form transaksi setelah rekening dipilih
        $(document).on('change', '#selected_rekening_id', function() {
            const rekeningId = $(this).val();
            const saldo = $(this).find(':selected').data('saldo');
            const rekeningName = $(this).find(':selected').text();

            if (rekeningId) {
                // Tampilkan form transaksi
                $('#transaction_form_card').slideDown();

                // Isi hidden input dan informasi saldo
                $('#rekening_id_input').val(rekeningId);
                $('#current_saldo_display').text(saldo.toLocaleString('id-ID'));
                $('#rekening_name_display').text(rekeningName);

            } else {
                $('#transaction_form_card').slideUp();
            }
        });

        // 2. Logika pencarian anggota via AJAX ke API
        $('#search_member').on('keyup', function() {
            const query = $(this).val();

            if (query.length > 2) {
                $.get(API_ENDPOINT, {q: query}, function(response) {
                    if (response.success && response.data.length > 0) {
                        // Build select options from response data
                        let selectHtml = '<p class="text-success">Ditemukan ' + response.data.length + ' rekening. Silakan pilih:</p>\n';
                        selectHtml += '<select class="form-control" id="selected_rekening_id">\n';
                        selectHtml += '    <option value="">-- Pilih Rekening --</option>\n';

                        response.data.forEach(function(rekening) {
                            const label = rekening.anggota_nama + ' - ' + rekening.no_rekening + ' (' + rekening.jenis_simpanan + ') - Saldo: Rp ' + rekening.saldo.toLocaleString('id-ID');
                            selectHtml += '    <option value="' + rekening.rekening_id + '" data-saldo="' + rekening.saldo + '">' + label + '</option>\n';
                        });

                        selectHtml += '</select>\n';
                        $('#rekening_selection_area').html(selectHtml);
                    } else {
                        $('#rekening_selection_area').html('<p class="text-warning">Tidak ada rekening ditemukan.</p>');
                    }
                }).fail(function() {
                    $('#rekening_selection_area').html('<p class="text-danger">Terjadi kesalahan saat mencari rekening.</p>');
                });

            } else if (query.length === 0) {
                // Bersihkan tampilan jika input kosong
                $('#rekening_selection_area').html('<p class="text-muted">Hasil pencarian akan muncul di sini. Minimal 3 karakter.</p>');
                $('#transaction_form_card').slideUp();
            }
        });

        // 3. Validasi jumlah penarikan (tidak boleh melebihi saldo)
        $('#jumlah').on('change', function() {
            const jumlah = parseInt($(this).val()) || 0;
            const saldo = parseInt($('#current_saldo_display').text().replace(/\./g, '')) || 0;

            if (jumlah > saldo) {
                alert('Jumlah penarikan melebihi saldo! Saldo tersedia: Rp ' + saldo.toLocaleString('id-ID'));
                $(this).val('');
            }
        });
    </script>
@endsection

