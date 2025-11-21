@extends('adminlte::page')

@section('title', 'Setor Tunai')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Catat Setoran Tunai</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- CARD PENCARIAN ANGGOTA --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">1. Cari Anggota / Pilih Rekening</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="search_member">Cari Anggota (NIK / Nama)</label>
                        {{-- ID 'search_member' ini akan digunakan untuk fitur AJAX --}}
                        <input type="text" id="search_member" class="form-control" placeholder="Ketik NIK atau Nama Anggota...">
                    </div>

                    {{-- AREA HASIL PENCARIAN / PILIHAN REKENING --}}
                    <div id="rekening_selection_area" class="mt-3">
                        <p class="text-muted">Hasil pencarian akan muncul di sini. Harap pilih salah satu rekening untuk melakukan setoran.</p>
                        {{-- Contoh: Jika Anggota A punya Rekening Wajib dan Sukarela, muncul di sini --}}
                        {{--
                        <select class="form-control" id="selected_rekening_id">
                            <option value="123" data-saldo="150000">Rekening Wajib (Saldo: Rp 150.000)</option>
                            <option value="456" data-saldo="500000">Rekening Sukarela (Saldo: Rp 500.000)</option>
                        </select>
                        --}}
                    </div>
                </div>
            </div>

            {{-- CARD FORM TRANSAKSI SETORAN (Tersembunyi sampai rekening dipilih) --}}
            <div id="transaction_form_card" class="card card-success" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">2. Form Setoran</h3>
                </div>
                <form action="{{ route('simpanan.setor.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="alert alert-info" id="saldo_info_display">
                           Rekening terpilih: **<span id="rekening_name_display"></span>** | Saldo Saat Ini: **Rp <span id="current_saldo_display">0</span>**
                        </div>

                        {{-- Hidden input untuk mengirimkan ID Rekening ke Controller --}}
                        <input type="hidden" name="rekening_id" id="rekening_id_input">

                        <div class="form-group">
                            <label for="jumlah">Jumlah Setoran (Rp)</label>
                            <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" placeholder="Contoh: 100000" value="{{ old('jumlah') }}" required>
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
                        <button type="submit" class="btn btn-success"><i class="fas fa-arrow-down"></i> Catat Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // --- LOGIKA JAVASCRIPT/AJAX SIMPLIFIED ---

        // Asumsi: Kita sudah punya endpoint API yang mengembalikan daftar rekening anggota
        // Berdasarkan NIK/Nama yang diinput di #search_member
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
    </script>
@stop
