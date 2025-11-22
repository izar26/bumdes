@extends('adminlte::page')

@section('title', 'Data Pinjaman')

@section('content_header')
    <h1><i class="fas fa-book"></i> Data Pinjaman Anggota</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Semua Pinjaman</h3>
            <div class="card-tools">
                <a href="{{ route('simpanan.pinjaman.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus-circle"></i> Catat Pinjaman Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-ban"></i> {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover dataTable">
                    <thead>
                        <tr>
                            <th>No Pinjaman</th>
                            <th>Anggota</th>
                            <th>Pokok Pinjaman</th>
                            {{-- KOLOM BARU: Sisa Tagihan (Rp) --}}
                            <th>Sisa Tagihan (Rp)</th>
                            <th>Status</th>
                            <th class="text-center" width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pinjamans as $pinjaman)
                            @php
                                // 1. Cari angsuran berikutnya utk tombol bayar (Logika Lama)
                                $nextAngsuran = $pinjaman->angsuran
                                    ->where('status', 'belum_bayar')
                                    ->sortBy('angsuran_ke')
                                    ->first();

                                // 2. Hitung Total Sisa Rupiah (Logika Baru)
                                // Menjumlahkan kolom 'jumlah_bayar' dari semua angsuran yg statusnya belum lunas
                                $sisaRupiah = $pinjaman->angsuran
                                    ->where('status', 'belum_bayar')
                                    ->sum('jumlah_bayar');

                                // 3. Hitung Sisa Kali Bayar (Logika Lama)
                                $sisaKali = $pinjaman->angsuran
                                    ->where('status', 'belum_bayar')
                                    ->count();
                            @endphp

                            <tr>
                                <td><span class="text-bold text-primary">{{ $pinjaman->no_pinjaman }}</span></td>
                                <td>
                                    <div>{{ $pinjaman->anggota->nama_lengkap ?? 'Anggota Terhapus' }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($pinjaman->tanggal_pencairan)->format('d M Y') }}</small>
                                </td>
                                <td>Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</td>

                                {{-- TAMPILKAN SISA RUPIAH --}}
                                <td>
                                    @if($pinjaman->status == 'lunas')
                                        <span class="text-success text-bold">Rp 0</span>
                                    @else
                                        {{-- Tampilkan merah agar terlihat sebagai tunggakan --}}
                                        <span class="text-danger text-bold">Rp {{ number_format($sisaRupiah, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if($pinjaman->status == 'lunas')
                                        <span class="badge badge-success">LUNAS</span>
                                    @else
                                        <span class="badge badge-info">AKTIF</span>
                                        <br>
                                        {{-- Tetap tampilkan sisa kali bayar sebagai info tambahan --}}
                                        <small>Kurang: {{ $sisaKali }}x Bayar</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        {{-- Tombol Detail --}}
                                        <a href="{{ route('simpanan.pinjaman.show', $pinjaman->pinjaman_id) }}" class="btn btn-sm btn-default" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Tombol Bayar (Muncul jika ada tagihan) --}}
                                        @if($nextAngsuran)
                                            <a href="{{ route('simpanan.angsuran.bayar', $nextAngsuran->angsuran_id) }}" class="btn btn-sm btn-success" title="Bayar Angsuran Ke-{{ $nextAngsuran->angsuran_ke }}">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </a>
                                        @endif

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('simpanan.pinjaman.destroy', $pinjaman->pinjaman_id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Urutkan berdasarkan No Pinjaman (index 0) secara descending agar data terbaru diatas
            $('.dataTable').DataTable({ "order": [[ 0, "desc" ]] });
        });
    </script>
@endsection
