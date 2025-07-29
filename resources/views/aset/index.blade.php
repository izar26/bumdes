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

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Aset</th>
                        <th>Jenis Aset</th>
                        <th>Nilai Perolehan</th>
                        <th>Tanggal Perolehan</th>
                        <th>Kondisi</th>
                        <th>Lokasi</th>
                        <th>BUMDes</th>
                        <th>Unit Usaha</th>
                        <th>Penanggung Jawab</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aset as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_aset }}</td>
                            <td>{{ $item->jenis_aset }}</td>
                            <td>Rp {{ number_format($item->nilai_perolehan, 2, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_perolehan)->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $item->kondisi == 'Baik' ? 'success' : ($item->kondisi == 'Rusak Ringan' ? 'warning' : 'danger') }}">{{ $item->kondisi }}</span></td>
                            <td>{{ $item->lokasi ?? '-' }}</td>
                            <td>{{ $item->bungdes->nama_bungdes ?? '-' }}</td>
                            <td>{{ $item->unitUsaha->nama_unit ?? '-' }}</td>
                            <td>{{ $item->penanggungJawabUser->name ?? '-' }}</td>
                            <td class="text-nowrap">
                                {{-- <a href="{{ route('bumdes.aset.edit', $item->aset_id) }}" class="btn btn-xs btn-info" title="Edit Aset">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('bumdes.aset.destroy', $item->aset_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus Aset">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada data aset yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .btn-xs {
            padding: .25rem .5rem;
            font-size: .75rem;
            line-height: 1.5;
            border-radius: .2rem;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Daftar Aset BUMDes loaded!');
    </script>
@stop
