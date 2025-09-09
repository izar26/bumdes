@extends('adminlte::page')

@section('title', 'Edit Tagihan')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Tagihan #{{ $tagihan->id }}</h1>
@stop

@section('content')
<form action="{{ route('usaha.tagihan.update', $tagihan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
            @include('usaha.tagihan._form')
            </div>
            <div class="card-footer">
            <a href="{{ route('usaha.tagihan.index') }}" class="btn btn-default">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </form>
@stop
