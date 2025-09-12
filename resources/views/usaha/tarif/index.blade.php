@extends('adminlte::page')

@section('title', 'Manajemen Tarif')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Tarif</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">Daftar Aturan Harga</span>
            <a href="{{ route('usaha.tarif.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Tarif Baru</a>
        </div>
        <div class="card-body">
            @if(session('success')) <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> @endif

            @foreach ($semua_tarif as $jenis => $tarifs)
                <h4 class="mt-4 mb-2">{{ $jenisTarifOptions[$jenis] ?? 'Lainnya' }}</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Deskripsi</th>
                                @if($jenis == 'pemakaian')
                                    <th class="text-center">Batas Bawah (m³)</th>
                                    <th class="text-center">Batas Atas (m³)</th>
                                @endif
                                <th class="text-right">Harga</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tarifs->sortBy('batas_bawah') as $tarif)
                            <tr>
                                <td>{{ $tarif->deskripsi }}</td>
                                @if($jenis == 'pemakaian')
                                    <td class="text-center">{{ $tarif->batas_bawah ?? '-' }}</td>
                                    <td class="text-center">{{ $tarif->batas_atas ?? '> ' . ($tarif->batas_bawah - 1) }}</td>
                                @endif
                                <td class="text-right">Rp {{ number_format($tarif->harga, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('usaha.tarif.edit', $tarif) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="document.getElementById('delete-form-{{$tarif->id}}').submit();" title="Hapus"><i class="fa fa-trash"></i></button>
                                    <form id="delete-form-{{$tarif->id}}" action="{{ route('usaha.tarif.destroy', $tarif) }}" method="POST" style="display: none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@stop
