@extends('adminlte::page')

@section('title', 'Daftar Akun Keuangan')

@section('content_header')
    <h1>Daftar Akun Keuangan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Chart of Accounts (COA)</h3>
                <a href="{{ route('admin.manajemen-data.akun.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Akun Baru
                </a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="akun-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Tipe Akun</th>
                            <th>Header?</th>
                            <th>Parent Akun</th>
                            <th width="220px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topLevelAkuns->sortBy('kode_akun') as $akun)
                            {{-- Panggil Blade Component di sini --}}
                            <x-akun-row :akun="$akun" :level="0" :tipe-akun-options="$tipeAkunOptions" />
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-book-open fa-2x mb-2"></i>
                                        <p>Belum ada akun keuangan yang terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-confirm-modal
        modal-id="deleteConfirmModal"
        title="Konfirmasi Hapus Akun"
        body="Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan."
        confirm-button-text="Hapus"
        confirm-button-class="btn-danger"
    />

@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
            white-space: nowrap;
            word-break: keep-all;
        }

        .actions {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                gap: 6px;
            }

            .actions .btn {
                width: 100%;
            }
        }

        /* Styles for inline editing */
        .editing-row .actions .edit-akun,
        .editing-row .actions .delete-form {
            display: none;
        }
        .editing-row .actions .save-akun,
        .editing-row .actions .cancel-edit {
            display: inline-block;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
            display: block;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Store original values when entering edit mode
            let originalRowContent = {};
            // Make sure these variables are passed from the controller (AkunController@index)
            const tipeAkunOptions = @json($tipeAkunOptions ?? []);
            const parentAkunsData = @json($akuns->filter(fn($a) => $a->is_header)->mapWithKeys(fn($a) => [$a->akun_id => $a->kode_akun . ' - ' . $a->nama_akun]) ?? []);

            // Handle Edit button click
            $('#akun-table').on('click', '.edit-akun', function() {
                const $row = $(this).closest('tr');
                const akunId = $row.data('akun-id');

                // If another row is being edited, cancel it first
                const $activeEditRow = $('.editing-row');
                if ($activeEditRow.length > 0 && $activeEditRow.data('akun-id') !== akunId) {
                    $('.cancel-edit[data-id="' + $activeEditRow.data('akun-id') + '"]').trigger('click');
                }

                $row.addClass('editing-row');

                originalRowContent[akunId] = {};

                $row.find('td[data-field]').each(function() {
                    const $td = $(this);
                    const field = $td.data('field');
                    const originalValue = $td.data('value') !== undefined ? $td.data('value') : $td.text().trim();
                    originalRowContent[akunId][field] = originalValue;

                    let inputHtml = '';
                    if (field === 'kode_akun') {
                        // Kode akun tetap readonly for inline edit
                        inputHtml = `<input type="text" class="form-control form-control-sm" value="${originalValue}" readonly>`;
                    } else if (field === 'nama_akun') {
                        inputHtml = `<input type="text" name="nama_akun" class="form-control form-control-sm" value="${originalValue}" required>`;
                    } else if (field === 'tipe_akun') {
                        inputHtml = `<select name="tipe_akun" class="form-control form-control-sm" required>`;
                        $.each(tipeAkunOptions, function(val, label) {
                            inputHtml += `<option value="${val}" ${originalValue == val ? 'selected' : ''}>${label}</option>`;
                        });
                        inputHtml += `</select>`;
                    } else if (field === 'is_header') {
                        inputHtml = `
                            <div class="form-check">
                                <input type="hidden" name="is_header" value="0">
                                <input type="checkbox" name="is_header" class="form-check-input" value="1" ${originalValue == 1 ? 'checked' : ''}>
                                <label class="form-check-label">Ya</label>
                            </div>
                        `;
                    } else if (field === 'parent_id') {
                        inputHtml = `<select name="parent_id" class="form-control form-control-sm">`;
                        inputHtml += `<option value="">-- Tidak Ada Akun Induk --</option>`;
                        $.each(parentAkunsData, function(val, label) {
                            // Prevent an account from being its own parent or a child of its own children
                            if (val != akunId) { // Basic check, more robust logic might be needed for complex hierarchies
                                inputHtml += `<option value="${val}" ${originalValue == val ? 'selected' : ''}>${label}</option>`;
                            }
                        });
                        inputHtml += `</select>`;
                    }
                    $td.html(inputHtml);
                });

                const $actionsTd = $row.find('.actions');
                $actionsTd.html(`
                    <button class="btn btn-success btn-xs save-akun" data-id="${akunId}">Simpan</button>
                    <button class="btn btn-secondary btn-xs cancel-edit" data-id="${akunId}">Batal</button>
                `);
            });

            // Handle Save button click
            $('#akun-table').on('click', '.save-akun', function() {
                const $row = $(this).closest('tr');
                const akunId = $row.data('akun-id');
                const originalKodeAkun = $row.data('original-kode_akun');
                const url = '{{ route('admin.manajemen-data.akun.update', ['akun' => ':akunId']) }}'.replace(':akunId', akunId);

                const data = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    kode_akun: originalKodeAkun,
                    nama_akun: $row.find('input[name="nama_akun"]').val(),
                    tipe_akun: $row.find('select[name="tipe_akun"]').val(),
                    is_header: $row.find('input[name="is_header"]').is(':checked') ? 1 : 0,
                    parent_id: $row.find('select[name="parent_id"]').val() || null,
                };

                $row.find('.error-message').remove();
                $row.find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    type: 'POST', // Use POST for _method:PUT
                    data: data,
                    success: function(response) {
                        // Re-render the row with updated data (or fetch it again if complex)
                        // A simpler approach for inline edit is to just update the displayed text
                        $row.find('td[data-field="kode_akun"]').html(originalKodeAkun); // Code shouldn't change
                        $row.find('td[data-field="nama_akun"]').text(response.data.nama_akun);
                        $row.find('td[data-field="tipe_akun"]').text(tipeAkunOptions[response.data.tipe_akun] || response.data.tipe_akun).data('value', response.data.tipe_akun);
                        $row.find('td[data-field="is_header"]').text(response.data.is_header ? 'Ya' : 'Tidak').data('value', response.data.is_header);
                        $row.find('td[data-field="parent_id"]').text(response.data.parent ? response.data.parent.kode_akun + ' - ' + response.data.parent.nama_akun : '-').data('value', response.data.parent_id);

                        // Revert action buttons
                        $row.find('.actions').html(`
                            <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}">Edit</button>
                            <form id="delete-akun-form-${akunId}" action="${'{{ route('admin.manajemen-data.akun.destroy', ['akun' => ':akunId']) }}'.replace(':akunId', akunId)}" method="POST" class="delete-form" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn btn-danger btn-xs"
                                        data-toggle="modal"
                                        data-target="#deleteConfirmModal"
                                        data-form-id="delete-akun-form-${akunId}">
                                    Hapus
                                </button>
                            </form>
                        `);

                        $row.removeClass('editing-row');
                        delete originalRowContent[akunId]; // Clear stored original content

                        toastr.success(response.message || 'Akun berhasil diperbarui!');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                const $input = $row.find(`[name="${field}"]`);
                                $input.addClass('is-invalid');
                                // Remove existing error messages for this field
                                $input.next('.error-message').remove();
                                $input.after(`<span class="error-message">${messages.join(', ')}</span>`);
                            });
                            toastr.error('Validasi gagal. Mohon periksa kembali input Anda.');
                        } else {
                            toastr.error('Terjadi kesalahan saat memperbarui akun. Silakan coba lagi.');
                            console.error('AJAX error:', xhr.responseText);
                        }
                    }
                });
            });

            // Handle Cancel button click
            $('#akun-table').on('click', '.cancel-edit', function() {
                const $row = $(this).closest('tr');
                const akunId = $row.data('akun-id');

                if (originalRowContent[akunId]) {
                    $row.find('td[data-field]').each(function() {
                        const $td = $(this);
                        const field = $td.data('field');
                        const originalVal = originalRowContent[akunId][field];
                        if (field === 'is_header') {
                            $td.text(originalVal == 1 ? 'Ya' : 'Tidak').data('value', originalVal);
                        } else if (field === 'tipe_akun') {
                            $td.text(tipeAkunOptions[originalVal] || originalVal).data('value', originalVal);
                        } else if (field === 'parent_id') {
                            $td.text(parentAkunsData[originalVal] || '-').data('value', originalVal);
                        } else {
                            $td.text(originalVal);
                        }
                    });
                }
                // Clear any error messages and invalid classes
                $row.find('.error-message').remove();
                $row.find('.is-invalid').removeClass('is-invalid');

                // Revert action buttons
                $row.find('.actions').html(`
                    <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}">Edit</button>
                    <form id="delete-akun-form-${akunId}" action="${'{{ route('admin.manajemen-data.akun.destroy', ['akun' => ':akunId']) }}'.replace(':akunId', akunId)}" method="POST" class="delete-form" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-danger btn-xs"
                                data-toggle="modal"
                                data-target="#deleteConfirmModal"
                                data-form-id="delete-akun-form-${akunId}">
                            Hapus
                        </button>
                    </form>
                `);

                $row.removeClass('editing-row');
                delete originalRowContent[akunId];
            });

            // Initial setup for Toastr
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
            }
        });
    </script>
@stop
