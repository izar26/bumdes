@extends('adminlte::page')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-edit mr-2"></i> Edit Aset BUMDes</h4>
                        <a href="{{ route('bumdes.aset.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body p-5">
                    <form action="{{ route('bumdes.aset.update', $aset->aset_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Nomor Inventaris -->
                            <div class="col-md-6 mb-4">
                                <label for="nomor_inventaris" class="form-label fw-bold">Nomor Inventaris</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control @error('nomor_inventaris') is-invalid @enderror" 
                                           id="nomor_inventaris" name="nomor_inventaris" 
                                           value="{{ old('nomor_inventaris', $aset->nomor_inventaris) }}" required>
                                    @error('nomor_inventaris')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Nama Aset -->
                            <div class="col-md-6 mb-4">
                                <label for="nama_aset" class="form-label fw-bold">Nama Aset</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-box"></i></span>
                                    <input type="text" class="form-control @error('nama_aset') is-invalid @enderror" 
                                           id="nama_aset" name="nama_aset" 
                                           value="{{ old('nama_aset', $aset->nama_aset) }}" required>
                                    @error('nama_aset')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Jenis Aset -->
                            <div class="col-md-6 mb-4">
                                <label for="jenis_aset" class="form-label fw-bold">Jenis Aset</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control @error('jenis_aset') is-invalid @enderror" 
                                           id="jenis_aset" name="jenis_aset" 
                                           value="{{ old('jenis_aset', $aset->jenis_aset) }}" required>
                                    @error('jenis_aset')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Nilai Perolehan -->
                            <div class="col-md-6 mb-4">
                                <label for="nilai_perolehan" class="form-label fw-bold">Nilai Perolehan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" class="form-control @error('nilai_perolehan') is-invalid @enderror" 
                                           id="nilai_perolehan" name="nilai_perolehan" 
                                           value="{{ old('nilai_perolehan', $aset->nilai_perolehan) }}" required min="0">
                                    @error('nilai_perolehan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tanggal Perolehan -->
                            <div class="col-md-6 mb-4">
                                <label for="tanggal_perolehan" class="form-label fw-bold">Tanggal Perolehan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control @error('tanggal_perolehan') is-invalid @enderror" 
                                           id="tanggal_perolehan" name="tanggal_perolehan" 
                                           value="{{ old('tanggal_perolehan', $aset->tanggal_perolehan) }}" required>
                                    @error('tanggal_perolehan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Kondisi -->
                            <div class="col-md-6 mb-4">
                                <label for="kondisi" class="form-label fw-bold">Kondisi</label>
                                <select class="form-select @error('kondisi') is-invalid @enderror" id="kondisi" name="kondisi" required>
                                    <option value="Baik" {{ old('kondisi', $aset->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Rusak Ringan" {{ old('kondisi', $aset->kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                    <option value="Rusak Berat" {{ old('kondisi', $aset->kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                                </select>
                                @error('kondisi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Lokasi -->
                            <div class="col-md-6 mb-4">
                                <label for="lokasi" class="form-label fw-bold">Lokasi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control @error('lokasi') is-invalid @enderror" 
                                           id="lokasi" name="lokasi" 
                                           value="{{ old('lokasi', $aset->lokasi) }}">
                                    @error('lokasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Unit Usaha -->
                            <div class="col-md-6 mb-4">
                                <label for="unit_usaha_id" class="form-label fw-bold">Unit Usaha</label>
                                <select class="form-select @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id" name="unit_usaha_id">
                                    <option value="">Pilih Unit Usaha</option>
                                    @foreach($unitUsahas as $unitUsaha)
                                        <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id', $aset->unit_usaha_id) == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                            {{ $unitUsaha->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_usaha_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-3">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
        border: none;
    }
    
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
    }
    
    .input-group-text {
        min-width: 45px;
        justify-content: center;
    }
    
    .form-control, .form-select {
        padding: 0.75rem 1rem;
        border-radius: 0.375rem !important;
        border: 1px solid #ced4da;
    }
    
    .btn {
        padding: 0.6rem 1.25rem;
        font-weight: 500;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .invalid-feedback {
        font-size: 0.875rem;
    }
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
    }
</style>
@endsection