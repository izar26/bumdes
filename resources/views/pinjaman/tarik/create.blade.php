@extends('adminlte::page')

@section('title', 'Tarik Tunai')

@section('content_header')
    <h1><i class="fas fa-minus-circle"></i> Catat Penarikan Tunai</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">1. Cari Anggota / Pilih Rekening</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="search_member">Cari Anggota (NIK / Nama)</label>
                        {{-- Field ini perlu dihubungkan ke AJAX untuk mencari data anggota --}}
                        <input type="text" id="search_member" class="form-control" placeholder="Ketik NIK atau Nama Anggota...">
                    </div>
                    <div id="rekening_selection_area" class="mt-3">
                        <p class="text-muted">Setelah anggota ditemukan, pilih rekening untuk penarikan.</p>
                    </div>
                </div>
            </div>

            {{-- CARD FORM TRANSAKSI PENARIKAN (Tampil setelah rekening dipilih) --}}
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
                            <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" placeholder="Contoh: 50000" value="{{ old('jumlah') }}" required>
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

{{-- Tambahkan script AJAX yang sama dengan file setor/create.blade.php di sini --}}
