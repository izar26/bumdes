@extends('adminlte::page')

@section('title', 'Tambah Tarif Baru')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Aturan Tarif Baru</h1>
@stop

@section('content')
    <form action="{{ route('usaha.tarif.store') }}" method="POST">
        @csrf
        <div class="card">
            @include('usaha.tarif._form', ['submitButtonText' => 'Tambah Tarif'])
        </div>
    </form>
@stop
