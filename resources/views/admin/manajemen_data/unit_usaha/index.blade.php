@extends('adminlte::page')

@section('title', 'Unit Usaha')

@section('content_header')
    <h1>Manajemen Unit Usaha</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Unit Usaha</h3>
                    <div class="card-tools">
                        @can('admin_bumdes')
                            <a href="{{ route('admin.manajemen-data.unit_usaha.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Unit Usaha
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <table id="unitUsahaTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Unit</th>
                                <th>Jenis Usaha</th>
                                <th>Status Operasi</th>
                                <th>Tanggal Mulai</th>
                                <th>Penanggung Jawab</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unitUsahas as $unitUsaha)
                                <tr>
                                    <td>{{ $unitUsaha->nama_unit }}</td>
                                    <td>{{ $unitUsaha->jenis_usaha }}</td>
                                    <td>{{ $unitUsaha->status_operasi }}</td>
                                    <td>{{ $unitUsaha->tanggal_mulai_operasi ? \Carbon\Carbon::parse($unitUsaha->tanggal_mulai_operasi)->translatedFormat('d F Y') : '-' }}</td>

                                    <td>
                                        @forelse($unitUsaha->users as $user)
                                            <span class="badge badge-secondary">{{ $user->name }}</span>@if(!$loop->last), @endif
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.manajemen-data.unit_usaha.edit', $unitUsaha->unit_usaha_id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @can('admin_bumdes')
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-form-id="delete-form-{{ $unitUsaha->unit_usaha_id }}"
                                                    data-title="Konfirmasi Hapus Unit Usaha"
                                                    data-body="Apakah Anda yakin ingin menghapus unit usaha '{{ $unitUsaha->nama_unit }}'? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait."
                                                    data-button-text="Ya, Hapus"
                                                    data-button-class="btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        @endcan

                                        <form id="delete-form-{{ $unitUsaha->unit_usaha_id }}" action="{{ route('admin.manajemen-data.unit_usaha.destroy', $unitUsaha->unit_usaha_id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi (menggunakan komponen yang sudah kita buat) --}}
    {{-- Anda bisa meletakkan modal ini di file yang sama atau di partial terpisah --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteModalBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="confirmDeleteButton" class="btn"></button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            // Inisialisasi DataTable
            $("#unitUsahaTable").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                }
            });

            // Logika untuk mengisi modal konfirmasi
            $('#confirmDeleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var formId = button.data('form-id');
                var title = button.data('title');
                var body = button.data('body');
                var buttonText = button.data('button-text');
                var buttonClass = button.data('button-class');

                var modal = $(this);
                modal.find('.modal-title').text(title);
                modal.find('.modal-body').text(body);

                var confirmButton = modal.find('#confirmDeleteButton');
                confirmButton.text(buttonText);
                confirmButton.removeClass().addClass('btn').addClass(buttonClass);
                confirmButton.off('click').on('click', function() {
                    $('#' + formId).submit();
                });
            });
        });
    </script>
@stop

@section('plugins.Datatables', true)
