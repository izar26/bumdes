{{-- resources/views/keuangan/kas_bank/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detail Transaksi')

@section('content_header')
    <h1>Detail Transaksi: {{ $kasBank->nama_akun_kas_bank }}</h1>
    <h4>Saldo Saat Ini: <strong>Rp {{ number_format($kasBank->saldo_saat_ini, 2, ',', '.') }}</strong></h4>
@stop

@section('content')
    {{-- Form Tambah Transaksi --}}
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Transaksi Baru</h3>
        </div>
        <form action="{{ route('transaksi.store') }}" method="POST">
            @csrf
            <input type="hidden" name="kas_bank_id" value="{{ $kasBank->kas_bank_id }}">
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
                    <div class="form-group col-md-6">
                        <label for="tanggal_transaksi">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="jenis_transaksi">Jenis Transaksi</label>
                        <select class="form-control" id="jenis_transaksi" name="jenis_transaksi" required>
                            <option value="debit">Pemasukan (Debit)</option>
                            <option value="kredit">Pengeluaran (Kredit)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="jumlah">Jumlah</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" placeholder="Masukkan jumlah tanpa titik atau koma" required>
                        </div>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="akun_id">Akun Transaksi</label>
                        <select class="form-control" id="akun_id" name="akun_id" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach ($akuns as $akun)
                                <option value="{{ $akun->akun_id }}">
                                    [{{ $akun->kode_akun }}] {{ $akun->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Contoh: Penjualan produk, Pembelian ATK, dll." required></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                <a href="{{ route('kas-bank.index') }}" class="btn btn-secondary">Kembali ke Daftar Akun</a>
            </div>
        </form>
    </div>

    {{-- Tabel Riwayat Transaksi --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Transaksi</h3>
        </div>
        <div class="card-body">
            <table id="transaction-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Pemasukan (Debit)</th>
                        <th>Pengeluaran (Kredit)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kasBank->transaksiKasBanks as $transaksi)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y') }}</td>
                            <td>{{ $transaksi->deskripsi }}</td>
                            <td>
                                @if($transaksi->jumlah_debit > 0)
                                    Rp {{ number_format($transaksi->jumlah_debit, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($transaksi->jumlah_kredit > 0)
                                    Rp {{ number_format($transaksi->jumlah_kredit, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    {{-- Jika Anda menggunakan plugin select2 untuk dropdown yang lebih baik --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable untuk tabel riwayat jika diperlukan
            $('#transaction-table').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "order": [[ 0, "desc" ]] // Urutkan berdasarkan tanggal terbaru
            });

            // Inisialisasi Select2 jika diinstal
            // $('#akun_id').select2();

            // Script untuk menampilkan notifikasi dari session
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2500
                });
            @endif
        });
    </script>
@stop