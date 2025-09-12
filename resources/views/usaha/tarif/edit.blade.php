@extends('adminlte::page')

@section('title', 'Edit Tarif')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Aturan Tarif</h1>
@stop

@section('content')
    <form action="{{ route('usaha.tarif.update', $tarif) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            @include('usaha.tarif._form', ['submitButtonText' => 'Simpan Perubahan'])
        </div>
    </form>
@stop
