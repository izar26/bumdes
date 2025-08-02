@extends('adminlte::page')

@section('title', 'Daftar Produk')

@section('content_header')
    <h1>Daftar Produk</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Manajemen Produk Usaha</h3>
            <div class="card-tools">
                <a href="{{ route('usaha.produk.create') }}" class="btn btn-primary btn-sm">Tambah Produk</a>
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

            @if ($produks->isEmpty())
                <div class="alert alert-warning" role="alert">
                    Belum ada produk yang terdaftar.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Satuan</th>
                                <th>Unit Usaha</th>
                                <th>Kategori</th>
                                <th>Stok Minimum</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produks as $produk)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $produk->nama_produk }}</td>
                                    <td>Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
<td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>

                                    <td>{{ $produk->satuan_unit }}</td>
                                    <td>{{ $produk->unitUsaha->nama_unit ?? 'N/A' }}</td>
                                    <td>{{ $produk->kategori->nama_kategori ?? 'Tidak Berkategori' }}</td>
                                    <td>{{ $produk->stok_minimum }}</td>
                                    <td>
                                        <a href="{{ route('usaha.produk.edit', $produk->produk_id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>

                                        <form id="delete-form-{{ $produk->produk_id }}"
                                              action="{{ route('usaha.produk.destroy', $produk->produk_id) }}"
                                              method="POST"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                            class="btn btn-sm btn-danger mb-1"
                                                            data-toggle="modal"
                                                            data-target="#confirmModal"
                                                            data-form-id="delete-form-{{ $produk->produk_id }}"
                                                            data-title="Konfirmasi Penghapusan Produk"
                                                            data-body="Apakah Anda yakin ingin menghapus produk '{{ $produk->nama_produk }}' ini secara permanen?"
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
            @endif
        </div>
    </div>

    @include('components.confirm-modal', [
        'modalId' => 'confirmModal',
        'title' => '',
        'body' => 'Apakah Anda yakin?',
        'confirmButtonText' => 'Lanjutkan',
        'confirmButtonClass' => 'btn-primary',
        'actionFormId' => ''
    ])
@endsection
