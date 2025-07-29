@extends('adminlte::page')

@section('title', 'Edit Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-edit"></i> Edit Aset: {{ $aset->nama_aset }}</h1>
@stop

@section('content')
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title">Form Edit Aset</h3>
        </div>
        <form action="{{ route('bumdes.aset.update', $aset->aset_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nama_aset">Nama Aset</label>
                    <input type="text" name="nama_aset" class="form-control @error('nama_aset') is-invalid @enderror" id="nama_aset" value="{{ old('nama_aset', $aset->nama_aset) }}" placeholder="Masukkan nama aset" required>
                    @error('nama_aset')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="jenis_aset">Jenis Aset</label>
                    <input type="text" name="jenis_aset" class="form-control @error('jenis_aset') is-invalid @enderror" id="jenis_aset" value="{{ old('jenis_aset', $aset->jenis_aset) }}" placeholder="Contoh: Gedung, Kendaraan, Peralatan" required>
                    @error('jenis_aset')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nilai_perolehan">Nilai Perolehan (Rp)</label>
                    <input type="number" name="nilai_perolehan" class="form-control @error('nilai_perolehan') is-invalid @enderror" id="nilai_perolehan" value="{{ old('nilai_perolehan', $aset->nilai_perolehan) }}" step="0.01" min="0" placeholder="Contoh: 15000000.00" required>
                    @error('nilai_perolehan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="tanggal_perolehan">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan" class="form-control @error('tanggal_perolehan') is-invalid @enderror" id="tanggal_perolehan" value="{{ old('tanggal_perolehan', $aset->tanggal_perolehan->format('Y-m-d')) }}" required>
                    @error('tanggal_perolehan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="kondisi">Kondisi</label>
                    <select name="kondisi" id="kondisi" class="form-control @error('kondisi') is-invalid @enderror" required>
                        <option value="">Pilih Kondisi</option>
                        <option value="Baik" {{ old('kondisi', $aset->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                        <option value="Rusak Ringan" {{ old('kondisi', $aset->kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="Rusak Berat" {{ old('kondisi', $aset->kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi (Opsional)</label>
                    <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi" value="{{ old('lokasi', $aset->lokasi) }}" placeholder="Contoh: Kantor Pusat, Unit Simpan Pinjam">
                    @error('lokasi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="bungdes_id">ID BUMDes</label>
                    <input type="number" name="bungdes_id" class="form-control @error('bungdes_id') is-invalid @enderror" id="bungdes_id" value="{{ old('bungdes_id', $aset->bungdes_id) }}" placeholder="ID BUMDes" required>
                    @error('bungdes_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="unit_usaha_id">ID Unit Usaha (Opsional)</label>
                    <input type="number" name="unit_usaha_id" class="form-control @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id" value="{{ old('unit_usaha_id', $aset->unit_usaha_id) }}" placeholder="ID Unit Usaha (opsional)">
                    @error('unit_usaha_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="penanggung_jawab">Penanggung Jawab (Opsional)</label>
                    <select name="penanggung_jawab" id="penanggung_jawab" class="form-control @error('penanggung_jawab') is-invalid @enderror">
                        <option value="">Pilih Penanggung Jawab</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('penanggung_jawab', $aset->penanggung_jawab) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('penanggung_jawab')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info"><i class="fas fa-sync-alt"></i> Perbarui Aset</button>
                <a href="{{ route('bumdes.aset.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Edit Aset BUMDes loaded!');
    </script>
@stop
