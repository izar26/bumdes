@extends('adminlte::page')

@section('title', 'Daftar Pelanggan')

@section('content_header')
    <h1 class="m-0 text-dark">Daftar Pelanggan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">
                Data Semua Pelanggan
            </span>
            <a href="{{ route('usaha.pelanggan.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Pelanggan Baru
            </a>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif
            @if(session('error')) <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semua_pelanggan as $pelanggan)
                        <tr>
                            <td>{{ $loop->iteration + $semua_pelanggan->firstItem() - 1 }}</td>
                            <td>{{ $pelanggan->nama }}</td>
                            <td>{{ $pelanggan->alamat }}</td>
                            <td>{{$pelanggan->kontak}}</td>
                            <td class="text-center">
                                <span class="badge {{ $pelanggan->status_pelanggan == 'Aktif' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $pelanggan->status_pelanggan }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('usaha.pelanggan.edit', $pelanggan) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="if(confirm('Apakah Anda yakin? Pelanggan yang memiliki tagihan tidak dapat dihapus.')) { document.getElementById('delete-form-{{$pelanggan->id}}').submit(); }" title="Hapus"><i class="fa fa-trash"></i></button>
                                <form id="delete-form-{{$pelanggan->id}}" action="{{ route('usaha.pelanggan.destroy', $pelanggan) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data pelanggan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($semua_pelanggan->hasPages())
        <div class="card-footer clearfix">
            {{ $semua_pelanggan->links() }}
        </div>
        @endif
    </div>
@stop
