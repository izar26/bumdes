@extends('adminlte::page')

@section('title', 'Edit Unit Usaha')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Edit Unit Usaha</h1>
        <a href="{{ route('admin.manajemen-data.unit_usaha.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-gray">
                <div class="card-header bg-gray">
                    <h3 class="card-title">Form Edit Unit Usaha</h3>
                </div>
                <form action="{{ route('admin.manajemen-data.unit_usaha.update', $unitUsaha->unit_usaha_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_unit" class="text-gray">Nama Unit Usaha</label>
                                    <input type="text" name="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror"
                                           id="nama_unit" value="{{ old('nama_unit', $unitUsaha->nama_unit) }}" required>
                                    @error('nama_unit')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jenis_usaha" class="text-gray">Jenis Usaha</label>
                                    <input type="text" name="jenis_usaha" class="form-control @error('jenis_usaha') is-invalid @enderror"
                                           id="jenis_usaha" value="{{ old('jenis_usaha', $unitUsaha->jenis_usaha) }}" required>
                                    @error('jenis_usaha')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai_operasi" class="text-gray">Tanggal Mulai Operasi</label>
                                    <input type="date" name="tanggal_mulai_operasi" class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror"
                                           id="tanggal_mulai_operasi" value="{{ old('tanggal_mulai_operasi', $unitUsaha->tanggal_mulai_operasi) }}">
                                    @error('tanggal_mulai_operasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status_operasi" class="text-gray">Status Operasi</label>
                                    <select name="status_operasi" id="status_operasi" class="form-control @error('status_operasi') is-invalid @enderror" required>
                                        <option value="Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="Dalam Pengembangan" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Dalam Pengembangan' ? 'selected' : '' }}>Dalam Pengembangan</option>
                                    </select>
                                    @error('status_operasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="penanggung_jawab_ids" class="text-gray">Penanggung Jawab</label>
                            <select name="penanggung_jawab_ids[]" id="penanggung_jawab_ids"
                                    class="form-control select2 @error('penanggung_jawab_ids') is-invalid @enderror"
                                    multiple="multiple" style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" @if(in_array($user->user_id, $assignedUserIds)) selected @endif>
                                        {{ $user->name }} ({{ $user->getRoleNames()->first() }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih satu atau lebih penanggung jawab</small>
                            @error('penanggung_jawab_ids')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-gray-light">
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
        .border-gray {
            border-color: #d1d5db !important;
        }
        .bg-gray {
            background-color: #f3f4f6 !important;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
        }
        .bg-gray-light {
            background-color: #f9fafb !important;
            border-top: 1px solid #d1d5db;
        }
        .text-gray {
            color: #4b5563;
            font-weight: 500;
        }
        .card-title {
            font-weight: 600;
            color: #374151;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Pilih penanggung jawab",
                allowClear: true
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop   
