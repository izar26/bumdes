@extends('adminlte::page')
@section('title', 'Tambah Pemasok')
@section('content_header')
    <h1>Tambah Pemasok Baru</h1>
@stop
@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Pemasok</h3>
    </div>
    <form action="{{ route('usaha.pemasok.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-group">
                <label for="nama_pemasok">Nama Pemasok</label>
                <input type="text" name="nama_pemasok" class="form-control" value="{{ old('nama_pemasok') }}" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat (Opsional)</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
            </div>
            <div class="form-group">
                <label for="no_telepon">No. Telepon (Opsional)</label>
                <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon') }}">
            </div>
            <div class="form-group">
                <label for="email">Email (Opsional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <label for="unit_usaha_id">Terkait Unit Usaha</label>

                {{-- Field ini hanya untuk tampilan, nilai dikirim lewat input hidden --}}
                <select name="unit_usaha_id_display" id="unit_usaha_id_display" class="form-control" disabled required>
                    <option value="">Pilih Unit Usaha</option>
                    @foreach ($unitUsahas as $unit)
                        <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                           {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>

                {{-- Input hidden yang akan mengirimkan nilai unit_usaha_id --}}
                <input type="hidden" name="unit_usaha_id" id="unit_usaha_id_hidden" value="{{ old('unit_usaha_id') }}">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('usaha.pemasok.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectDisplay = document.getElementById('unit_usaha_id_display');
        const hiddenInput = document.getElementById('unit_usaha_id_hidden');

        // Ambil ID dari unit usaha pertama yang ada di dropdown
        const firstUnitUsahaId = selectDisplay.options.length > 1 ? selectDisplay.options[1].value : '';

        // Jika hanya ada satu unit usaha yang dikelola user, pilih secara otomatis
        if (selectDisplay.options.length === 2 && firstUnitUsahaId) {
            selectDisplay.value = firstUnitUsahaId;
            hiddenInput.value = firstUnitUsahaId;
            selectDisplay.dispatchEvent(new Event('change'));
        }

        // Jika old('unit_usaha_id') ada, pilih dan set nilainya
        const oldUnitUsahaId = hiddenInput.value;
        if (oldUnitUsahaId) {
            selectDisplay.value = oldUnitUsahaId;
        }

        // Tambahkan event listener untuk memastikan input hidden selalu terupdate
        selectDisplay.addEventListener('change', function() {
            hiddenInput.value = this.value;
        });
    });
</script>
@stop
