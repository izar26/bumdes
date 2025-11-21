@extends('adminlte::page')

@section('title', 'Detail Rekening Anggota')

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Detail Rekening: **{{ $anggota->nama_lengkap ?? 'Anggota Tidak Ditemukan' }}**</h1>
@stop

@section('content')
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#ringkasan" data-toggle="tab">Ringkasan Rekening</a></li>
                        <li class="nav-item"><a class="nav-link" href="#histori" data-toggle="tab">Histori Transaksi</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        {{-- TAB RINGKASAN REKENING --}}
                        <div class="tab-pane active" id="ringkasan">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Jenis Simpanan</th>
                                        <th>No Rekening</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($anggota->rekeningSimpanan as $rekening)
                                        <tr>
                                            <td>{{ $rekening->jenisSimpanan->nama_jenis ?? 'N/A' }}</td>
                                            <td>{{ $rekening->no_rekening }}</td>
                                            <td>**Rp {{ number_format($rekening->saldo) }}**</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">Anggota ini belum memiliki rekening simpanan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- TAB HISTORI TRANSAKSI --}}
                        <div class="tab-pane" id="histori">
                            <table class="table table-bordered dataTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Rekening</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Saldo Akhir</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Menggabungkan semua transaksi dari semua rekening --}}
                                    @foreach ($anggota->rekeningSimpanan as $rekening)
                                        @foreach ($rekening->transaksiSimpanan as $transaksi)
                                            <tr>
                                                <td>{{ $transaksi->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                                                <td>{{ $rekening->jenisSimpanan->kode_jenis ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $transaksi->jenis_transaksi == 'setor' ? 'success' : 'danger' }}">
                                                        {{ strtoupper($transaksi->jenis_transaksi) }}
                                                    </span>
                                                </td>
                                                <td>Rp {{ number_format($transaksi->jumlah) }}</td>
                                                <td>Rp {{ number_format($transaksi->saldo_setelah_transaksi) }}</td>
                                                <td>{{ $transaksi->admin->name ?? 'Sistem' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
