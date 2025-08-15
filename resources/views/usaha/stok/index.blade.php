@extends('adminlte::page')

@section('title', 'Daftar Stok Produk')

@section('content_header')
    <h1>Daftar Stok Produk</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manajemen Inventaris Produk</h3>
            @if(auth()->user()->hasRole('admin_unit_usaha'))
            <div class="card-tools">
                <a href="{{ route('usaha.stok.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Buat Penyesuaian Stok
                </a>
            </div>
            @endif
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
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
                        <th>ID Produk</th>
                        <th>Nama Produk</th>
                        <th>Unit Usaha</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok Saat Ini</th>
                        <th>Stok Minimum</th>
                        <th>Status Stok</th>
                        <th>Lokasi Penyimpanan</th>
                        <th>Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produks as $produk)
                        @php
                            $currentStok = $produk->stok ? $produk->stok->jumlah_stok : 0;
                            $stokStatusClass = '';
                            if ($currentStok <= $produk->stok_minimum) {
                                $stokStatusClass = 'table-danger'; // Highlight row for low stock
                            }
                        @endphp
                        <tr class="{{ $stokStatusClass }}">
                            <td>{{ $produk->produk_id }}</td>
                            <td>{{ $produk->nama_produk }}</td>
                            <td>{{ $produk->unitUsaha->nama_unit ?? 'N/A' }}</td>
                            <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
                            <td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                <strong>{{ $currentStok }}</strong> {{ $produk->satuan_unit ?? '' }}
                            </td>
                            <td>{{ $produk->stok_minimum }} {{ $produk->satuan_unit ?? '' }}</td>
                            <td>
                                @if ($currentStok <= $produk->stok_minimum && $produk->stok_minimum > 0)
                                    <span class="badge badge-danger">Stok Rendah!</span>
                                @elseif ($currentStok == 0)
                                    <span class="badge badge-warning">Stok Habis!</span>
                                @else
                                    <span class="badge badge-success">Aman</span>
                                @endif
                            </td>
                            <td>{{ $produk->stok->lokasi_penyimpanan ?? '-' }}</td>
                            <td>
                                @if($produk->stok && $produk->stok->tanggal_perbarui)
                                    {{ $produk->stok->tanggal_perbarui->format('d M Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada data produk atau stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/vendor/datatables-bs4/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="/vendor/datatables/jquery.dataTables.min.js"></script>
    {{-- <script src="/vendor/datatables-bs4/js/dataTables.bootstrap4.min.js"></script> --}}
    <script>
        $(function () {
            $('.table').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop
