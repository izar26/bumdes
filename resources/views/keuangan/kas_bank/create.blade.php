{{-- resources/views/keuangan/kas_bank/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Tambah Akun Kas & Bank')

@section('content_header')
    <h1>Tambah Akun Kas & Bank</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Tambah Akun</h3>
    </div>
    <form action="{{ route('kas-bank.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada masalah dengan inputan Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="nama_akun_kas_bank">Nama Akun (Contoh: Kas Toko, Bank BRI Unit)</label>
                <input type="text" name="nama_akun_kas_bank" class="form-control" id="nama_akun_kas_bank" placeholder="Masukkan nama yang mudah dikenali" value="{{ old('nama_akun_kas_bank') }}">
            </div>

            <div class="form-group">
                <label for="akun_id">Terhubung ke Akun (dari Daftar Akun)</label>
                <select name="akun_id" class="form-control" id="akun_id">
                    <option value="">-- Pilih Jenis Akun --</option>
                    @foreach ($akun_list as $akun)
                        <option value="{{ $akun->akun_id }}" {{ old('akun_id') == $akun->akun_id ? 'selected' : '' }}>
                           [{{ $akun->kode_akun }}] {{ $akun->nama_akun }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Pilih jenis akun kas/bank yang sesuai dari daftar akun utama Anda.</small>
            </div>
            
            <div class="form-group">
                <label for="nomor_rekening">Nomor Rekening (Opsional)</label>
                <input type="text" name="nomor_rekening" class="form-control" id="nomor_rekening" placeholder="Masukkan nomor rekening jika ini adalah akun bank" value="{{ old('nomor_rekening') }}">
            </div>

            <div class="form-group">
                <label for="saldo_saat_ini">Saldo Awal</label>
                <input type="number" step="0.01" name="saldo_saat_ini" class="form-control" id="saldo_saat_ini" placeholder="Masukkan saldo awal akun" value="{{ old('saldo_saat_ini', 0) }}">
                 <small class="form-text text-muted">Masukkan saldo awal saat akun ini pertama kali didaftarkan di sistem.</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('kas-bank.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop