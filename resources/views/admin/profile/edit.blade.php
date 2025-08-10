@extends('adminlte::page')

@section('title', 'User Profile')

@section('content_header')
    <h1>User Profile</h1>
    @if ($user->anggota)
        <div class="float-right">
            <span class="badge badge-{{ $user->anggota->is_profile_complete ? 'success' : 'warning' }}">
                {{ $user->anggota->is_profile_complete ? 'Profile Complete' : 'Profile Incomplete' }}
            </span>
        </div>
    @endif
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header p-2">
                    <ul class="nav nav-pills nav-fill">
                        <li class="nav-item">
                            <a class="nav-link {{ session('tab') === 'account' || !session('tab') ? 'active' : '' }}" href="#account" data-toggle="tab">
                                <i class="fas fa-user-circle mr-2"></i>Akun Setting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ session('tab') === 'personal' ? 'active' : '' }}" href="#personal" data-toggle="tab">
                                <i class="fas fa-id-card mr-2"></i>Data Diri
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- Account Tab --}}
                        <div class="tab-pane {{ session('tab') === 'account' || !session('tab') ? 'active' : '' }}" id="account">
                            <form action="{{ route('profile.update-account') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    @include('components.status-alerts', ['tab' => 'account'])
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            {{-- Path foto diperbaiki untuk mengambil dari relasi anggota --}}
                                            <img src="{{ $user->anggota?->photo ? asset('storage/' . $user->anggota->photo) : asset('vendor/adminlte/dist/img/avatar.png') }}"
                                                 class="profile-user-img img-fluid img-circle shadow-lg"
                                                 alt="User Photo"
                                                 id="profileImagePreview"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #6c757d;">
                                            <div class="position-absolute bottom-0 right-0">
                                                <label for="photo" class="btn btn-sm btn-primary rounded-circle shadow"
                                                       style="width: 40px; height: 40px; cursor: pointer;"
                                                       title="Change photo" data-toggle="tooltip">
                                                    <i class="fas fa-camera"></i>
                                                </label>
                                                <input type="file" id="photo" name="photo" class="d-none" accept="image/*">
                                            </div>
                                        </div>
                                        @error('photo')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="font-weight-bold">Nama Lengkap</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary"><i class="fas fa-user text-white"></i></span>
                                            </div>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="font-weight-bold">Email</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary"><i class="fas fa-envelope text-white"></i></span>
                                            </div>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                        </div>
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="font-weight-bold">Password Baru</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary"><i class="fas fa-lock text-white"></i></span>
                                            </div>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                                            </div>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Biarkan Kosong Jika Tidak Ingin Diganti</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation" class="font-weight-bold">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary"><i class="fas fa-lock text-white"></i></span>
                                            </div>
                                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right bg-white">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-2"></i> Update Account
                                    </button>
                                </div>
                            </form>
                        </div>
                        {{-- Personal Information Tab --}}
                        <div class="tab-pane {{ session('tab') === 'personal' ? 'active' : '' }}" id="personal">
                            <form action="{{ route('profile.update-personal') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    @include('components.status-alerts', ['tab' => 'personal'])
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nik" class="font-weight-bold">NIK</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary"><i class="fas fa-id-card text-white"></i></span>
                                                    </div>
                                                    <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $user->anggota->nik ?? '') }}" placeholder="Enter 16-digit National ID" required>
                                                </div>
                                                @error('nik')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="jenis_kelamin" class="font-weight-bold">Gender</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary"><i class="fas fa-venus-mars text-white"></i></span>
                                                    </div>
                                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                                        <option value="Laki-laki" {{ old('jenis_kelamin', $user->anggota->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-Laki</option>
                                                        <option value="Perempuan" {{ old('jenis_kelamin', $user->anggota->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                    </select>
                                                </div>
                                                @error('jenis_kelamin')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="alamat" class="font-weight-bold">Alamat</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary"><i class="fas fa-map-marker-alt text-white"></i></span>
                                            </div>
                                            <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" placeholder="Enter complete address" required>{{ old('alamat', $user->anggota->alamat ?? '') }}</textarea>
                                        </div>
                                        @error('alamat')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="no_telepon" class="font-weight-bold">Nomor Telepon</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary"><i class="fas fa-phone text-white"></i></span>
                                                    </div>
                                                    <input type="text" name="no_telepon" id="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon', $user->anggota->no_telepon ?? '') }}" placeholder="Example: 081234567890" required>
                                                </div>
                                                @error('no_telepon')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="jabatan" class="font-weight-bold">Posisi/Jabatan</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary"><i class="fas fa-briefcase text-white"></i></span>
                                                    </div>
                                                    <input type="text" name="jabatan" id="jabatan" class="form-control" value="{{ ucwords(str_replace('_', ' ', $user->getRoleNames()->first())) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right bg-white">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-2"></i> Update Personal Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $('#photo').change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profileImagePreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            $('#photo_personal').change(function() {
                 let fileName = $(this).val().split('\\').pop();
                 $(this).next('.custom-file-label').html(fileName);
            });

            $('.toggle-password').click(function() {
                const input = $(this).closest('.input-group').find('input');
                const icon = $(this).find('i');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Handle tab switching based on session or validation errors
            const activeTab = "{{ session('tab') }}" || 'account';
            $('a[href="#' + activeTab + '"]').tab('show');

            // Handle invalid fields after form submission
            if ($('.tab-pane.active .is-invalid').length) {
                 const invalidTabId = $('.is-invalid').closest('.tab-pane').attr('id');
                 $('a[href="#' + invalidTabId + '"]').tab('show');
                 $('a[href="#' + invalidTabId + '"]').focus();
            }
        });
    console.log(@json($user->anggota?->photo ? asset('storage/' . $user->anggota->photo) : asset('vendor/adminlte/dist/img/avatar.png')));

    </script>
@stop
