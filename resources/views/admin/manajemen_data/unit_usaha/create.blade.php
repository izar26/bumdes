@extends('adminlte::page')

@section('title', 'Tambah Unit Usaha')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Unit Usaha Baru</h1>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.manajemen-data.unit_usaha.index') }}">Unit Usaha</a></li>
            <li class="breadcrumb-item active">Tambah Baru</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-business-time mr-2"></i>
                        Tambah Unit Usaha Baru
                    </h3>
                </div>
                <form action="{{ route('admin.manajemen-data.unit_usaha.store') }}" method="POST" id="unitUsahaForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_unit" class="font-weight-bold">
                                Nama Unit Usaha <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_unit"
                                   class="form-control @error('nama_unit') is-invalid @enderror"
                                   id="nama_unit"
                                   placeholder="Contoh: Toko Maju Jaya, Bengkel Sejahtera"
                                   value="{{ old('nama_unit') }}"
                                   required
                                   maxlength="100">
                            @error('nama_unit')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="form-group">
                            <label for="jenis_usaha" class="font-weight-bold">
                                Jenis Usaha <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_usaha"
                                    class="form-control select2 @error('jenis_usaha') is-invalid @enderror"
                                    id="jenis_usaha"
                                    required>
                                <option value="">Pilih Jenis Usaha</option>
                                <option value="Perdagangan" {{ old('jenis_usaha') == 'Perdagangan' ? 'selected' : '' }}>Perdagangan</option>
                                <option value="Jasa" {{ old('jenis_usaha') == 'Jasa' ? 'selected' : '' }}>Jasa</option>
                                <option value="Manufaktur" {{ old('jenis_usaha') == 'Manufaktur' ? 'selected' : '' }}>Manufaktur</option>
                                <option value="Pertanian" {{ old('jenis_usaha') == 'Pertanian' ? 'selected' : '' }}>Pertanian</option>
                                <option value="Lainnya" {{ old('jenis_usaha') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('jenis_usaha')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi_usaha" class="font-weight-bold">
                                Deskripsi Singkat Usaha
                            </label>
                            <textarea name="deskripsi_usaha"
                                      class="form-control @error('deskripsi_usaha') is-invalid @enderror"
                                      id="deskripsi_usaha"
                                      rows="2"
                                      placeholder="Deskripsi singkat tentang unit usaha ini">{{ old('deskripsi_usaha') }}</textarea>
                            @error('deskripsi_usaha')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai_operasi" class="font-weight-bold">
                                        Tanggal Mulai Operasi
                                    </label>
                                    <div class="input-group date" id="tanggalMulaiOperasi" data-target-input="nearest">
                                        <input type="text" name="tanggal_mulai_operasi"
                                               class="form-control datetimepicker-input @error('tanggal_mulai_operasi') is-invalid @enderror"
                                               data-target="#tanggalMulaiOperasi"
                                               value="{{ old('tanggal_mulai_operasi') }}"
                                               placeholder="Pilih tanggal"/>
                                        <div class="input-group-append" data-target="#tanggalMulaiOperasi" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    @error('tanggal_mulai_operasi')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status_operasi" class="font-weight-bold">
                                        Status Operasi <span class="text-danger">*</span>
                                    </label>
                                    <select name="status_operasi"
                                            id="status_operasi"
                                            class="form-control @error('status_operasi') is-invalid @enderror"
                                            required>
                                        <option value="Aktif" {{ old('status_operasi') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status_operasi') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="Dalam Pengembangan" {{ old('status_operasi') == 'Dalam Pengembangan' ? 'selected' : '' }}>Dalam Pengembangan</option>
                                    </select>
                                    @error('status_operasi')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="penanggung_jawab_ids" class="font-weight-bold">
                                Penanggung Jawab <span class="text-danger">*</span>
                            </label>
                            <select name="penanggung_jawab_ids[]"
                                    id="penanggung_jawab_ids"
                                    class="form-control select2 @error('penanggung_jawab_ids') is-invalid @enderror"
                                    multiple="multiple"
                                    style="width: 100%;"
                                    required>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" {{ in_array($user->user_id, old('penanggung_jawab_ids', [])) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->getRoleNames()->first() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('penanggung_jawab_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Pilih satu atau lebih penanggung jawab untuk unit usaha ini. <br> Jika Tidak Ada Buat User Baru Dengan Role Admin Unit
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="alamat_usaha" class="font-weight-bold">
                                Alamat Usaha
                            </label>
                            <textarea name="alamat_usaha"
                                      class="form-control @error('alamat_usaha') is-invalid @enderror"
                                      id="alamat_usaha"
                                      rows="2"
                                      placeholder="Alamat lengkap unit usaha">{{ old('alamat_usaha') }}</textarea>
                            @error('alamat_usaha')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-secondary mr-2" onclick="window.location.href='{{ route('admin.manajemen-data.unit_usaha.index') }}'">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css"/>
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #006fe6;
            color: white;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.7);
        }
        .card-header {
            border-bottom: none;
        }
        .invalid-feedback.d-block {
            display: block !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Pilih opsi",
                allowClear: true
            });

            // Initialize datepicker
            $('#tanggalMulaiOperasi').datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'id',
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'far fa-times-circle'
                }
            });

            // Form validation
            $('#unitUsahaForm').validate({
                rules: {
                    nama_unit: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    jenis_usaha: {
                        required: true
                    },
                    'penanggung_jawab_ids[]': {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    nama_unit: {
                        required: "Nama unit usaha wajib diisi",
                        minlength: "Nama unit usaha minimal 3 karakter",
                        maxlength: "Nama unit usaha maksimal 100 karakter"
                    },
                    jenis_usaha: {
                        required: "Jenis usaha wajib dipilih"
                    },
                    'penanggung_jawab_ids[]': {
                        required: "Pilih minimal 1 penanggung jawab",
                        minlength: "Pilih minimal 1 penanggung jawab"
                    }
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@stop
