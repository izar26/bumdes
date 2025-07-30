@extends('adminlte::page')
@section('title', 'Detail Pembelian')
@section('content_header')
    <h1>Detail Faktur Pembelian</h1>
@stop
@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-file-invoice"></i> Faktur Pembelian
                <small class="float-right">Tanggal: {{ \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari Pemasok
            <address>
                <strong>{{ $pembelian->pemasok->nama_pemasok }}</strong><br>
                {{ $pembelian->pemasok->alamat ?? '' }}<br>
                Telepon: {{ $pembelian->pemasok->no_telepon ?? '' }}<br>
                Email: {{ $pembelian->pemasok->email ?? '' }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            Kepada
            <address>
                <strong>BUMDes Anda</strong><br>
                Alamat BUMDes Anda
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>No. Faktur #{{ $pembelian->no_faktur ?? '-' }}</b><br>
            <b>Status:</b>
            @if($pembelian->status_pembelian == 'Lunas')
                <span class="badge badge-success">Lunas</span>
            @else
                <span class="badge badge-warning">Belum Lunas</span>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Produk</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembelian->detailPembelians as $detail)
                    <tr>
                        <td>{{ $detail->jumlah }}</td>
                        <td>{{ $detail->produk->nama_produk }}</td>
                        <td>{{ 'Rp ' . number_format($detail->harga_unit, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-6"></div>
        <div class="col-6">
            <p class="lead">Total Pembayaran</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Total:</th>
                        <td class="text-right"><strong>{{ 'Rp ' . number_format($pembelian->nominal_pembelian, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <button onclick="window.print();" class="btn btn-default"><i class="fas fa-print"></i> Cetak</button>
            <a href="{{ route('usaha.pembelian.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@stop
@section('css')
<style>
    @media print {
        .main-sidebar, .main-header, .btn, .content-header, .no-print {
            display: none !important;
        }
        .content-wrapper, .content {
            margin: 0 !important; padding: 0 !important;
        }
    }
</style>
@stop
