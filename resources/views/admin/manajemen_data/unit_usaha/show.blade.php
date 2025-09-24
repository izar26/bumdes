@extends('adminlte::page')

@section('title', 'Detail Unit Usaha')

@section('content_header')
    <h1>Detail Unit Usaha</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Unit Usaha: {{ $unitUsaha->nama_unit }}</h3>
        </div>
        <div class="card-body">
            <p><strong>ID Unit:</strong> {{ $unitUsaha->unit_usaha_id }}</p>
            <p><strong>Nama Unit:</strong> {{ $unitUsaha->nama_unit }}</p>
            <p><strong>Jenis Usaha:</strong> {{ $unitUsaha->jenis_usaha }}</p>
            <p><strong>BUMDes Induk:</strong> {{ $unitUsaha->bungdes->nama_bumdes ?? 'N/A' }}</p>
            <p><strong>Penanggung Jawab:</strong> {{ $unitUsaha->user->username ?? 'N/A' }}</p>
            <p><strong>Tanggal Mulai Operasi:</strong> {{ optional($unitUsaha->tanggal_mulai_operasi)->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Status Operasi:</strong> {{ $unitUsaha->status_operasi }}</p>
            <p><strong>Dibuat Pada:</strong> {{ $unitUsaha->created_at->format('d F Y H:i') }}</p>
            <p><strong>Diperbarui Pada:</strong> {{ $unitUsaha->updated_at->format('d F Y H:i') }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.manajemen-data.unit_usaha.edit', $unitUsaha->unit_usaha_id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.manajemen-data.unit_usaha.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>
    </div>
@stop
