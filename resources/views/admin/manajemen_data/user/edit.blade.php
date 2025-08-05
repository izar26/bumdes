@extends('adminlte::page')

@section('title', 'Edit Pengguna: ' . $user->name)

@section('content_header')
    <h1>Edit Pengguna: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Pengguna</h3>
        </div>
        <form action="{{ route('admin.manajemen-data.user.update', $user->user_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
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

                {{-- Basic User Information Fields --}}
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus maxlength="255">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" maxlength="255">
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Username akan digunakan untuk login jika diisi.</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required maxlength="255">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8">
                </div>

                {{-- Role Selection --}}
                <div class="form-group">
                    <label for="role">Peran (Role)</label>
                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">-- Pilih Peran --</option>
                        @foreach ($rolesOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $user->role) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- User Active Status --}}
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktifkan Pengguna</label>
                    </div>
                    @error('is_active')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Nonaktifkan untuk mencegah pengguna login.</small>
                </div>

                {{-- Bagian ini sudah diperbaiki untuk konsisten dengan create.blade.php --}}
                <div class="form-group" id="unit_usaha_assignment_group" style="display: {{ in_array(old('role', $user->role), ['manajer_unit_usaha', 'admin_unit_usaha']) ? 'block' : 'none' }};">
                    <label for="unit_usaha_ids">Unit Usaha yang Bertanggung Jawab</label>
                    <select name="unit_usaha_ids[]" id="unit_usaha_ids" class="form-control @error('unit_usaha_ids') is-invalid @enderror" multiple="multiple">
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->unit_usaha_id }}" {{ in_array($unitUsaha->unit_usaha_id, old('unit_usaha_ids', $assignedUnitUsahaIds)) ? 'selected' : '' }}>
                                {{ $unitUsaha->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_usaha_ids')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Pilih satu atau lebih unit usaha yang akan dikelola pengguna ini.</small>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Perbarui Pengguna</button>
                <a href="{{ route('admin.manajemen-data.user.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="/vendor/select2/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            function toggleUnitUsahaAssignment() {
                const selectedRole = $('#role').val();
                const assignableRoles = ['manajer_unit_usaha', 'admin_unit_usaha'];

                if (assignableRoles.includes(selectedRole)) {
                    $('#unit_usaha_assignment_group').show();
                } else {
                    $('#unit_usaha_assignment_group').hide();
                    $('#unit_usaha_ids').val(null).trigger('change');
                }
            }

            toggleUnitUsahaAssignment();

            $('#role').on('change', function() {
                toggleUnitUsahaAssignment();
            });

            $('#unit_usaha_ids').select2({
                placeholder: "-- Pilih Unit Usaha --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@stop
