@extends('adminlte::page')

@section('title', 'Tambah Aset BUMDes Baru')

@section('content_header')
    <h1><i class="fas fa-fw fa-plus"></i> Tambah Aset BUMDes Baru</h1>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Aset</h3>
        </div>
        <form action="{{ route('bumdes.aset.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="form-group">
                    <label for="nomor_inventaris">Nomor Inventaris</label>
                    <input type="text" name="nomor_inventaris" id="nomor_inventaris"
                        class="form-control @error('nomor_inventaris') is-invalid @enderror"
                        value="{{ old('nomor_inventaris') }}" placeholder="Masukkan nomor inventaris aset">
                    @error('nomor_inventaris')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nama_aset">Nama Aset</label>
                    <input type="text" name="nama_aset" id="nama_aset"
                        class="form-control @error('nama_aset') is-invalid @enderror" value="{{ old('nama_aset') }}"
                        placeholder="Masukkan nama aset">
                    @error('nama_aset')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="jenis_aset">Jenis Aset</label>
                    <input type="text" name="jenis_aset" id="jenis_aset"
                        class="form-control @error('jenis_aset') is-invalid @enderror" value="{{ old('jenis_aset') }}"
                        placeholder="Contoh: Gedung, Kendaraan, Peralatan">
                    @error('jenis_aset')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nilai_perolehan">Nilai Perolehan</label>
                    <input type="number" step="0.01" name="nilai_perolehan" id="nilai_perolehan"
                        class="form-control @error('nilai_perolehan') is-invalid @enderror"
                        value="{{ old('nilai_perolehan') }}" placeholder="Masukkan nilai perolehan (contoh: 15000000)">
                    @error('nilai_perolehan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="tanggal_perolehan">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan" id="tanggal_perolehan"
                        class="form-control @error('tanggal_perolehan') is-invalid @enderror"
                        value="{{ old('tanggal_perolehan') }}">
                    @error('tanggal_perolehan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="kondisi">Kondisi</label>
                    <select name="kondisi" id="kondisi" class="form-control @error('kondisi') is-invalid @enderror">
                        <option value="">Pilih Kondisi</option>
                        <option value="Baik" {{ old('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                        <option value="Rusak Ringan" {{ old('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan
                        </option>
                        <option value="Rusak Berat" {{ old('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat
                        </option>
                    </select>
                    @error('kondisi')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi"
                        class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi') }}"
                        placeholder="Masukkan lokasi aset">
                    @error('lokasi')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <!-- Contoh sederhana di aset.create atau aset.edit -->
                <div class="form-group">
                    <label for="unit_usaha_id">Unit Usaha</label>
                    <select name="unit_usaha_id" id="unit_usaha_id" class="form-control">
                        <option value="">Pilih Unit Usaha (Opsional)</option>
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->unit_usaha_id }}">
                                {{ $unitUsaha->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('bumdes.aset.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@stop
