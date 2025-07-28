{{-- resources/views/keuangan/kas_bank/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Daftar Kas & Bank')

@section('content_header')
    <h1>Daftar Akun Kas & Bank</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Akun</h3>
            <div class="card-tools">
            <a href="{{ route('kas-bank.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Akun Baru
            </a>
        </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">    
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama Akun</th>
                        <th>Nomor Rekening</th>
                        <th>Saldo Saat Ini</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kasBanks as $key => $kasBank)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $kasBank->nama_akun_kas_bank }}</td>
                            <td>{{ $kasBank->nomor_rekening ?? '-' }}</td>
                            <td>Rp {{ number_format($kasBank->saldo_saat_ini, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('kas-bank.show', $kasBank->kas_bank_id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Lihat Transaksi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data akun kas/bank.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop