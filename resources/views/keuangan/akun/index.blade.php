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
                    <a href="{{ route('keuangan.akun.create') }}"
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
                    {{-- Tambahkan class "display" untuk DataTables --}}
                    <table class="table table-bordered table-striped display" id="akun-table">
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
                            {{-- Iterasi semua akun, tidak hanya top-level, agar DataTables dapat memproses semua baris --}}
                            {{-- Pastikan variabel `$akuns` berisi semua akun yang ingin ditampilkan, diurutkan jika perlu --}}
                            @forelse ($akuns->sortBy('kode_akun') as $akun)
                                {{-- Tambahkan class 'is-header-row' jika akun adalah header --}}
                                <tr data-akun-id="{{ $akun->akun_id }}"
                                    data-original-kode_akun="{{ $akun->kode_akun }}"
                                    class="{{ $akun->is_header ? 'is-header-row' : '' }}">
                                    <td data-field="kode_akun" data-value="{{ $akun->kode_akun }}">
                                        {{ $akun->kode_akun }}
                                    </td>
                                    <td data-field="nama_akun" data-value="{{ $akun->nama_akun }}">
                                        {{-- Tambahkan indentasi visual jika akun memiliki parent_id --}}
                                        @if ($akun->parent_id)
                                            @php
                                                // Hitung level indentasi berdasarkan kedalaman hierarki
                                                // Ini mungkin memerlukan rekursi di controller atau model
                                                // Untuk tujuan tampilan sederhana di sini, kita bisa membedakan berdasarkan parent_id saja.
                                                // Jika ingin indentasi akurat sesuai level, logic di controller/model perlu dimodifikasi
                                                $indentation = $akun->parent_id ? '  ' : ''; // Contoh sederhana: 2 spasi jika punya parent
                                            @endphp
                                            <span style="padding-left: {{ ($akun->depth ?? 0) * 20 }}px;"></span>
                                            <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>
                                        @endif
                                        {{ $akun->nama_akun }}
                                    </td>
                                    <td data-field="tipe_akun" data-value="{{ $akun->tipe_akun }}">
                                        {{ $tipeAkunOptions[$akun->tipe_akun] ?? $akun->tipe_akun }}
                                    </td>
                                    <td data-field="is_header" data-value="{{ $akun->is_header ? 1 : 0 }}">
                                        @if ($akun->is_header)
                                            <span class="badge badge-success">Ya</span>
                                        @else
                                            <span class="badge badge-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td data-field="parent_id" data-value="{{ $akun->parent_id }}">
                                        {{ $akun->parent ? $akun->parent->kode_akun . ' - ' . $akun->parent->nama_akun : '-' }}
                                    </td>
                                    <td class="actions">
                                        <button class="btn btn-warning btn-xs edit-akun" data-id="{{ $akun->akun_id }}"
                                            data-toggle="tooltip" title="Edit Akun">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form id="delete-akun-form-{{ $akun->akun_id }}"
                                            action="{{ route('keuangan.akun.destroy', ['akun' => $akun->akun_id]) }}"
                                            method="POST" class="delete-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                                                data-target="#deleteConfirmModal"
                                                data-form-id="delete-akun-form-{{ $akun->akun_id }}"
                                                title="Hapus Akun">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
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
        {{-- DataTables CSS --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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

            /* Gaya untuk membedakan akun header */
            .is-header-row {
                font-weight: bold;
                background-color: #f2f2f2; /* Warna latar belakang abu-abu muda */
            }
            .is-header-row td {
                border-top: 1px solid #dee2e6 !important; /* Tambahkan garis atas untuk pemisah visual */
                border-bottom: 1px solid #dee2e6 !important; /* Tambahkan garis bawah */
            }
        </style>
    @stop

    @section('js')
        {{-- jQuery and DataTables JS --}}
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $(document).ready(function() {
                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Inisialisasi DataTables
                $('#akun-table').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Bahasa Indonesia
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": [5] } // Nonaktifkan sorting untuk kolom aksi
                    ]
                });


                // Store original values when entering edit mode
                let originalRowContent = {};
                const tipeAkunOptions = @json($tipeAkunOptions ?? []);
                // Perlu memastikan `$akuns` di sini adalah *semua* akun, bukan hanya topLevelAkuns
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
                    const url = '{{ route('keuangan.akun.update', ['akun' => ':akunId']) }}'.replace(':akunId', akunId);

                    const data = {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        kode_akun: originalKodeAkun, // Kode akun tidak bisa diubah lewat inline edit
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
                            // Mendapatkan instance DataTable
                            const dataTable = $('#akun-table').DataTable();
                            const rowNode = $row[0]; // Dapatkan DOM node dari baris
                            const rowData = dataTable.row(rowNode).data(); // Dapatkan data baris yang ada di DataTable

                            // Update data di DataTable
                            // Perhatikan bahwa ini akan me-redraw baris, jadi pastikan data yang dikirim konsisten
                            // dengan format yang diharapkan DataTable
                            rowData[0] = originalKodeAkun; // Kode Akun
                            rowData[1] = response.data.nama_akun; // Nama Akun
                            rowData[2] = tipeAkunOptions[response.data.tipe_akun] || response.data.tipe_akun; // Tipe Akun
                            rowData[3] = response.data.is_header ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>'; // Header?
                            rowData[4] = response.data.parent ? response.data.parent.kode_akun + ' - ' + response.data.parent.nama_akun : '-'; // Parent Akun

                            // Perbarui data di DOM secara manual untuk memastikan indentasi atau badge tetap
                            $row.find('td[data-field="nama_akun"]').html(
                                (response.data.parent_id ? '<span style="padding-left: ' + (response.data.depth * 20 || 20) + 'px;"></span><i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>' : '') + response.data.nama_akun
                            );
                            $row.find('td[data-field="tipe_akun"]').text(tipeAkunOptions[response.data.tipe_akun] || response.data.tipe_akun).data('value', response.data.tipe_akun);
                            $row.find('td[data-field="is_header"]').html(response.data.is_header ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>').data('value', response.data.is_header);
                            $row.find('td[data-field="parent_id"]').text(response.data.parent ? response.data.parent.kode_akun + ' - ' + response.data.parent.nama_akun : '-').data('value', response.data.parent_id);

                            // Update DataTables internal data for the row
                            dataTable.row(rowNode).data(rowData).draw(false); // redraw false agar tidak mereset paging/sorting

                            // Set/unset header class
                            if (response.data.is_header) {
                                $row.addClass('is-header-row');
                            } else {
                                $row.removeClass('is-header-row');
                            }


                            // Revert action buttons
                            $row.find('.actions').html(`
                                <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}" data-toggle="tooltip" title="Edit Akun">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form id="delete-akun-form-${akunId}" action="${'{{ route('keuangan.akun.destroy', ['akun' => ':akunId']) }}'.replace(':akunId', akunId)}" method="POST" class="delete-form" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-danger btn-xs"
                                            data-toggle="modal"
                                            data-target="#deleteConfirmModal"
                                            data-form-id="delete-akun-form-${akunId}"
                                            title="Hapus Akun">
                                        <i class="fas fa-trash"></i> Hapus
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
                                $td.html(originalVal == 1 ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>').data('value', originalVal);
                            } else if (field === 'tipe_akun') {
                                $td.text(tipeAkunOptions[originalVal] || originalVal).data('value', originalVal);
                            } else if (field === 'parent_id') {
                                // Find parent data from the parentAkunsData array
                                const parentDisplay = parentAkunsData[originalVal] || '-';
                                $td.text(parentDisplay).data('value', originalVal);
                            } else if (field === 'nama_akun') {
                                // Re-apply indentation for nama_akun if it had a parent
                                const originalParentId = originalRowContent[akunId]['parent_id'];
                                const originalDepth = originalRowContent[akunId]['depth'] || 0; // Assuming depth is stored or can be calculated
                                const indentHtml = originalParentId ? `<span style="padding-left: ${originalDepth * 20}px;"></span><i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>` : '';
                                $td.html(indentHtml + originalVal);
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
                        <button class="btn btn-warning btn-xs edit-akun" data-id="${akunId}" data-toggle="tooltip" title="Edit Akun">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form id="delete-akun-form-${akunId}" action="${'{{ route('keuangan.akun.destroy', ['akun' => ':akunId']) }}'.replace(':akunId', akunId)}" method="POST" class="delete-form" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="btn btn-danger btn-xs"
                                    data-toggle="modal"
                                    data-target="#deleteConfirmModal"
                                    data-form-id="delete-akun-form-${akunId}"
                                    title="Hapus Akun">
                                <i class="fas fa-trash"></i> Hapus
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
