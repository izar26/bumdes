@extends('adminlte::page')

@section('title', 'Kelola Unit Usaha')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Kelola Unit Usaha</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Kelola Unit Usaha</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Detail Unit Usaha</h3>
                </div>
                <form action="{{ route('usaha.unit_setting.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="nama_unit">Nama Unit Usaha</label>
                            <input type="text" name="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" id="nama_unit" value="{{ old('nama_unit', $unitUsaha->nama_unit) }}" placeholder="Masukkan nama unit usaha">
                            @error('nama_unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jenis_usaha">Jenis Usaha</label>
                            <input type="text" name="jenis_usaha" class="form-control @error('jenis_usaha') is-invalid @enderror" id="jenis_usaha" value="{{ old('jenis_usaha', $unitUsaha->jenis_usaha) }}" placeholder="Masukkan jenis usaha">
                            @error('jenis_usaha')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_mulai_operasi">Tanggal Mulai Operasi</label>
                            <input type="date"
    name="tanggal_mulai_operasi"
    id="tanggal_mulai_operasi"
    class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror"
    value="{{ old('tanggal_mulai_operasi', optional($unitUsaha->tanggal_mulai_operasi)->format('Y-m-d')) }}">

                            @error('tanggal_mulai_operasi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status_operasi">Status Operasi</label>
                            <select name="status_operasi" id="status_operasi" class="form-control @error('status_operasi') is-invalid @enderror">
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
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">simpan</button>
                    </div>
                </form>
            </div>
            </div>
    </div>
@stop
