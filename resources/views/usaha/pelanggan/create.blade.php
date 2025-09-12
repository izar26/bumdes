@extends('adminlte::page')

@section('title', 'Tambah Pelanggan Baru')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Pelanggan Baru (Massal)</h1>
@stop

@section('content')
    <form action="{{ route('usaha.pelanggan.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="pelanggan-table">
                        <thead>
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>Alamat</th>
                                <th>Kontak</th>
                                <th style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris pertama sebagai template --}}
                            <tr>
                                <td>
                                    <input type="text" name="pelanggan[0][nama]" class="form-control" placeholder="Masukkan nama pelanggan" required>
                                </td>
                                <td>
                                    <input type="text" name="pelanggan[0][alamat]" class="form-control" placeholder="Masukkan alamat" required>
                                </td>
                                    <td>
                                    <input type="text" name="pelanggan[0][kontak]" class="form-control" placeholder="Masukkan kontak" required>
                                </td>
                                <td>
                                    {{-- Tombol hapus tidak ada di baris pertama --}}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" id="tambah-baris" class="btn btn-secondary mt-2"><i class="fa fa-plus"></i> Tambah Baris</button>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('usaha.pelanggan.index') }}" class="btn btn-default">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Semua Pelanggan</button>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
$(document).ready(function() {
    let rowIndex = 1;
    $('#tambah-baris').on('click', function() {
        // Template untuk baris baru
        let newRow = `
            <tr>
                <td>
                    <input type="text" name="pelanggan[${rowIndex}][nama]" class="form-control" placeholder="Masukkan nama pelanggan" required>
                </td>
                <td>
                    <input type="text" name="pelanggan[${rowIndex}][alamat]" class="form-control" placeholder="Masukkan alamat" required>
                </td>
                 <td>
                    <input type="text" name="pelanggan[${rowIndex}][kontak]" class="form-control" placeholder="Masukkan kontak" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm hapus-baris"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#pelanggan-table tbody').append(newRow);
        rowIndex++; // Increment index untuk baris berikutnya
    });

    // Gunakan event delegation untuk tombol hapus pada baris yang baru ditambahkan
    $('#pelanggan-table').on('click', '.hapus-baris', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endsection
