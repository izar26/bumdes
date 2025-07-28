@extends('adminlte::page')

@section('title', 'Daftar Unit Usaha')

@section('content_header')
    <h1>Daftar Unit Usaha</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manajemen Unit Usaha</h3>
            <div class="card-tools">
                <a href="{{ route('admin.unit_usaha.create') }}" class="btn btn-primary btn-sm">Tambah Unit Usaha</a>
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

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Unit</th>
                        <th>Jenis Usaha</th>
                        <th>BUMDes</th>
                        <th>Penanggung Jawab</th>
                        <th>Tgl Mulai Operasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitUsahas as $unitUsaha)
                        <tr>
                            <td>{{ $unitUsaha->unit_usaha_id }}</td>
                            <td>{{ $unitUsaha->nama_unit }}</td>
                            <td>{{ $unitUsaha->jenis_usaha }}</td>
                            <td>{{ $unitUsaha->bungdes->nama_bumdes ?? 'N/A' }}</td>
                            <td>{{ $unitUsaha->user->username ?? 'N/A' }}</td> {{-- Menampilkan username penanggung jawab --}}
                            <td>{{ optional($unitUsaha->tanggal_mulai_operasi)->format('d-m-Y') }}</td>
                            <td>{{ $unitUsaha->status_operasi }}</td>
                            <td>
                                <a href="{{ route('admin.unit_usaha.show', $unitUsaha->unit_usaha_id) }}" class="btn btn-info btn-xs">Detail</a>
                                <a href="{{ route('admin.unit_usaha.edit', $unitUsaha->unit_usaha_id) }}" class="btn btn-warning btn-xs">Edit</a>
                                <form action="{{ route('admin.unit_usaha.destroy', $unitUsaha->unit_usaha_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Apakah Anda yakin ingin menghapus unit usaha ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada unit usaha yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
