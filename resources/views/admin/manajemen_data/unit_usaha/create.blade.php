@extends('adminlte::page')

@section('title', 'Tambah Unit Usaha')

@section('content_header')
    <h1>Tambah Unit Usaha</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Unit Usaha</h3>
        </div>
        <form action="{{ route('admin.manajemen-data.unit_usaha.store') }}" method="POST">
            @csrf
            <div class="card-body">
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
                    <label for="nama_unit">Nama Unit</label>
                    <input type="text" name="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" id="nama_unit" placeholder="Masukkan Nama Unit Usaha" value="{{ old('nama_unit') }}" required>
                    @error('nama_unit')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jenis_usaha">Jenis Usaha</label>
                    <input type="text" name="jenis_usaha" class="form-control @error('jenis_usaha') is-invalid @enderror" id="jenis_usaha" placeholder="Contoh: Perdagangan, Pertanian, Jasa" value="{{ old('jenis_usaha') }}" required>
                    @error('jenis_usaha')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="bungdes_id">Pilih BUMDes</label>
                    <select name="bungdes_id" id="bungdes_id" class="form-control @error('bungdes_id') is-invalid @enderror" required>
                        <option value="">-- Pilih BUMDes --</option>
                        @foreach ($bungdeses as $bungdes)
                            <option value="{{ $bungdes->bungdes_id }}" {{ old('bungdes_id') == $bungdes->bungdes_id ? 'selected' : '' }}>
                                {{ $bungdes->nama_bumdes }}
                            </option>
                        @endforeach
                    </select>
                    @error('bungdes_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="user_id">Penanggung Jawab</label>
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                        <option value="">-- Pilih Penanggung Jawab (Opsional) --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->user_id }}" {{ old('user_id') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->username }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal_mulai_operasi">Tanggal Mulai Operasi</label>
                    <input type="date" name="tanggal_mulai_operasi" class="form-control @error('tanggal_mulai_operasi') is-invalid @enderror" id="tanggal_mulai_operasi" value="{{ old('tanggal_mulai_operasi') }}">
                    @error('tanggal_mulai_operasi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status_operasi">Status Operasi</label>
                    <select name="status_operasi" id="status_operasi" class="form-control @error('status_operasi') is-invalid @enderror" required>
                        <option value="Aktif" {{ old('status_operasi') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Pasif" {{ old('status_operasi') == 'Pasif' ? 'selected' : '' }}>Pasif</option>
                        <option value="Dalam Pengembangan" {{ old('status_operasi') == 'Dalam Pengembangan' ? 'selected' : '' }}>Dalam Pengembangan</option>
                        {{-- Tambahkan status lain jika ada --}}
                    </select>
                    @error('status_operasi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Unit Usaha</button>
            </div>
        </form>
    </div>
@stop
