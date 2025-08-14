@extends('adminlte::page')

@section('title', 'Manajemen Pemasok')

@section('content_header')
    <h1>Manajemen Pemasok</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pemasok</h3>
        <div class="card-tools">
            <a href="{{ route('usaha.pemasok.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Pemasok
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                {{ session('success') }}
            </div>
        @endif
        {{-- Tambahkan penanganan error juga --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                {{ session('error') }}
            </div>
        @endif
        <table id="table-pemasok" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pemasok</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pemasoks as $key => $pemasok)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $pemasok->nama_pemasok }}</td>
                        <td>{{ $pemasok->no_telepon ?? '-' }}</td>
                        <td>{{ $pemasok->email ?? '-' }}</td>
                        <td>
                            <a href="{{ route('usaha.pemasok.edit', $pemasok->pemasok_id) }}" class="btn btn-info btn-xs">Edit</a>
                            <form id="delete-form-{{ $pemasok->pemasok_id }}" {{-- Perbaikan: penjualan_id diganti jadi pemasok_id --}}
                                  action="{{ route('usaha.pemasok.destroy', $pemasok->pemasok_id) }}"
                                  method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn btn-xs btn-danger"
                                        data-toggle="modal"
                                        data-target="#confirmDeleteModal" {{-- Perbaikan: ID target modal sesuai dengan include --}}
                                        data-form-id="delete-form-{{ $pemasok->pemasok_id }}"
                                        data-title="Konfirmasi Penghapusan Pemasok" {{-- Judul modal lebih spesifik --}}
                                        data-body="Apakah Anda yakin ingin menghapus pemasok '{{ $pemasok->nama_pemasok }}' ini secara permanen? Aksi ini tidak bisa dikembalikan."
                                        data-button-text="Hapus Permanen"
                                        data-button-class="btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL INI DIPERBAIKI: ID modal --}}
@include('components.confirm-modal', [
    'modalId' => 'confirmDeleteModal',
    'title' => 'Konfirmasi Penghapusan Pemasok',
    'body' => 'Apakah Anda yakin ingin menghapus data ini?',
    'confirmButtonText' => 'Hapus',
    'confirmButtonClass' => 'btn-danger',
    'actionFormId' => ''
])
@stop

@section('plugins.Datatables', true)
