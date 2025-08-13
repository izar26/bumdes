@extends('adminlte::page')

@section('title', 'Edit Data Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Data Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
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
                            <div class="col-md-6">
                                <h5>Data Pribadi</h5><hr>
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required>
                                        @error('nama_lengkap') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="nik">NIK</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $anggota->nik) }}" required pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                                        @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                                        <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                                        @error('alamat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="no_telepon">No Telepon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                        <input type="tel" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" value="{{ old('no_telepon', $anggota->no_telepon) }}" required>
                                        @error('no_telepon') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span></div>
                                        <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" required>
                                            <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('jenis_kelamin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="photo">Ganti Foto Profil</label>
                                    <input type="file" name="photo" class="form-control-file @error('photo') is-invalid @enderror" id="photo">
                                    @error('photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    @if($anggota->photo)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($anggota->photo) }}" alt="Foto Profil" class="img-thumbnail" width="150" id="photo-preview">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle mr-2"></i>Data Keanggotaan & Akun</h5><hr>
                                <div class="form-group">
                                    <label for="role">Jabatan</label>
                                    <select name="role" class="form-control @error('role') is-invalid @enderror" id="role" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ optional($anggota->user)->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group" id="unit_usaha_group">
                                    <label for="unit_usaha_id">Unit Usaha</label>
                                    <select name="unit_usaha_id" class="form-control @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id">
                                        <option value="">-- Pilih Unit Usaha --</option>
                                        @foreach($unitUsahas as $unit)
                                            <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id', $anggota->unit_usaha_id) == $unit->unit_usaha_id ? 'selected' : '' }}>
                                                {{ $unit->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_usaha_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="status_anggota">Status Anggota</label>
                                    <select name="status_anggota" class="form-control @error('status_anggota') is-invalid @enderror" id="status_anggota" required>
                                        <option value="Aktif" {{ old('status_anggota', $anggota->status_anggota) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Nonaktif" {{ old('status_anggota', $anggota->status_anggota) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    @error('status_anggota') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <h5 class="mt-4">Manajemen Akun</h5><hr>
                                @if ($anggota->user)
                                    <div class="form-group">
                                        <label for="email">Email Akun</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email', $anggota->user->email) }}">
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Ubah Password</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="********">
                                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="********">
                                    </div>
                                @else
                                    <div class="alert alert-info"><i class="icon fas fa-info-circle"></i> Anggota ini belum memiliki akun. Anda bisa membuatnya di sini.</div>
                                    <div class="form-group">
                                        <label for="email">Email untuk Akun Baru</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Contoh: nama@email.com">
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password untuk Akun Baru</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="********">
                                        <small class="form-text text-muted">Wajib diisi jika membuat akun baru.</small>
                                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="********">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> simpan</button>
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
        $('#unit_usaha_id').select2({ placeholder: "-- Pilih Unit Usaha --", allowClear: true });

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

        // Live preview foto
        $('#photo').on('change', function(event) {
            var preview = $('#photo-preview');
            if (event.target.files && event.target.files[0]) {
                preview.show();
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                }
                reader.readAsDataURL(event.target.files[0]);
            } else {
                preview.hide();
            }
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });
</script>
@endpush
@section('plugins.Select2', true)
kenapa tidak ada emailnya di tabel kalo sudah ada akunnya?
