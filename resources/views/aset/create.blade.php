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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nomor_inventaris">Nomor Inventaris</label>
                            <input type="text" name="nomor_inventaris" id="nomor_inventaris"
                                class="form-control @error('nomor_inventaris') is-invalid @enderror"
                                value="{{ old('nomor_inventaris') }}" placeholder="Masukkan nomor inventaris aset" required>
                            @error('nomor_inventaris')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_aset">Nama Aset</label>
                            <input type="text" name="nama_aset" id="nama_aset"
                                class="form-control @error('nama_aset') is-invalid @enderror" value="{{ old('nama_aset') }}"
                                placeholder="Masukkan nama aset" required>
                            @error('nama_aset')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_aset">Jenis Aset</label>
                            <input type="text" name="jenis_aset" id="jenis_aset"
                                class="form-control @error('jenis_aset') is-invalid @enderror" value="{{ old('jenis_aset') }}"
                                placeholder="Contoh: Gedung, Kendaraan, Peralatan" required>
                            @error('jenis_aset')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nilai_perolehan">Nilai Perolehan</label>
                            <input type="number" step="0.01" name="nilai_perolehan" id="nilai_perolehan"
                                class="form-control @error('nilai_perolehan') is-invalid @enderror"
                                value="{{ old('nilai_perolehan') }}" placeholder="Masukkan nilai perolehan (contoh: 15000000)" required min="0">
                            @error('nilai_perolehan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_perolehan">Tanggal Perolehan</label>
                            <input type="date" name="tanggal_perolehan" id="tanggal_perolehan"
                                class="form-control @error('tanggal_perolehan') is-invalid @enderror"
                                value="{{ old('tanggal_perolehan') }}" required>
                            @error('tanggal_perolehan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kondisi">Kondisi</label>
                            <select name="kondisi" id="kondisi" class="form-control @error('kondisi') is-invalid @enderror" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="Baru" {{ old('kondisi') == 'Baru' ? 'selected' : '' }}>Baru</option>
                                <option value="Baik" {{ old('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Rusak Ringan" {{ old('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ old('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- --- Kolom Baru untuk Penyusutan --- --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">Informasi Penyusutan</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="metode_penyusutan">Metode Penyusutan</label>
                            <select name="metode_penyusutan" id="metode_penyusutan" class="form-control @error('metode_penyusutan') is-invalid @enderror" required>
                                <option value="">Pilih Metode</option>
                                @foreach ($metodePenyusutan as $key => $value)
                                    <option value="{{ $key }}" {{ old('metode_penyusutan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('metode_penyusutan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="masa_manfaat">Masa Manfaat (Tahun)</label>
                            <input type="number" name="masa_manfaat" id="masa_manfaat"
                                class="form-control @error('masa_manfaat') is-invalid @enderror" value="{{ old('masa_manfaat') }}"
                                placeholder="Contoh: 5 tahun" required min="1">
                            @error('masa_manfaat')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nilai_residu">Nilai Residu</label>
                            <input type="number" step="0.01" name="nilai_residu" id="nilai_residu"
                                class="form-control @error('nilai_residu') is-invalid @enderror"
                                value="{{ old('nilai_residu') }}" placeholder="Masukkan nilai residu (contoh: 100000)" min="0">
                            @error('nilai_residu')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                {{-- --------------------------------- --}}

                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="unit_usaha_id">Unit Usaha</label>
                            <select name="unit_usaha_id" id="unit_usaha_id" class="form-control @error('unit_usaha_id') is-invalid @enderror">
                                <option value="">Pilih Unit Usaha (Opsional)</option>
                                @foreach ($unitUsahas as $unitUsaha)
                                    <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id') == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                        {{ $unitUsaha->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_usaha_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
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
