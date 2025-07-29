@extends('adminlte::page')


@section('content')
<div class="card">
    <div class="card-header">
        <h2>Edit Produk: {{ $produk->nama_produk }}</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('usaha.produk.update', $produk->produk_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama_produk') is-invalid @enderror" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                @error('nama_produk')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="deskripsi_produk" class="form-label">Deskripsi Produk</label>
                <textarea class="form-control @error('deskripsi_produk') is-invalid @enderror" id="deskripsi_produk" name="deskripsi_produk" rows="3">{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                @error('deskripsi_produk')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="harga_beli" class="form-label">Harga Beli <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('harga_beli') is-invalid @enderror" id="harga_beli" name="harga_beli" value="{{ old('harga_beli', $produk->harga_beli) }}" required min="0">
                    @error('harga_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="harga_jual" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('harga_jual') is-invalid @enderror" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', $produk->harga_jual) }}" required min="0">
                    @error('harga_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="satuan_unit" class="form-label">Satuan Unit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('satuan_unit') is-invalid @enderror" id="satuan_unit" name="satuan_unit" value="{{ old('satuan_unit', $produk->satuan_unit) }}" required>
                    @error('satuan_unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stok_minimum" class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('stok_minimum') is-invalid @enderror" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', $produk->stok_minimum) }}" required min="0">
                    @error('stok_minimum')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="unit_usaha_id" class="form-label">Unit Usaha <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id" name="unit_usaha_id" required>
                        <option value="">Pilih Unit Usaha</option>
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->id }}" {{ old('unit_usaha_id', $produk->unit_usaha_id) == $unitUsaha->id ? 'selected' : '' }}>
                                {{ $unitUsaha->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_usaha_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select @error('kategori_id') is-invalid @enderror" id="kategori_id" name="kategori_id">
                        <option value="">Tidak Berkategori</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ old('kategori_id', $produk->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Perbarui Produk</button>
            <a href="{{ route('usaha.produk.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
