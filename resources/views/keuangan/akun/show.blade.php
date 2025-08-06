@extends('adminlte::page')

@section('title', 'Detail Akun Keuangan')

@section('content_header')
    <h1>Detail Akun Keuangan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Akun: {{ $akun->nama_akun }} ({{ $akun->kode_akun }})</h3>
        </div>
        <div class="card-body">
            <p><strong>Kode Akun:</strong> {{ $akun->kode_akun }}</p>
            <p><strong>Nama Akun:</strong> {{ $akun->nama_akun }}</p>
            <p><strong>Tipe Akun:</strong> {{ $akun->tipe_akun }}</p>
            <p><strong>Akun Header:</strong> {{ $akun->is_header ? 'Ya' : 'Tidak' }}</p>
            <p><strong>Akun Induk:</strong> {{ $akun->parent ? $akun->parent->kode_akun . ' - ' . $akun->parent->nama_akun : '-' }}</p>

            @if ($akun->children->count() > 0)
                <h5>Sub-Akun:</h5>
                <ul>
                    @foreach ($akun->children as $child)
                        <li>{{ $child->kode_akun }} - {{ $child->nama_akun }}</li>
                    @endforeach
                </ul>
            @endif

            <p><strong>Dibuat Pada:</strong> {{ $akun->created_at->format('d F Y H:i') }}</p>
            <p><strong>Diperbarui Pada:</strong> {{ $akun->updated_at->format('d F Y H:i') }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.akun.edit', $akun->akun_id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.akun.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>
    </div>
@stop
