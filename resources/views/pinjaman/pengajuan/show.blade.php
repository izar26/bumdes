@extends('adminlte::page')

@section('title', 'Detail Pinjaman')

@section('content_header')
    <h1><i class="fas fa-clipboard-list"></i> Detail Pinjaman #{{ $pengajuanPinjaman->no_pinjaman }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            {{-- CARD DETAIL PINJAMAN --}}
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Informasi Pinjaman</h3></div>
                <div class="card-body">
                    <p><strong>Anggota:</strong> {{ $pengajuanPinjaman->anggota->nama_lengkap ?? 'N/A' }}</p>
                    @php $badge = ['pending' => 'warning', 'approved' => 'info', 'rejected' => 'danger', 'lunas' => 'success']; @endphp
                    <p><strong>Status:</strong> <span class="badge badge-{{ $badge[$pengajuanPinjaman->status] ?? 'secondary' }}">{{ strtoupper($pengajuanPinjaman->status) }}</span></p>
                    <hr>
                    <p><strong>Jumlah Pinjaman:</strong> Rp {{ number_format($pengajuanPinjaman->jumlah_pinjaman) }}</p>
                    <p><strong>Tenor:</strong> {{ $pengajuanPinjaman->tenor }} Bulan</p>
                    @if ($pengajuanPinjaman->status !== 'pending')
                        <p><strong>Angsuran Pokok / Bln:</strong> **Rp {{ number_format($pengajuanPinjaman->jumlah_angsuran_per_bulan) }}**</p>
                    @endif
                    <p><strong>Tgl Pengajuan:</strong> {{ $pengajuanPinjaman->tanggal_pengajuan->format('d M Y') }}</p>
                </div>

                @if ($pengajuanPinjaman->status === 'pending')
                <div class="card-footer text-center">
                    {{-- TOMBOL APPROVAL --}}
                    <form action="{{ route('simpanan.pinjaman.approve', $pengajuanPinjaman->pinjaman_id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Setuju? Jadwal Angsuran akan dibuat.')">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                    </form>
                    {{-- TOMBOL REJECT --}}
                    <form action="{{ route('simpanan.pinjaman.reject', $pengajuanPinjaman->pinjaman_id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pengajuan ini?')">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-8">
            {{-- CARD JADWAL ANGSURAN --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Jadwal Pembayaran Angsuran ({{ $pengajuanPinjaman->angsuran->count() }} Angsuran)</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Ke-</th>
                                <th>Jatuh Tempo</th>
                                <th>Jumlah Bayar</th>
                                <th>Status</th>
                                <th>Tgl Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengajuanPinjaman->angsuran as $angsuran)
                                <tr>
                                    <td>{{ $angsuran->angsuran_ke }}</td>
                                    <td>{{ $angsuran->tanggal_jatuh_tempo->format('d M Y') }}</td>
                                    <td>Rp {{ number_format($angsuran->jumlah_bayar) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $angsuran->status === 'lunas' ? 'success' : 'warning' }}">{{ strtoupper($angsuran->status) }}</span>
                                    </td>
                                    <td>{{ $angsuran->tanggal_bayar ? $angsuran->tanggal_bayar->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if ($angsuran->status !== 'lunas')
                                            <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#bayarModal{{ $angsuran->angsuran_id }}">
                                                <i class="fas fa-hand-holding-usd"></i> Bayar
                                            </button>
                                        @else
                                            <span class="text-success">Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Jadwal angsuran belum dibuat (Pinjaman belum disetujui).</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PEMBAYARAN ANGSURAN: Harus disertakan di halaman ini --}}
    @foreach ($pengajuanPinjaman->angsuran as $angsuran)
        @include('pinjaman.angsuran.bayar', ['angsuran' => $angsuran])
    @endforeach

@stop
