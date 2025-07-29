@extends('adminlte::page')

@section('title', 'Tambah Produk Baru')

@section('content_header')
    <h1>Tambah Produk Baru</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Produk</h3>
    </div>
    <form action="{{ route('produk.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada masalah dengan inputan Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="nama_produk">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" id="nama_produk" placeholder="Masukkan nama produk" value="{{ old('nama_produk') }}" required>
            </div>

            <div class="form-group">
                <label for="deskripsi_produk">Deskripsi (Opsional)</label>
                <textarea name="deskripsi_produk" class="form-control" id="deskripsi_produk" rows="3">{{ old('deskripsi_produk') }}</textarea>
            </div>
            <div class="form-group">
                <label for="deskripsi_produk">Kategori</label>
                <textarea name="deskripsi_produk" class="form-control" id="deskripsi_produk" rows="3">{{ old('kategori') }}</textarea>
            </div>

            <div class="form-group">
                <label for="unit_usaha_id">Unit Usaha</label>
                <select name="unit_usaha_id" class="form-control" id="unit_usaha_id" required>
                    <option value="">-- Pilih Unit Usaha --</option>
                    @foreach ($unitUsahas as $unit)
                        <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                           {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <label for="harga_beli">Harga Beli (Modal)</label>
                    <input type="number" name="harga_beli" class="form-control" id="harga_beli" placeholder="0" value="{{ old('harga_beli') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="harga_jual">Harga Jual</label>
                    <input type="number" name="harga_jual" class="form-control" id="harga_jual" placeholder="0" value="{{ old('harga_jual') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="satuan_unit">Satuan Unit</label>
                    <input type="text" name="satuan_unit" class="form-control" id="satuan_unit" placeholder="Contoh: Pcs, Kg, Liter" value="{{ old('satuan_unit') }}" required>
                </div>
                <div class="form-group col-md-2">
                    <label for="stok_awal">Stok Awal</label>
                    <input type="number" name="stok_awal" class="form-control" id="stok_awal" placeholder="0" value="{{ old('stok_awal', 0) }}" required>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop
