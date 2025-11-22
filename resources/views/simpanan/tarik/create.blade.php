@extends('adminlte::page')

@section('title', 'Tarik Tunai')

@section('content_header')
    <h1><i class="fas fa-minus-circle"></i> Catat Penarikan Tunai</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- CARD 1: PILIH REKENING (DROPDOWN) --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">1. Pilih Rekening Sumber</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Cari Anggota / Rekening</label>
                        <select id="pilih_rekening" class="form-control select2" style="width: 100%;">
                            <option value="">-- Ketik Nama / NIK / No Rekening --</option>
                            @foreach($rekenings as $rek)
                                <option value="{{ $rek->rekening_id }}"
                                        data-saldo="{{ $rek->saldo }}"
                                        data-nama="{{ $rek->anggota->nama_lengkap ?? 'Tanpa Nama' }} - {{ $rek->no_rekening }}">
                                    {{ $rek->no_rekening }} - {{ $rek->anggota->nama_lengkap ?? 'Tanpa Nama' }} (Saldo: Rp {{ number_format($rek->saldo, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pastikan saldo mencukupi sebelum memilih.</small>
                    </div>
                </div>
            </div>

            {{-- CARD 2: FORM PENARIKAN --}}
            <div id="transaction_form_card" class="card" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">2. Form Penarikan</h3>
                </div>
                <form action="{{ route('simpanan.tarik.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        <div class="alert alert-warning">
                           <i class="fas fa-user-check"></i> Rekening: <strong><span id="rekening_name_display">-</span></strong>
                           <br>
                           <i class="fas fa-wallet"></i> Saldo Tersedia: <strong>Rp <span id="current_saldo_display">0</span></strong>
                        </div>

                        <input type="hidden" name="rekening_id" id="rekening_id_input">

                        <div class="form-group">
                            <label for="jumlah">Jumlah Penarikan (Rp)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" placeholder="0" value="{{ old('jumlah') }}" required>
                            </div>
                            @error('jumlah') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Keperluan penarikan...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Simpan Penarikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Init Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: "-- Ketik Nama / NIK / No Rekening --",
                allowClear: true
            });

            // 2. Logika Change
            $('#pilih_rekening').on('change', function() {
                let selectedOption = $(this).find(':selected');
                let rekeningId = $(this).val();
                let saldo = selectedOption.data('saldo');
                let namaRekening = selectedOption.data('nama');

                // Reset input jumlah saat ganti rekening
                $('#jumlah').val('');

                if (rekeningId) {
                    $('#rekening_id_input').val(rekeningId);
                    $('#rekening_name_display').text(namaRekening);
                    $('#current_saldo_display').text(new Intl.NumberFormat('id-ID').format(saldo));
                    $('#transaction_form_card').slideDown();
                } else {
                    $('#transaction_form_card').slideUp();
                    $('#rekening_id_input').val('');
                }
            });

            // 3. Validasi Client-Side (Cegah tarik lebih dari saldo)
            $('#jumlah').on('keyup change', function() {
                let inputJumlah = parseFloat($(this).val()) || 0;
                // Ambil saldo dari teks yang ditampilkan, hilangkan titik format ribuan
                let saldoText = $('#current_saldo_display').text().replace(/\./g, '');
                let saldoTersedia = parseFloat(saldoText) || 0;

                if (inputJumlah > saldoTersedia) {
                    // Beri peringatan visual (merah)
                    $(this).addClass('is-invalid');
                    // Opsional: Alert jika ingin lebih agresif
                    // alert('Saldo tidak mencukupi!');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
@stop
