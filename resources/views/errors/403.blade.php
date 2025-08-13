@extends('adminlte::page')

@section('title', 'Akses Ditolak')

@section('content_header')
    <h1>Akses Ditolak</h1>
@stop

@section('content')
    <div class="error-page">
        <h2 class="headline text-danger"> 403</h2>
        <div class="error-content">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Akses ditolak.</h3>
            <p>
                Anda tidak memiliki izin untuk mengakses halaman ini.
                Mungkin Anda bisa kembali ke <a href="{{ route('dashboard.redirect') }}">dashboard</a>.
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
