@extends('adminlte::page')

@section('title', 'Manajemen Kategori Usaha')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Kategori Usaha</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('components.flash-message')

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Daftar Kategori Usaha</h3>
                        <a href="{{ route('usaha.kategori.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Kategori
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($kategoris->isEmpty())
                        <div class="alert alert-info">
                            <i class="icon fas fa-info-circle"></i>
                            Belum ada kategori yang terdaftar.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="25%">Nama Kategori</th>
                                        <th>Deskripsi</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kategoris as $kategori)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $kategori->nama_kategori }}</strong>
                                            </td>
                                            <td>{{ $kategori->deskripsi ?: '-' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('usaha.kategori.edit', $kategori->id) }}"
                                                       class="btn btn-info"
                                                       title="Edit">
                                                       Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.confirm-modal', [
    'modalId' => 'confirmDeleteModalKategori', // ID unik untuk modal ini
    'title' => 'Konfirmasi Penghapusan Kategori',
    'body' => 'Apakah Anda yakin ingin menghapus data ini?',
    'confirmButtonText' => 'Hapus',
    'confirmButtonClass' => 'btn-danger',
    'actionFormId' => '' // Akan diisi oleh JavaScript
])
@stop

@section('css')
    <style>
        .table thead th {
            vertical-align: middle;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
@stop
