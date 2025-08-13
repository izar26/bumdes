@extends('adminlte::page')

@section('title', 'Halaman Tidak Ditemukan')

@section('content_header')
    <h1>Halaman Tidak Ditemukan</h1>
@stop

@section('content')
    <div class="error-page">
        <h2 class="headline text-warning"> 404</h2>
        <div class="error-content">
            <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Halaman tidak ditemukan.</h3>
            <p>
                Kami tidak dapat menemukan halaman yang Anda cari.
                Mungkin Anda bisa kembali ke <a href="{{ route('home') }}">dashboard</a>.
            </p>
        </div>
    </div>
@stop

@section('css')
    <style>
        .error-page {
            text-align: center;
            margin-top: 100px;
        }
        .error-page .error-content {
            margin-top: 30px;
        }
        .error-page h2.headline {
            font-size: 100px;
            font-weight: 300;
        }
    </style>
@stop
