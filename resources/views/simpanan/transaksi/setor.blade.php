@extends('adminlte::page')

@section('title', 'Setor Tunai')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Catat Setoran Tunai</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Alert Error / Success --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- CARD 1: PILIH REKENING (SEARCHABLE DROPDOWN) --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">1. Cari & Pilih Rekening</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Cari Anggota / Rekening</label>
                        {{-- Dropdown Select2 --}}
                        <select id="pilih_rekening" class="form-control select2" style="width: 100%;">
                            <option value="">-- Ketik Nama / NIK / No Rekening --</option>
                            @forelse($rekenings as $rek)
                                <option value="{{ $rek->rekening_id }}"
                                        data-saldo="{{ $rek->saldo }}"
                                        data-nama="{{ $rek->anggota->nama_lengkap ?? 'Tanpa Nama' }} - {{ $rek->no_rekening }}"
                                        {{ old('rekening_id') == $rek->rekening_id ? 'selected' : '' }}>
                                    {{ $rek->no_rekening }} - {{ $rek->anggota->nama_lengkap ?? 'Tanpa Nama' }} (Saldo: Rp {{ number_format($rek->saldo, 0, ',', '.') }})
                                </option>
                            @empty
                                <option disabled>Data Rekening Kosong</option>
                            @endforelse
                        </select>
                        <small class="text-muted">Ketik nama anggota atau nomor rekening pada kotak di atas untuk mencari.</small>
                    </div>
                </div>
            </div>

            {{-- CARD 2: FORM TRANSAKSI SETORAN --}}
            <div id="transaction_form_card" class="card card-success" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">2. Detail Setoran</h3>
                </div>
                <form action="{{ route('simpanan.setor.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="callout callout-info">
                           <h5><i class="fas fa-user"></i> <span id="rekening_name_display">-</span></h5>
                           <p>Saldo Saat Ini: <strong>Rp <span id="current_saldo_display">0</span></strong></p>
                        </div>

                        {{-- Hidden input yang menyimpan ID Rekening terpilih --}}
                        <input type="hidden" name="rekening_id" id="rekening_id_input" value="{{ old('rekening_id') }}">

                        <div class="form-group">
                            <label for="jumlah">Jumlah Setoran (Rp)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" placeholder="Contoh: 100000" value="{{ old('jumlah') }}" required>
                            </div>
                            @error('jumlah') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan transaksi...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

{{-- PERBAIKAN LINK CSS --}}
@section('css')
    {{-- CSS Select2 Utama --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- CSS Tema Bootstrap 4 (VERSI FIXED: 1.5.2) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <style>
        /* Fix opsional agar dropdown pas dengan AdminLTE */
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
        }
    </style>
@stop

@section('js')
    {{-- JS Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Inisialisasi Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: "-- Ketik Nama / NIK / No Rekening --",
                allowClear: true,
                width: '100%' // Pastikan lebar 100%
            });

            // Fungsi Update UI
            function updateUI(element) {
                let selectedOption = element.find(':selected');
                let rekeningId = element.val();
                let saldo = selectedOption.data('saldo');
                let namaRekening = selectedOption.data('nama');

                if (rekeningId) {
                    $('#rekening_id_input').val(rekeningId);
                    $('#rekening_name_display').text(namaRekening);
                    $('#current_saldo_display').text(new Intl.NumberFormat('id-ID').format(saldo));
                    $('#transaction_form_card').slideDown();
                } else {
                    $('#transaction_form_card').slideUp();
                    $('#rekening_id_input').val('');
                }
            }

            // 2. Event Listener Change
            $('#pilih_rekening').on('change', function() {
                updateUI($(this));
            });

            // 3. Auto Trigger saat reload (Old Input Persistence)
            if ($('#pilih_rekening').val()) {
                updateUI($('#pilih_rekening'));
            }
        });
    </script>
@stop
