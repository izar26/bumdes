@extends('adminlte::page')

@section('title', 'Lengkapi Profil')

@section('content_header')
    <h1>Lengkapi Data Profil</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <x-adminlte-card title="Data Profil Anggota" theme="primary" icon="fas fa-edit">

                {{-- Pesan Sukses atau Error dari Controller --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Pesan Error Validasi Umum --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update-complete') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Nama Lengkap (Read-only) --}}
                    <x-adminlte-input
                        name="nama_lengkap"
                        label="Nama Lengkap"
                        value="{{ old('nama_lengkap', $user->name) }}"
                        readonly
                        disable-feedback
                    />

                    {{-- NIK --}}
                    <x-adminlte-input
                        name="nik"
                        label="NIK"
                        placeholder="Masukkan Nomor Induk Kependudukan (16 digit)"
                        value="{{ old('nik', $anggota->nik) }}"
                        disable-feedback
                        required
                    />
                    @error('nik')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- Jenis Kelamin --}}
                    <x-adminlte-select2 name="jenis_kelamin" label="Jenis Kelamin">
                        <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </x-adminlte-select2>
                    @error('jenis_kelamin')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- Alamat --}}
                    <x-adminlte-textarea name="alamat" label="Alamat" placeholder="Masukkan alamat lengkap" required>
                        {{ old('alamat', $anggota->alamat) }}
                    </x-adminlte-textarea>
                    @error('alamat')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- Nomor Telepon --}}
                    <x-adminlte-input
                        name="no_telepon"
                        label="Nomor Telepon"
                        placeholder="Contoh: 081234567890"
                        value="{{ old('no_telepon', $anggota->no_telepon) }}"
                        disable-feedback
                        required
                    />
                    @error('no_telepon')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    {{-- Jabatan (Read-only) --}}
                    <x-adminlte-input
                        name="jabatan"
                        label="Jabatan"
                        value="{{ old('jabatan', ucwords(str_replace('_', ' ', $user->getRoleNames()->first()))) }}"
                        readonly
                        disable-feedback
                    />

                    {{-- Perbaikan: Input unit usaha hanya untuk role tertentu --}}
                    @if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha']))
                        <x-adminlte-select2 name="unit_usaha_id" label="Unit Usaha" {{ $anggota->unit_usaha_id ? 'disabled' : '' }}>
                            <option value="">Pilih Unit Usaha</option>
                            @foreach($unitUsahas as $unitUsaha)
                                <option value="{{ $unitUsaha->unit_usaha_id }}"
                                    {{ old('unit_usaha_id', $anggota->unit_usaha_id) == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                    {{ $unitUsaha->nama_unit }}
                                </option>
                            @endforeach
                        </x-adminlte-select2>
                        @if($anggota->unit_usaha_id)
                            <input type="hidden" name="unit_usaha_id" value="{{ $anggota->unit_usaha_id }}">
                        @endif
                        @error('unit_usaha_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    @endif

                    photo Profil
                    <x-adminlte-input-file name="photo" label="Upload photo Profil" placeholder="Pilih photo..."/>
                    @error('photo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    @if($anggota->photo)
                        <p class="text-muted">
                            photo saat ini:
                            <a href="{{ asset('storage/photo_anggota/' . $anggota->photo) }}" target="_blank">
                                {{ $anggota->photo }}
                            </a>
                        </p>
                    @endif

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Simpan Profil</button>
                    </div>
                </form>

            </x-adminlte-card>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            bsCustomFileInput.init();
        });
    </script>
@stop
