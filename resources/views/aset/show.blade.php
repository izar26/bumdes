@extends('adminlte::page')

@section('title', 'Detail Aset')

@section('content_header')
    <h1><i class="fas fa-fw fa-box"></i> Detail Aset</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi Lengkap Aset: {{ $aset->nama_aset }}</h3>
                </div>
                <div class="card-body">
                    {{-- Tampilan Informasi Dasar --}}
                    <h5>Informasi Aset</h5>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-3">ID Aset:</dt>
                        <dd class="col-sm-9">{{ $aset->aset_id }}</dd>

                        <dt class="col-sm-3">Nomor Inventaris:</dt>
                        <dd class="col-sm-9">{{ $aset->nomor_inventaris }}</dd>

                        <dt class="col-sm-3">Nama Aset:</dt>
                        <dd class="col-sm-9">{{ $aset->nama_aset }}</dd>

                        <dt class="col-sm-3">Jenis Aset:</dt>
                        <dd class="col-sm-9">{{ $aset->jenis_aset }}</dd>

                        <dt class="col-sm-3">Kondisi:</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-{{ $aset->kondisi == 'Baik' ? 'success' : ($aset->kondisi == 'Rusak Ringan' ? 'warning' : 'danger') }}">{{ $aset->kondisi }}</span>
                        </dd>

                        <dt class="col-sm-3">Lokasi:</dt>
                        <dd class="col-sm-9">{{ $aset->lokasi ?? '-' }}</dd>

                        <dt class="col-sm-3">Unit Usaha:</dt>
                        <dd class="col-sm-9">{{ $aset->unitUsaha->nama_unit ?? '-' }}</dd>
                    </dl>

                    {{-- Informasi Keuangan --}}
                    <h5 class="mt-4">Informasi Perolehan & Penyusutan</h5>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-3">Nilai Perolehan:</dt>
                        <dd class="col-sm-9">Rp {{ number_format($aset->nilai_perolehan, 2, ',', '.') }}</dd>

                        <dt class="col-sm-3">Tanggal Perolehan:</dt>
                        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d F Y') }}</dd>

                        <dt class="col-sm-3">Metode Penyusutan:</dt>
                        <dd class="col-sm-9">{{ $aset->metode_penyusutan ?? '-' }}</dd>

                        <dt class="col-sm-3">Masa Manfaat:</dt>
                        <dd class="col-sm-9">{{ $aset->masa_manfaat ? $aset->masa_manfaat . ' tahun' : '-' }}</dd>

                        <dt class="col-sm-3">Nilai Residu:</dt>
                        <dd class="col-sm-9">Rp {{ number_format($aset->nilai_residu, 2, ',', '.') }}</dd>

                        <dt class="col-sm-3">Nilai Saat Ini:</dt>
                        <dd class="col-sm-9">Rp {{ number_format($aset->nilai_saat_ini, 2, ',', '.') }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('bumdes.aset.edit', $aset->aset_id) }}" class="btn btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit Aset
                    </a>
                    <a href="{{ route('bumdes.aset.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dl-horizontal dt {
            text-align: left;
        }
    </style>
@stop
