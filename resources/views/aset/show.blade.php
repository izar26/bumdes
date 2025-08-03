@extends('adminlte::page')
@section('title', 'Detail Aset')
@section('content_header')
    <h1><i class="fas fa-fw fa-box"></i> Detail Aset</h1>
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Aset: {{ $aset->nama_aset }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Nomor Inventaris:</strong> {{ $aset->nomor_inventaris }}</p>
            <p><strong>Jenis Aset:</strong> {{ $aset->jenis_aset }}</p>
            <p><strong>Nilai Perolehan:</strong> Rp {{ number_format($aset->nilai_perolehan, 2, ',', '.') }}</p>
            <p><strong>Tanggal Perolehan:</strong> {{ \Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d M Y') }}</p>
            <p><strong>Kondisi:</strong> <span class="badge badge-{{ $aset->kondisi == 'Baik' ? 'success' : ($aset->kondisi == 'Rusak Ringan' ? 'warning' : 'danger') }}">{{ $aset->kondisi }}</span></p>
            <p><strong>Lokasi:</strong> {{ $aset->lokasi ?? '-' }}</p>
            <p><strong>Unit Usaha:</strong> {{ $aset->unitUsaha->nama_unit ?? '-' }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('bumdes.aset.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@stop