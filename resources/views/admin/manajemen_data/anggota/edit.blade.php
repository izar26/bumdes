@extends('adminlte::page')

@section('title', 'Edit Data Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Data Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Anggota: {{ $anggota->nama_lengkap }}</h3>
                </div>
                <form role="form" action="{{ route('admin.manajemen-data.anggota.update', $anggota->anggota_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            {{-- Kolom Kiri: Data Anggota --}}
                            <div class="col-md-6">
                                <h5>Data Diri Anggota</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required>
                                        @error('nama_lengkap') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="nik">NIK</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $anggota->nik) }}" required pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                                    </div>
                                    @error('nik') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                                    </div>
                                    @error('alamat') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="no_telepon">No. Telepon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                        <input type="tel" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}" placeholder="Contoh: 08123456789" required>
                                    </div>
                                    @error('no_telepon') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span></div>
                                        <select class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    @error('jenis_kelamin') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="photo">Foto Profil (Opsional)</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="photo" class="custom-file-input @error('photo') is-invalid @enderror" id="photo" accept="image/*">
                                            <label class="custom-file-label" for="photo">Pilih file...</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Maks. 2MB. Format: JPG, PNG, GIF.</small>
                                    @error('photo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    <div class="mt-2 text-center">
                                        <img id="photo-preview" class="img-thumbnail" width="150" src="{{ $anggota->photo ? Storage::url($anggota->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($anggota->nama_lengkap) . '&background=007bff&color=fff&size=150' }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Data Akun dan Jabatan --}}
                            <div class="col-md-6">
                                <h5>Data Akun Pengguna</h5>
                                <hr>
                                @if ($anggota->user)
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $anggota->user->email) }}" required>
                                        </div>
                                        @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="username">Username <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $anggota->user->username) }}" required>
                                        </div>
                                        @error('username') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password Baru</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Min. 8 karakter">
                                        </div>
                                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                        @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password">
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info"><i class="icon fas fa-info-circle"></i> Anggota ini belum memiliki akun. Anda bisa membuatnya di sini.</div>
                                    <div class="form-group">
                                        <label for="email">Email untuk Akun Baru</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Contoh: nama@email.com">
                                        </div>
                                        @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="username">Username untuk Akun Baru</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" id="username" value="{{ old('username') }}" placeholder="Username untuk login">
                                        </div>
                                        @error('username') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password untuk Akun Baru</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Min. 8 karakter">
                                        </div>
                                        <small class="form-text text-muted">Wajib diisi jika membuat akun baru.</small>
                                        @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Ketik ulang password">
                                        </div>
                                    </div>
                                @endif
                                <h5 class="mt-4">Informasi Jabatan</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="role">Jabatan</label>
                                    <select class="form-control select2 @error('role') is-invalid @enderror" id="role" name="role">
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ optional($anggota->user)->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group" id="unit_usaha_group">
                                    <label for="unit_usaha_id">Unit Usaha</label>
                                    <select name="unit_usaha_id" class="form-control select2 @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id">
                                        <option value="">-- Pilih Unit Usaha --</option>
                                        @foreach($unitUsahas as $unit)
                                            <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id', $anggota->unit_usaha_id) == $unit->unit_usaha_id ? 'selected' : '' }}>
                                                {{ $unit->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_usaha_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="status_anggota">Status Anggota</label>
                                    <select name="status_anggota" class="form-control @error('status_anggota') is-invalid @enderror" id="status_anggota" required>
                                        <option value="Aktif" {{ old('status_anggota', $anggota->status_anggota) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Nonaktif" {{ old('status_anggota', $anggota->status_anggota) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    @error('status_anggota') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                        <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2();

        function toggleUnitUsaha() {
            var selectedRole = $('#role').val();
            var unitUsahaGroup = $('#unit_usaha_group');
            var unitUsahaSelect = $('#unit_usaha_id');

            if (selectedRole === 'manajer_unit_usaha' || selectedRole === 'admin_unit_usaha') {
                unitUsahaGroup.slideDown();
                unitUsahaSelect.prop('required', true);
            } else {
                unitUsahaGroup.slideUp();
                unitUsahaSelect.prop('required', false);
            }
        }
        toggleUnitUsaha();
        $('#role').change(toggleUnitUsaha);
    });
</script>
@endpush

@section('plugins.Select2', true)
