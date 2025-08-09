@extends('adminlte::page')

@section('title', 'User Profile')

@section('content_header')
    <h1>User Profile</h1>
    <div class="float-right">
        <span class="badge badge-{{ $user->is_profile_complete ? 'success' : 'warning' }}">
            {{ $user->is_profile_complete ? 'Profile Complete' : 'Profile Incomplete' }}
        </span>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header p-2">
                    <ul class="nav nav-pills nav-fill">
                        <li class="nav-item">
                            <a class="nav-link {{ session('tab') === 'account' || !session('tab') ? 'active' : '' }}" href="#account" data-toggle="tab">
                                <i class="fas fa-user-circle mr-2"></i>Account Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ session('tab') === 'personal' ? 'active' : '' }}" href="#personal" data-toggle="tab">
                                <i class="fas fa-id-card mr-2"></i>Personal Information
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
                                            <img src="{{ $user->photo ? asset('storage/photos/' . $user->photo) : asset('vendor/adminlte/dist/img/avatar.png') }}"
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
                            <form action="{{ route('profile.update-personal') }}" method="POST" enctype="multipart/form-data">
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
                                                    <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $anggota->nik) }}" placeholder="Enter 16-digit National ID" required>
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
                                                        <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-Laki</option>
                                                        <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
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
                                            <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" placeholder="Enter complete address" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                                        </div>
                                        @error('alamat')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="no_telepon" class="font-weight-bold">Nomber</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary"><i class="fas fa-phone text-white"></i></span>
                                                    </div>
                                                    <input type="text" name="no_telepon" id="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon', $anggota->no_telepon) }}" placeholder="Example: 081234567890" required>
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
                                    @if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha']))
                                        <div class="form-group">
                                            <label for="unit_usaha_id" class="font-weight-bold">Business Unit</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-primary"><i class="fas fa-store text-white"></i></span>
                                                </div>
                                                <select name="unit_usaha_id" id="unit_usaha_id" class="form-control @error('unit_usaha_id') is-invalid @enderror" {{ $anggota->unit_usaha_id ? 'disabled' : '' }}>
                                                    <option value="">Select Business Unit</option>
                                                    @foreach($unitUsahas as $unitUsaha)
                                                        <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id', $anggota->unit_usaha_id) == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                                            {{ $unitUsaha->nama_unit }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($anggota->unit_usaha_id)
                                                <input type="hidden" name="unit_usaha_id" value="{{ $anggota->unit_usaha_id }}">
                                            @endif
                                            @error('unit_usaha_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="foto" class="font-weight-bold">Profile Photo</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('foto') is-invalid @enderror" id="foto" name="foto">
                                            <label class="custom-file-label" for="foto">Choose file...</label>
                                        </div>
                                        @error('foto')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @if($anggota->foto)
                                            <div class="mt-2">
                                                <label>Current Photo:</label>
                                                <div class="d-flex align-items-center">
                                                    <a href="{{ asset('storage/photos/' . $anggota->foto) }}" target="_blank" class="btn btn-sm btn-outline-primary mr-2">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                    <span class="text-muted">{{ $anggota->foto }}</span>
                                                </div>
                                            </div>
                                        @endif
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

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                const activeTab = $(e.target).attr('href').substring(1);
                sessionStorage.setItem('activeTab', activeTab);
            });

            const activeTab = sessionStorage.getItem('activeTab') || 'account';
            $('a[href="#' + activeTab + '"]').tab('show');

            const firstInvalid = $('.tab-pane.active .is-invalid').first();
            if (firstInvalid.length) {
                firstInvalid.focus();
            }

            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@stop

@section('css')
    <style>
        .profile-user-img {
            transition: all 0.3s ease;
        }
        .profile-user-img:hover {
            transform: scale(1.05);
            border-color: #007bff !important;
        }
        .card-primary.card-outline {
            border-top: 3px solid #007bff;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
            font-weight: 600;
        }
        .input-group-text.bg-primary {
            border-color: #007bff;
        }
        .btn-primary {
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
        }
        .toggle-password {
            border-color: #ced4da;
        }
        .toggle-password:hover {
            background-color: #e9ecef;
        }
        .custom-file-label::after {
            content: "Browse";
        }
    </style>
@stop
