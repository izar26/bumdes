@extends('adminlte::page')

@section('title', 'Edit Unit Usaha')

@section('content_header')
    <h1>Edit Unit Usaha: {{ $unitUsaha->nama_unit }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Unit Usaha</h3>
        </div>
        <form action="{{ route('admin.manajemen-data.unit_usaha.update', $unitUsaha->unit_usaha_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
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
                    <label for="nama_unit">Nama Unit Usaha</label>
                    <input type="text" name="nama_unit" id="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" value="{{ old('nama_unit', $unitUsaha->nama_unit) }}" required>
                    @error('nama_unit')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jenis_usaha">Jenis Usaha</label>
                    <input type="text" name="jenis_usaha" id="jenis_usaha" class="form-control @error('jenis_usaha') is-invalid @enderror" value="{{ old('jenis_usaha', $unitUsaha->jenis_usaha) }}" required>
                    @error('jenis_usaha')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal_mulai_operasi">Tanggal Mulai Operasi</label>
                    <input type="date" name="tanggal_mulai_operasi" id="tanggal_mulai_operasi" class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror" value="{{ old('tanggal_mulai_operasi', optional($unitUsaha->tanggal_mulai_operasi)->format('Y-m-d')) }}">
                    @error('tanggal_mulai_operasi')
                        <span class="invalid-feedback">{{ $message }}</span>
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
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="user_id">Penanggung Jawab</label>
                    {{-- Tampilkan dropdown hanya jika pengguna yang login adalah Admin Unit Usaha --}}
                    @if (Auth::user()->isAdminBumdes())
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                            <option value="">-- Pilih Penanggung Jawab --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->user_id }}" {{ old('user_id', $unitUsaha->user_id) == $user->user_id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    @else
                        {{-- Tampilkan nama penanggung jawab dalam bentuk teks yang tidak bisa diedit --}}
                        <input type="hidden" name="user_id" value="{{ $unitUsaha->user_id }}">
                        <input type="text" class="form-control" value="{{ optional($unitUsaha->user)->name ?? 'Belum Ditugaskan' }}" disabled>
                        <small class="form-text text-muted">Anda tidak memiliki izin untuk mengubah penanggung jawab.</small>
                    @endif
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.manajemen-data.unit_usaha.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop
