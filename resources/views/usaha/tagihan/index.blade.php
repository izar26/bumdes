@extends('adminlte::page')

@section('title', 'Daftar Tagihan')

@section('content_header')
    <h1 class="m-0 text-dark">Daftar Tagihan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-info-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('usaha.tagihan.cetak-selektif') }}" method="POST" target="_blank">
                @csrf
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('usaha.tagihan.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Buat Tagihan Baru
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-print"></i> Cetak yang Dipilih
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>#</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Periode</th>
                                        <th class="text-right">Total Bayar</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($semua_tagihan as $tagihan)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="tagihan_ids[]" value="{{ $tagihan->id }}" class="tagihan-checkbox">
                                        </td>
                                        <td>{{ $loop->iteration + $semua_tagihan->firstItem() - 1 }}</td>
                                        <td>{{ $tagihan->pelanggan->nama ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($tagihan->periode_tagihan)->isoFormat('MMMM YYYY') }}</td>
                                        <td class="text-right">Rp {{ number_format($tagihan->total_harus_dibayar, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $tagihan->status_pembayaran == 'Lunas' ? 'badge-success' : 'badge-warning' }}">
                                                {{ $tagihan->status_pembayaran }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('usaha.tagihan.show', $tagihan) }}" class="btn btn-sm btn-info" title="Detail"><i class="fa fa-eye"></i></a>
                                            <a href="{{ route('usaha.tagihan.edit', $tagihan) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="if(confirm('Apakah Anda yakin ingin menghapus tagihan ini?')) { document.getElementById('delete-form-{{$tagihan->id}}').submit(); }" title="Hapus"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data tagihan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $semua_tagihan->links() }}
                    </div>
                </div>
            </form>
            @foreach ($semua_tagihan as $tagihan)
            <form id="delete-form-{{$tagihan->id}}" action="{{ route('usaha.tagihan.destroy', $tagihan) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endforeach

        </div>
    </div>
@stop

@push('js')
<script>
    document.getElementById('select-all').addEventListener('click', function(event) {
        let checkboxes = document.querySelectorAll('.tagihan-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
</script>
@endpush
