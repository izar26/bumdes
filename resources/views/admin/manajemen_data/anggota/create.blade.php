@extends('adminlte::page')

@section('title', 'Tambah Anggota')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Anggota</h1>
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
                                    <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>
                                    @error('nama_lengkap') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nik">NIK</label>
                                    <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" required>
                                    @error('nik') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                                    @error('alamat') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="no_telepon">No. Telepon</label>
                                    <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" required>
                                    @error('no_telepon') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="photo">Foto Profil</label>
                                    <input type="file" class="form-control-file @error('photo') is-invalid @enderror" id="photo" name="photo">
                                    <small class="form-text text-muted">Maks. 2MB. Format: JPG, PNG, GIF, SVG.</small>
                                    @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Kolom Kanan: Data Akun dan Jabatan --}}
                            <div class="col-md-6">
                                <h5>Data Akun Pengguna (Opsional)</h5>
                                <hr>
                                <p class="text-muted">Isi bagian ini jika ingin membuat akun login untuk anggota.</p>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>

                                <h5>Informasi Jabatan</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="role">Jabatan</label>
                                    <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group" id="unit_usaha_group" style="{{ !in_array(old('role'), ['manajer_unit_usaha', 'admin_unit_usaha']) ? 'display:none;' : '' }}">
                                    <label for="unit_usaha_id">Unit Usaha</label>
                                    <select class="form-control @error('unit_usaha_id') is-invalid @enderror" id="unit_usaha_id" name="unit_usaha_id">
                                        <option value="">-- Pilih Unit Usaha --</option>
                                        @foreach($unitUsahas as $unit)
                                            <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                                {{ $unit->nama_unit_usaha }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_usaha_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.manajemen-data.anggota.index') }}" class="btn btn-default">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function toggleUnitUsaha() {
            var selectedRole = $('#role').val();
            var unitUsahaGroup = $('#unit_usaha_group');
            if (selectedRole === 'manajer_unit_usaha' || selectedRole === 'admin_unit_usaha') {
                unitUsahaGroup.show();
                $('#unit_usaha_id').prop('required', true);
            } else {
                unitUsahaGroup.hide();
                $('#unit_usaha_id').prop('required', false);
            }
        }

        toggleUnitUsaha();

        $('#role').change(function() {
            toggleUnitUsaha();
        });

        // Set `required` attribute for email and password based on user input
        $('#email, #password').on('input', function() {
            if ($('#email').val() !== '' || $('#password').val() !== '') {
                $('#email').attr('required', true);
                $('#password').attr('required', true);
                $('#password_confirmation').attr('required', true);
            } else {
                $('#email').removeAttr('required');
                $('#password').removeAttr('required');
                $('#password_confirmation').removeAttr('required');
            }
        });

        // Optional: Pre-fill password confirmation when a password is typed
        $('#password').on('input', function() {
            if ($(this).val() !== '') {
                $('#password_confirmation').attr('required', true);
            } else {
                $('#password_confirmation').removeAttr('required');
            }
        });
    });
</script>
@stop
