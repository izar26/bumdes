@extends('adminlte::page')

@section('title', 'Edit Unit Usaha')

@section('content_header')
    <h1>Edit Unit Usaha</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Unit Usaha</h3>
                </div>
                <form action="{{ route('admin.manajemen-data.unit_usaha.update', $unitUsaha->unit_usaha_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_unit">Nama Unit Usaha</label>
                            <input type="text" name="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" id="nama_unit" value="{{ old('nama_unit', $unitUsaha->nama_unit) }}" required>
                            @error('nama_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="jenis_usaha">Jenis Usaha</label>
                            <input type="text" name="jenis_usaha" class="form-control @error('jenis_usaha') is-invalid @enderror" id="jenis_usaha" value="{{ old('jenis_usaha', $unitUsaha->jenis_usaha) }}" required>
                            @error('jenis_usaha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="tanggal_mulai_operasi">Tanggal Mulai Operasi</label>
                            <input type="date" name="tanggal_mulai_operasi" class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror" id="tanggal_mulai_operasi" value="{{ old('tanggal_mulai_operasi', $unitUsaha->tanggal_mulai_operasi) }}">
                            @error('tanggal_mulai_operasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status_operasi">Status Operasi</label>
                            <select name="status_operasi" id="status_operasi" class="form-control @error('status_operasi') is-invalid @enderror" required>
                                <option value="Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Tidak Aktif" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="Dalam Pengembangan" {{ old('status_operasi', $unitUsaha->status_operasi) == 'Dalam Pengembangan' ? 'selected' : '' }}>Dalam Pengembangan</option>
                            </select>
                            @error('status_operasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="penanggung_jawab_ids">Penanggung Jawab</label>
                            <select name="penanggung_jawab_ids[]" id="penanggung_jawab_ids" class="form-control select2 @error('penanggung_jawab_ids') is-invalid @enderror" multiple="multiple" style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" @if(in_array($user->user_id, $assignedUserIds)) selected @endif>
                                        {{ $user->name }} ({{ $user->getRoleNames()->first() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('penanggung_jawab_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Perbarui</button>
                        <a href="{{ route('admin.manajemen-data.unit_usaha.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop
