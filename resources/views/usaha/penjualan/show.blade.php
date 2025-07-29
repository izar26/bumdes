@extends('adminlte::page')

@section('title', 'Detail Penjualan')

@section('content_header')
    <h1>Detail Invoice Penjualan</h1>
@stop

@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-store"></i> BUMDes Anda.
                <small class="float-right">Tanggal: {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari
            <address>
                <strong>BUMDes Anda</strong><br>
                Alamat BUMDes Anda<br>
                Telepon: (xxx) xxxx-xxxx<br>
                Email: info@bumdes.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            Kepada
            <address>
                <strong>{{ $penjualan->nama_pelanggan ?? 'Pelanggan Umum' }}</strong><br>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Invoice #{{ $penjualan->no_invoice }}</b><br>
            <br>
            <b>Status:</b> 
            @if($penjualan->status_penjualan == 'Lunas')
                <span class="badge badge-success">Lunas</span>
            @else
                <span class="badge badge-warning">Belum Lunas</span>
            @endif
            <br>
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
                    @foreach($penjualan->detailPenjualans as $detail)
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
        <div class="col-6">
            {{-- Bisa ditambahkan notes atau metode pembayaran di sini --}}
        </div>
        <div class="col-6">
            <p class="lead">Total Pembayaran</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Total:</th>
                        <td class="text-right"><strong>{{ 'Rp ' . number_format($penjualan->total_penjualan, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <a href="#" onclick="window.print();" class="btn btn-default"><i class="fas fa-print"></i> Cetak</a>
            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- CSS khusus untuk halaman cetak agar rapi --}}
    <style>
        @media print {
            .main-sidebar, .main-header, .btn, .content-header, .no-print {
                display: none !important;
            }
            .content-wrapper, .content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@stop