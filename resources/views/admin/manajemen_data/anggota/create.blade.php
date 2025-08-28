@extends('adminlte::page')

@section('title', 'Tambah Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Anggota</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
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
                    <h3 class="card-title">Form Tambah Anggota</h3>
                </div>
                <form role="form" action="{{ route('admin.manajemen-data.anggota.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
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
                                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap..." required>
                                    </div>
                                    @error('nama_lengkap') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nik">NIK</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" placeholder="Masukkan 16 digit NIK..." required pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                                    </div>
                                    @error('nik') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap..." required>{{ old('alamat') }}</textarea>
                                    </div>
                                    @error('alamat') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="no_telepon">No. Telepon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                        <input type="tel" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" placeholder="Contoh: 08123456789" required>
                                    </div>
                                    @error('no_telepon') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span></div>
                                        <select class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
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
                                        <img id="photo-preview" class="img-thumbnail" width="150" style="display:none;">
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Data Akun dan Jabatan --}}
                            <div class="col-md-6">
                                <h5>Data Akun Pengguna</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: nama@email.com" required>
                                    </div>
                                    @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Username untuk login" required>
                                    </div>
                                    @error('username') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Min. 8 karakter" required>
                                    </div>
                                    @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password" required>
                                    </div>
                                </div>

                                <h5 class="mt-4">Informasi Jabatan</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="role">Jabatan</label>
                                    <select class="form-control select2 @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group" id="unit_usaha_group" style="display:none;">
                                    <label for="unit_usaha_id">Unit Usaha (Wajib untuk Manajer/Admin Unit)</label>
                                    <select class="form-control @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id" name="unit_usaha_id">
                                        <option value="">-- Pilih Unit Usaha --</option>
                                        @foreach($unitUsahas as $unit)
                                            <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                                {{ $unit->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_usaha_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                        <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-default">Batal</a>
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

        // Fungsi untuk menampilkan/menyembunyikan Unit Usaha
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
