@extends('adminlte::page')

@section('title', 'Edit Data Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Data Anggota</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                {{-- Perbaikan: form action menggunakan anggota_id --}}
                <form action="{{ route('admin.manajemen-data.anggota.update', $anggota->anggota_id) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control"
                               value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required>
                    </div>


                    {{-- NIK --}}
                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" name="nik" class="form-control"
                               value="{{ old('nik', $anggota->nik) }}" required>
                    </div>

                    {{-- Alamat --}}
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                    </div>

                    {{-- No Telepon --}}
                    <div class="form-group">
                        <label>No Telepon</label>
                        <input type="text" name="no_telepon" class="form-control"
                               value="{{ old('no_telepon', $anggota->no_telepon) }}" required>
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    {{-- Role/Jabatan --}}
                    <div class="form-group">
                        <label>Jabatan / Role</label>
                        <select name="role" class="form-control">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($roles as $role)
                               <option value="{{ $role->name }}"
    {{ optional($anggota->user)->hasRole($role->name) ? 'selected' : '' }}>
    {{ ucwords(str_replace('_', ' ', $role->name)) }}
</option>

                            @endforeach
                        </select>
                        <small class="text-muted">Kosongkan jika anggota belum memiliki akun.</small>
                    </div>

                    {{-- Unit Usaha --}}
                    <div class="form-group">
                        <label>Unit Usaha</label>
                        <select name="unit_usaha_id" class="form-control">
                            <option value="">-- Tidak Ada --</option>
                            @foreach ($unitUsahas as $unit)
                                <option value="{{ $unit->unit_usaha_id }}" {{ $anggota->unit_usaha_id == $unit->unit_usaha_id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Anggota --}}
                    <div class="form-group">
                        <label>Status Anggota</label>
                        <select name="status_anggota" class="form-control" required>
                            <option value="Aktif" {{ $anggota->status_anggota == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ $anggota->status_anggota == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    {{-- Foto --}}
                    <div class="form-group">
                        <label>Foto Profil</label><br>
                        @if($anggota->photo)
                            <img src="{{ asset('storage/' . $anggota->photo) }}" class="img-thumbnail mb-2" width="120">
                        @endif
                        <input type="file" name="photo" class="form-control">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah foto.</small>
                    </div>

                    {{-- Email (Opsional) --}}
                    <div class="form-group">
                        <label>Email Akun</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', optional($anggota->user)->email) }}">
                        <small class="text-muted">Isi jika ingin membuat/mengubah akun.</small>
                    </div>

                    {{-- Password (Opsional) --}}
                    <div class="form-group">
                        <label>Password Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <input type="password" name="password_confirmation" class="form-control mt-2" placeholder="Konfirmasi Password Baru">
                    </div>

                    {{-- Tombol --}}
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
