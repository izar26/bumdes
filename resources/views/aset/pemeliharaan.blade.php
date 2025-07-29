@extends('adminlte::page')

@section('title', 'Pemeliharaan Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-tools"></i> Pemeliharaan Aset BUMDes</h1>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Manajemen Pemeliharaan Aset</h3>
        </div>
        <div class="card-body">
            <p>Halaman ini akan mencatat jadwal dan riwayat pemeliharaan aset BUMDes.</p>
            <p>Anda bisa menambahkan fitur seperti:</p>
            <ul>
                <li>Daftar aset dengan jadwal pemeliharaan.</li>
                <li>Pencatatan riwayat pemeliharaan (tanggal, biaya, deskripsi).</li>
                <li>Notifikasi untuk pemeliharaan yang akan datang.</li>
            </ul>
            <div class="alert alert-info">
                Fitur ini akan dikembangkan lebih lanjut.
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Pemeliharaan Aset BUMDes loaded!');
    </script>
@stop
