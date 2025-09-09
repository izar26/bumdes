@extends('adminlte::page')

@section('title', 'Buat Tagihan Baru')

@section('content_header')
    <h1 class="m-0 text-dark">Buat Tagihan Baru</h1>
@stop

@section('content')
    <form action="{{ route('usaha.tagihan.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                @include('usaha.tagihan._form')
            </div>
            <div class="card-footer">
                <a href="{{ route('usaha.tagihan.index') }}" class="btn btn-default">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
@stop
