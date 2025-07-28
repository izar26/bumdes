@extends('adminlte::page')

@section('title', 'Tambah Pengguna Baru')

@section('content_header')
    <h1>Tambah Pengguna Baru</h1>
@stop

@section('content')
    <div class="card">
        {{-- ... card header and alerts ... --}}
        <form action="{{ route('admin.user.store') }}" method="POST">
            @csrf
            <div class="card-body">
                {{-- ... existing form fields (name, username, email, password, password_confirmation) ... --}}

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
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Unit Usaha Assignment (initially hidden) --}}
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
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Pilih satu atau lebih unit usaha yang akan dikelola pengguna ini.</small>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Function to show/hide unit_usaha_assignment_group based on role selection
            function toggleUnitUsahaAssignment() {
                if ($('#role').val() === 'manajer_unit_usaha') {
                    $('#unit_usaha_assignment_group').show();
                } else {
                    $('#unit_usaha_assignment_group').hide();
                    $('#unit_usaha_ids').val(null).trigger('change'); // Clear selection
                }
            }

            // Initial call on page load
            toggleUnitUsahaAssignment();

            // Bind to change event of the role dropdown
            $('#role').on('change', function() {
                toggleUnitUsahaAssignment();
            });

            // Initialize Select2 for multi-select (requires Select2 library)
            // If you're using AdminLTE, Select2 is usually available or can be easily included.
            // If you don't have Select2, this will still work as a standard multi-select.
            $('#unit_usaha_ids').select2({
                placeholder: "-- Pilih Unit Usaha --",
                allowClear: true
            });
        });
    </script>
@stop
