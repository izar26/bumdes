<div class="card-body">
    <div class="form-group">
        <label for="jenis_tarif">Jenis Tarif</label>
        <select name="jenis_tarif" id="jenis_tarif" class="form-control @error('jenis_tarif') is-invalid @enderror" required>
            <option value="">-- Pilih Jenis Tarif --</option>
            @foreach ($jenisTarifOptions as $value => $label)
                <option value="{{ $value }}" {{ old('jenis_tarif', $tarif->jenis_tarif ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('jenis_tarif') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <input type="text" name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" value="{{ old('deskripsi', $tarif->deskripsi ?? '') }}" required>
        @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    {{-- Form Group ini akan tampil/sembunyi secara dinamis --}}
    <div id="batas-pemakaian-group" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="batas_bawah">Batas Bawah (m³)</label>
                    <input type="number" name="batas_bawah" id="batas_bawah" class="form-control @error('batas_bawah') is-invalid @enderror" value="{{ old('batas_bawah', $tarif->batas_bawah ?? '') }}">
                    @error('batas_bawah') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="batas_atas">Batas Atas (m³)</label>
                    <input type="number" name="batas_atas" id="batas_atas" class="form-control @error('batas_atas') is-invalid @enderror" value="{{ old('batas_atas', $tarif->batas_atas ?? '') }}">
                    <small class="form-text text-muted">Kosongkan jika tidak ada batas atas (misal: >25 m³).</small>
                    @error('batas_atas') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="harga">Harga</label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
            <input type="number" step="0.01" name="harga" id="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ old('harga', $tarif->harga ?? '') }}" required>
        </div>
        <small class="form-text text-muted">Untuk "Tarif Pemakaian", ini adalah harga per m³. Untuk lainnya, ini adalah biaya flat.</small>
        @error('harga') <span class="text-danger">{{ $message }}</span> @enderror
    </div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('usaha.tarif.index') }}" class="btn btn-default">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitButtonText ?? 'Simpan' }}</button>
</div>

@section('js')
<script>
$(document).ready(function() {
    function toggleBatasPemakaian() {
        if ($('#jenis_tarif').val() === 'pemakaian') {
            $('#batas-pemakaian-group').slideDown();
        } else {
            $('#batas-pemakaian-group').slideUp();
            // Kosongkan nilainya saat disembunyikan agar tidak terkirim
            $('#batas_bawah').val('');
            $('#batas_atas').val('');
        }
    }

    // Jalankan saat halaman dimuat
    toggleBatasPemakaian();

    // Jalankan saat dropdown berubah
    $('#jenis_tarif').on('change', function() {
        toggleBatasPemakaian();
    });
});
</script>
@endsection
