@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Lengkapi Data Profil Anda</div>
                <div class="card-body">
                    <p>Selamat datang! Akun Anda telah dibuat. Silakan lengkapi data profil di bawah ini untuk mengaktifkan akun Anda.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="nik">NIK</label>
                            <input type="text" name="nik" class="form-control" required value="{{ old('nik') }}">
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" required>{{ old('alamat') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="no_telepon">Nomor Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" required value="{{ old('no_telepon') }}">
                        </div>

                        <div class="form-group">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit_usaha_id">Pilih Unit Usaha</label>
                            <select name="unit_usaha_id" class="form-control" required>
                                <option value="">Pilih Unit Usaha</option>
                                @foreach(\App\Models\UnitUsaha::all() as $unitUsaha)
                                    <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id') == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>{{ $unitUsaha->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="jabatan">Jabatan (Opsional)</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}">
                        </div>

                        <div class="form-group">
                            <label for="foto">Foto Profil (Opsional)</label>
                            <input type="file" name="foto" class="form-control-file">
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Simpan dan Aktifkan Akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
