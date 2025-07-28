@extends('adminlte::page')

@section('title', 'Daftar Akun Keuangan')

@section('content_header')
    <h1>Daftar Akun Keuangan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chart of Accounts (COA)</h3>
            <div class="card-tools">
                <a href="{{ route('admin.akun.create') }}" class="btn btn-primary btn-sm">Tambah Akun Baru</a>
            </div>
        </div>
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

            <table class="table table-bordered table-striped" id="akun-table">
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe Akun</th>
                        <th>Header?</th>
                        <th>Parent Akun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        function renderAkunRow($akun, $level = 0, $tipeAkunOptions, $parentAkuns) {
                            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level * 2); // More indentation
                            $akunId = $akun->akun_id;
                            $routeUpdate = route('admin.akun.update', $akunId);
                            $routeDestroy = route('admin.akun.destroy', $akunId);
                    @endphp
                            <tr data-akun-id="{{ $akunId }}" data-original-kode_akun="{{ $akun->kode_akun }}" class="akun-row">
                                <td data-field="kode_akun">{{ $indent }}{{ $akun->kode_akun }}</td>
                                <td data-field="nama_akun">{{ $indent }}{{ $akun->nama_akun }}</td>
                                <td data-field="tipe_akun" data-value="{{ $akun->tipe_akun }}">{{ $tipeAkunOptions[$akun->tipe_akun] ?? $akun->tipe_akun }}</td>
                                <td data-field="is_header" data-value="{{ $akun->is_header }}">{{ $akun->is_header ? 'Ya' : 'Tidak' }}</td>
                                <td data-field="parent_id" data-value="{{ $akun->parent_id }}">
                                    {{ $akun->parent ? $akun->parent->kode_akun . ' - ' . $akun->parent->nama_akun : '-' }}
                                </td>
                                <td class="actions">
                                    <button class="btn btn-warning btn-xs edit-akun" data-id="{{ $akunId }}">Edit</button>
                                    <form action="{{ $routeDestroy }}" method="POST" class="delete-form" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            {{-- Recursively render children --}}
                            @if ($akun->children->count() > 0)
                                @foreach ($akun->children->sortBy('kode_akun') as $child)
                                    @php renderAkunRow($child, $level + 1, $tipeAkunOptions, $parentAkuns); @endphp
                                @endforeach
                            @endif
                    @php
                        }
                    @endphp

                    @forelse ($topLevelAkuns->sortBy('kode_akun') as $akun)
                        @php renderAkunRow($akun, 0, \App\Models\Akun::getTipeAkunOptions(), $akuns); @endphp
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada akun keuangan yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    {{-- Add some basic CSS for inline editing --}}
    <style>
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
            // Store original values when entering edit mode
            let originalRowContent = {};
            const tipeAkunOptions = @json(\App\Models\Akun::getTipeAkunOptions());
            const parentAkunsData = @json($akuns->filter(fn($a) => $a->is_header)->mapWithKeys(fn($a) => [$a->akun_id => $a->kode_akun . ' - ' . $a->nama_akun])); // Only header accounts can be parents

            // Handle Edit button click
            $('#akun-table').on('click', '.edit-akun', function() {
                const $row = $(this).closest('tr');
                const akunId = $row.data('akun-id');

                // If another row is being edited, cancel it first
                const $activeEditRow = $('.editing-row');
                if ($activeEditRow.length > 0 && $activeEditRow.data('akun-id') !== akunId) {
                    $('.cancel-edit').trigger('click');
                }

                $row.addClass('editing-row'); // Add class to manage button visibility

                originalRowContent[akunId] = {}; // Initialize for this row

                // Convert td content to input fields
                $row.find('td[data-field]').each(function() {
                    const $td = $(this);
                    const field = $td.data('field');
                    const originalValue = $td.data('value') !== undefined ? $td.data('value') : $td.text().trim(); // Use data-value if available
                    originalRowContent[akunId][field] = originalValue; // Store original content

                    let inputHtml = '';
                    if (field === 'kode_akun') {
                        // Kode Akun is typically not editable, or only editable with great care.
                        // For inline edit, making it readonly is safer.
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
                            // Prevent an account from becoming its own parent or a child's parent
                            if (val != akunId) { // Prevent self-referencing
                                // More complex check for circular reference (children) needed for robust solution
                                inputHtml += `<option value="${val}" ${originalValue == val ? 'selected' : ''}>${label}</option>`;
                            }
                        });
                        inputHtml += `</select>`;
                    }
                    $td.html(inputHtml);
                });

                // Replace buttons
                const $actionsTd = $row.find('.actions');
                $actionsTd.html(`
                    <button class="btn btn-success btn-xs save-akun" data-id="${akunId}">Simpan</button>
                    <button class="btn btn-secondary btn-xs cancel-edit" data-id="${akunId}">Batal</button>
                `);
            });

            $('#akun-table').on('click', '.save-akun', function() {
                const $row = $(this).closest('tr');
                const akunId = $row.data('akun-id');
                const originalKodeAkun = $row.data('original-kode_akun');
                const url = `/admin/akun/${akunId}`; // Matches PUT /admin/akun/{akun_id}

                const data = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    kode_akun: originalKodeAkun, // Send original kode_akun, as it's readonly
                    nama_akun: $row.find('input[name="nama_akun"]').val(),
                    tipe_akun: $row.find('select[name="tipe_akun"]').val(),
                    is_header: $row.find('input[name="is_header"]').is(':checked') ? 1 : 0,
                    parent_id: $row.find('select[name="parent_id"]').val() || null, // Ensure null if empty
                };

                // Clear existing error messages
                $row.find('.error-message').remove();
                $row.find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    type: 'POST', // Use POST for _method:PUT
                    data: data,
                    success: function(response) {
                        // Update table cells with new data
                        $row.find('td[data-field="kode_akun"]').html($row.data('original-kode_akun')); // Kode akun always original
                        $row.find('td[data-field="nama_akun"]').text(response.data.nama_akun);
                        $row.find('td[data-field="tipe_akun"]').text(tipeAkunOptions[response.data.tipe_akun] || response.data.tipe_akun).data('value', response.data.tipe_akun);
                        $row.find('td[data-field="is_header"]').text(response.data.is_header ? 'Ya' : 'Tidak').data('value', response.data.is_header);
                        $row.find('td[data-field="parent_id"]').text(response.data.parent ? response.data.parent.kode_akun + ' - ' + response.data.parent.nama_akun : '-').data('value', response.data.parent_id);

                        // Revert buttons
                        $row.find('.actions').html(`
                            <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}">Edit</button>
                            <form action="/admin/akun/${akunId}" method="POST" class="delete-form" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">Hapus</button>
                            </form>
                        `);

                        $row.removeClass('editing-row');
                        delete originalRowContent[akunId]; // Clean up stored original content

                        // Show success message
                        toastr.success(response.message || 'Akun berhasil diperbarui!');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation errors
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                // Find the corresponding input and add error class/message
                                const $input = $row.find(`[name="${field}"]`);
                                $input.addClass('is-invalid');
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

                // Revert all fields to original content
                if (originalRowContent[akunId]) {
                    $row.find('td[data-field]').each(function() {
                        const $td = $(this);
                        const field = $td.data('field');
                        const originalVal = originalRowContent[akunId][field];
                        // Special handling for 'is_header' and 'tipe_akun' to display label, not value
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


                // Revert buttons
                $row.find('.actions').html(`
                    <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}">Edit</button>
                    <form action="/admin/akun/${akunId}" method="POST" class="delete-form" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')">Hapus</button>
                    </form>
                `);

                $row.removeClass('editing-row');
                delete originalRowContent[akunId];
            });

            // For delete button: ensure form submission works
            $('#akun-table').on('submit', '.delete-form', function(e) {
                // The confirm dialog is already in the button's onclick
                // This is just to ensure the form submission itself is handled
            });

            // Initialize Toastr (AdminLTE comes with Toastr.js)
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
