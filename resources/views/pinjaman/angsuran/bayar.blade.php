@extends('adminlte::page')

@section('title', 'Bayar Angsuran')

@section('content_header')
    <h1><i class="fas fa-cash-register"></i> Catat Pembayaran Angsuran</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">

            {{-- Info Card --}}
            <div class="card card-widget widget-user-2">
                <div class="widget-user-header bg-info">
                    <div class="widget-user-image">
                        <img class="img-circle elevation-2" src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="User Avatar">
                    </div>
                    <h3 class="widget-user-username">{{ $angsuran->pinjaman->anggota->nama_lengkap }}</h3>
                    <h5 class="widget-user-desc">No Pinjaman: {{ $angsuran->pinjaman->no_pinjaman }}</h5>
                </div>
                <div class="card-footer p-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <span class="nav-link">
                                Angsuran Ke <span class="float-right badge bg-primary">{{ $angsuran->angsuran_ke }}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                Jatuh Tempo <span class="float-right text-danger">{{ \Carbon\Carbon::parse($angsuran->tanggal_jatuh_tempo)->format('d M Y') }}</span>
                            </span>
                        </li>
                        {{--
                            BAGIAN INI DIHAPUS/DIKOMENTARI KARENA AKAN KITA PINDAH KE FORM DI BAWAH
                            SUPAYA BISA DIEDIT NILAINYA
                        --}}
                        {{--
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Total Tagihan</strong> <span class="float-right text-bold text-dark">Rp {{ number_format($angsuran->jumlah_bayar) }}</span>
                            </span>
                        </li>
                        --}}
                    </ul>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Konfirmasi Pembayaran</h3>
                </div>

                {{-- Gunakan angsuran_id pada parameter route --}}
                <form action="{{ route('simpanan.angsuran.store', $angsuran->angsuran_id) }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- 1. INPUT NOMINAL BAYAR (BARU) --}}
                        <div class="form-group">
                            <label for="nominal_bayar">Nominal Yang Dibayarkan (Rp)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                {{-- Value default diambil dari tagihan seharusnya, tapi user BISA MENGGANTINYA --}}
                                <input type="number" name="nominal_bayar" class="form-control text-bold @error('nominal_bayar') is-invalid @enderror"
                                       value="{{ old('nominal_bayar', $angsuran->jumlah_bayar) }}" required>
                            </div>
                            <small class="text-muted">Tagihan seharusnya: Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}. Silakan ubah jika nasabah membayar jumlah berbeda.</small>
                            @error('nominal_bayar') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- 2. INPUT TANGGAL --}}
                        <div class="form-group">
                            <label for="tanggal_bayar">Tanggal Pembayaran</label>
                            <input type="date" name="tanggal_bayar" class="form-control @error('tanggal_bayar') is-invalid @enderror"
                                   value="{{ old('tanggal_bayar', now()->toDateString()) }}" required>
                            @error('tanggal_bayar') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- 3. INPUT KETERANGAN --}}
                        <div class="form-group">
                            <label for="keterangan">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Transfer via Bank BCA / Tunai / Bayar Sebagian">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('simpanan.pinjaman.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-success float-right"><i class="fas fa-check"></i> Simpan Pembayaran</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@stop
