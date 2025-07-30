@extends('adminlte::page')

@section('title', 'Tambah Pengguna Baru')

@section('content_header')
    <h1>Tambah Pengguna Baru</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Penambahan Pengguna</h3>
        </div>
        <form action="{{ route('admin.manajemen-data.user.store') }}" method="POST">
            @csrf
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
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus maxlength="255">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" maxlength="255">
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Username akan digunakan untuk login jika diisi.</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required maxlength="255">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="8">
                </div>

                {{-- Role Selection --}}
                <div class="form-group">
                    <label for="role">Peran (Role)</label>
                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">-- Pilih Peran --</option>
                        @foreach ($rolesOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
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

                {{-- Unit Usaha Assignment (initially hidden, shown if 'manajer_unit_usaha' is selected) --}}
                <div class="form-group" id="unit_usaha_assignment_group" style="display: {{ old('role') == 'manajer_unit_usaha' ? 'block' : 'none' }};">
                    <label for="unit_usaha_ids">Unit Usaha yang Bertanggung Jawab</label>
                    <select name="unit_usaha_ids[]" id="unit_usaha_ids" class="form-control @error('unit_usaha_ids') is-invalid @enderror" multiple="multiple">
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->unit_usaha_id }}" {{ in_array($unitUsaha->unit_usaha_id, old('unit_usaha_ids', [])) ? 'selected' : '' }}>
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
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                <a href="{{ route('admin.manajemen-data.user.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    {{-- AdminLTE 3 usually includes Select2 CSS. If not, uncomment below. --}}
    <link rel="stylesheet" href="/vendor/select2/css/select2.min.css">
@stop

@section('js')
    <script src="/vendor/select2/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to show/hide unit_usaha_assignment_group based on role selection
            function toggleUnitUsahaAssignment() {
                if ($('#role').val() === 'manajer_unit_usaha') {
                    $('#unit_usaha_assignment_group').show();
                } else {
                    $('#unit_usaha_assignment_group').hide();
                    $('#unit_usaha_ids').val(null).trigger('change'); // Clear selection to prevent sending hidden data
                }
            }

            // Initial call on page load to set correct visibility based on old() value
            toggleUnitUsahaAssignment();

            // Bind to change event of the role dropdown
            $('#role').on('change', function() {
                toggleUnitUsahaAssignment();
            });
            $('#unit_usaha_ids').select2({
                placeholder: "-- Pilih Unit Usaha --",
                allowClear: true,
                // Optional: You might want to limit height if many options
                // dropdownCssClass: "bigdrop",
            });
        });
    </script>
@stop
