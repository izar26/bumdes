@extends('adminlte::page')

@section('title', 'Daftar Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-boxes"></i> Daftar Aset BUMDes</h1>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar Aset</h3>
            <div class="card-tools">
                <a href="{{ route('bumdes.aset.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Aset Baru
                </a>
            </div>
        </div>
        <div class="card-body p-0">
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

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomor Inventaris</th>
                            <th>Nama Aset</th>
                            <th>Nilai Perolehan</th>
                            <th>Tanggal Perolehan</th>
                            <th>Metode Penyusutan</th>
                            <th>Nilai Residu</th>
                            <th>Nilai Saat Ini</th>
                            <th>Kondisi</th>
                            <th>Unit Usaha</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aset as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nomor_inventaris ?? '-' }}</td>
                                <td>{{ $item->nama_aset }}</td>
                                <td>Rp {{ number_format($item->nilai_perolehan, 2, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_perolehan)->format('d M Y') }}</td>
                                <td>{{ $item->metode_penyusutan ?? '-' }}</td>
                                <td>Rp {{ number_format($item->nilai_residu, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->nilai_saat_ini, 2, ',', '.') }}</td>
                                <td><span class="badge badge-{{ $item->kondisi == 'Baik' ? 'success' : ($item->kondisi == 'Rusak Ringan' ? 'warning' : 'danger') }}">{{ $item->kondisi }}</span></td>
                                <td>{{ $item->unitUsaha->nama_unit ?? '-' }}</td>
                                <td class="text-nowrap">
                                    <div class="btn-group">
                                        <a href="{{ route('bumdes.aset.show', $item->aset_id) }}" class="btn btn-xs btn-primary" title="Lihat Detail Aset">
                                            <i class="fas fa-eye"></i> Show
                                        </a>
                                        <a href="{{ route('bumdes.aset.edit', $item->aset_id) }}" class="btn btn-xs btn-info" title="Edit Aset">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form id="delete-aset-{{ $item->aset_id }}" action="{{ route('bumdes.aset.destroy', $item->aset_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-danger" title="Hapus Aset"
                                                data-toggle="modal" data-target="#confirmModal"
                                                data-form-id="delete-aset-{{ $item->aset_id }}"
                                                data-title="Konfirmasi Hapus"
                                                data-body="Apakah Anda yakin ingin menghapus aset ini?"
                                                data-button-text="Hapus"
                                                data-button-class="btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">Tidak ada data aset yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $aset->links('pagination::bootstrap-4') }}
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@stop
