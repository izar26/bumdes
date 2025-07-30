@extends('adminlte::page')
@section('title', 'Edit Pemasok')
@section('content_header')
    <h1>Edit Data Pemasok</h1>
@stop
@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Edit Pemasok</h3>
    </div>
    <form action="{{ route('usaha.pemasok.update', $pemasok->pemasok_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-group">
                <label for="nama_pemasok">Nama Pemasok</label>
                <input type="text" name="nama_pemasok" class="form-control" value="{{ old('nama_pemasok', $pemasok->nama_pemasok) }}" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat (Opsional)</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $pemasok->alamat) }}</textarea>
            </div>
            <div class="form-group">
                <label for="no_telepon">No. Telepon (Opsional)</label>
                <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon', $pemasok->no_telepon) }}">
            </div>
            <div class="form-group">
                <label for="email">Email (Opsional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $pemasok->email) }}">
            </div>
            <div class="form-group">
                <label for="unit_usaha_id">Terkait Unit Usaha</label>
                <select name="unit_usaha_id" class="form-control" required>
                    <option value="">-- Pilih Unit Usaha --</option>
                    @foreach ($unitUsahas as $unit)
                        <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id', $pemasok->unit_usaha_id) == $unit->unit_usaha_id ? 'selected' : '' }}>
                           {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('usaha.pemasok.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop
