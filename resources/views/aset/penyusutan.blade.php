@extends('adminlte::page')

@section('title', 'Penyusutan Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-percent"></i> Penyusutan Aset BUMDes</h1>
@stop

@section('content')
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">Manajemen Penyusutan Aset</h3>
        </div>
        <div class="card-body">
            <p>Halaman ini akan menampilkan daftar aset beserta perhitungan penyusutannya.</p>
            <p>Anda bisa menambahkan fitur seperti:</p>
            <ul>
                <li>Daftar aset dengan nilai buku, umur ekonomis, dan metode penyusutan.</li>
                <li>Fungsi untuk mencatat penyusutan bulanan/tahunan.</li>
                <li>Laporan penyusutan.</li>
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
        console.log('Penyusutan Aset BUMDes loaded!');
    </script>
@stop
