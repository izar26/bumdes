@extends('adminlte::page')

@section('title', 'Edit Unit Usaha')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">✏️ Edit Unit Usaha</h1>
        <a href="{{ route('admin.manajemen-data.unit_usaha.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-gray shadow-sm">
                <div class="card-header bg-gray">
                    <h3 class="card-title mb-0">Form Edit Unit Usaha</h3>
                </div>
                <form action="{{ route('admin.manajemen-data.unit_usaha.update', $unitUsaha->unit_usaha_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            {{-- Kolom Kiri --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_unit" class="text-gray">Nama Unit Usaha <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_unit"
                                        class="form-control @error('nama_unit') is-invalid @enderror"
                                        id="nama_unit" placeholder="Contoh: Toko Sembako Sejahtera"
                                        value="{{ old('nama_unit', $unitUsaha->nama_unit) }}" required>
                                    @error('nama_unit')
                                        <small class="invalid-feedback">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jenis_usaha" class="text-gray">Jenis Usaha <span class="text-danger">*</span></label>
                                    <input type="text" name="jenis_usaha"
                                        class="form-control @error('jenis_usaha') is-invalid @enderror"
                                        id="jenis_usaha" placeholder="Contoh: Retail, Kafe, Bengkel"
                                        value="{{ old('jenis_usaha', $unitUsaha->jenis_usaha) }}" required>
                                    @error('jenis_usaha')
                                        <small class="invalid-feedback">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Kolom Kanan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai_operasi" class="text-gray">Tanggal Mulai Operasi</label>
                                    <input type="date" name="tanggal_mulai_operasi"
                                        class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror"
                                        id="tanggal_mulai_operasi" value="{{ old('tanggal_mulai_operasi', $unitUsaha->tanggal_mulai_operasi) }}">
                                    @error('tanggal_mulai_operasi')
                                        <small class="invalid-feedback">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status_operasi" class="text-gray">Status Operasi <span class="text-danger">*</span></label>
                                    <select name="status_operasi" id="status_operasi"
                                        class="form-control @error('status_operasi') is-invalid @enderror" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Aktif' ? 'selected' : '' }}> Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Tidak Aktif' ? 'selected' : '' }}> Tidak Aktif</option>
                                        <option value="Dalam Pengembangan" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Dalam Pengembangan' ? 'selected' : '' }}> Dalam Pengembangan</option>
                                    </select>
                                    @error('status_operasi')
                                        <small class="invalid-feedback">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Penanggung Jawab --}}
                        <div class="form-group">
                            <label for="penanggung_jawab_ids" class="text-gray">Penanggung Jawab</label>
                            <select name="penanggung_jawab_ids[]" id="penanggung_jawab_ids"
                                class="form-control select2 @error('penanggung_jawab_ids') is-invalid @enderror"
                                multiple="multiple" style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" class="text-danger"
                                        @if(in_array($user->user_id, $assignedUserIds)) selected @endif>
                                        {{ $user->name }} ({{ $user->getRoleNames()->first() ?? 'Tidak Ada Role' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Bisa memilih lebih dari satu orang.</small>
                            @error('penanggung_jawab_ids')
                                <small class="invalid-feedback">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-gray-light text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .border-gray { border-color: #d1d5db !important; }
        .bg-gray { background-color: #f3f6f3 !important; color: #374151; border-bottom: 1px solid #d1d5db; }
        .bg-gray-light { background-color: #f9fafb !important; border-top: 1px solid #d1d5db; }
        .text-gray { color: black; font-weight: 500; }
        .card-title { font-weight: 600; color: #374151; }
       .select2-container--default .select2-selection--multiple {
    border: 2px solid #007bff !important; /* Biru tegas */
    border-radius: 6px !important;
    min-height: 45px !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    padding: 8px !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #007bff !important;
    border: none !important;
    color: #fff !important;
    padding: 5px 10px !important;
    border-radius: 4px !important;
    font-size: 14px;
}

    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#penanggung_jawab_ids').select2({
                placeholder: "Pilih penanggung jawab",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@stop
