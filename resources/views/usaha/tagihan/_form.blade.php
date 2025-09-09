<div class="row">
    {{-- Kolom Kiri: Informasi Dasar --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="pelanggan_id">Pelanggan</label>
            <select name="pelanggan_id" id="pelanggan_id" class="form-control @error('pelanggan_id') is-invalid @enderror">
                <option value="">-- Pilih Pelanggan --</option>
                @foreach ($semua_pelanggan as $pelanggan)
                <option value="{{ $pelanggan->id }}" {{ old('pelanggan_id', $tagihan->pelanggan_id ?? '') == $pelanggan->id ? 'selected' : '' }}>
                    {{ $pelanggan->nama }} - {{ $pelanggan->alamat }}
                </option>
                @endforeach
            </select>
            @error('pelanggan_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="periode_tagihan">Periode Tagihan</label>
                <input type="month" name="periode_tagihan" id="periode_tagihan" class="form-control @error('periode_tagihan') is-invalid @enderror" value="{{ old('periode_tagihan', isset($tagihan) ? \Carbon\Carbon::parse($tagihan->periode_tagihan)->format('Y-m') : '') }}">
                @error('periode_tagihan') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 form-group">
                 <label for="petugas_id">Petugas</label>
                <select name="petugas_id" id="petugas_id" class="form-control @error('petugas_id') is-invalid @enderror">
                    <option value="">-- Opsional --</option>
                    @foreach ($semua_petugas as $petugas)
                    <option value="{{ $petugas->id }}" {{ old('petugas_id', $tagihan->petugas_id ?? '') == $petugas->id ? 'selected' : '' }}>
                        {{ $petugas->nama_petugas }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="meter_awal">Meter Awal (m³)</label>
                <input type="number" step="0.01" name="meter_awal" id="meter_awal" class="form-control @error('meter_awal') is-invalid @enderror" value="{{ old('meter_awal', $tagihan->meter_awal ?? '') }}">
                @error('meter_awal') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 form-group">
                <label for="meter_akhir">Meter Akhir (m³)</label>
                <input type="number" step="0.01" name="meter_akhir" id="meter_akhir" class="form-control @error('meter_akhir') is-invalid @enderror" value="{{ old('meter_akhir', $tagihan->meter_akhir ?? '') }}">
                @error('meter_akhir') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Rincian Biaya --}}
    <div class="col-md-6">
        <h5>Rincian Biaya</h5>
        <div id="rincian-wrapper">
             @if(old('rincian', isset($tagihan) ? $tagihan->rincian : []))
                @foreach(old('rincian', isset($tagihan) ? $tagihan->rincian : []) as $index => $item)
                <div class="row align-items-center rincian-item mb-2">
                    <div class="col-6">
                        <input type="text" name="rincian[{{$index}}][deskripsi]" class="form-control" placeholder="Deskripsi" value="{{ $item['deskripsi'] ?? $item->deskripsi }}">
                    </div>
                    <div class="col-4">
                        <input type="number" name="rincian[{{$index}}][subtotal]" class="form-control subtotal" placeholder="Subtotal" value="{{ $item['subtotal'] ?? $item->subtotal }}">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-danger btn-sm remove-rincian"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                @endforeach
            @else
                {{-- Default rincian jika form create --}}
                @if(!isset($tagihan))
                <div class="row align-items-center rincian-item mb-2">
                    <div class="col-6"><input type="text" name="rincian[0][deskripsi]" class="form-control" placeholder="Deskripsi" value="Pemakaian Air"></div>
                    <div class="col-4"><input type="number" name="rincian[0][subtotal]" class="form-control subtotal" placeholder="Subtotal"></div>
                    <div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-rincian"><i class="fa fa-trash"></i></button></div>
                </div>
                <div class="row align-items-center rincian-item mb-2">
                    <div class="col-6"><input type="text" name="rincian[1][deskripsi]" class="form-control" placeholder="Deskripsi" value="Biaya Administrasi"></div>
                    <div class="col-4"><input type="number" name="rincian[1][subtotal]" class="form-control subtotal" placeholder="Subtotal"></div>
                    <div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-rincian"><i class="fa fa-trash"></i></button></div>
                </div>
                @endif
            @endif
        </div>

        <button type="button" id="add-rincian" class="btn btn-success btn-sm mt-2">
            <i class="fa fa-plus"></i> Tambah Rincian
        </button>

        <hr>
        <div class="text-right">
            <h4>Total: <span id="total-display">Rp 0</span></h4>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // (JavaScript dari jawaban sebelumnya diletakkan di sini, tidak ada perubahan)
        let rincianWrapper = document.getElementById('rincian-wrapper');
        let addRincianBtn = document.getElementById('add-rincian');
        let totalDisplay = document.getElementById('total-display');
        let index = rincianWrapper.children.length;

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });
            totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        addRincianBtn.addEventListener('click', function() {
            let newItem = document.createElement('div');
            newItem.classList.add('row', 'align-items-center', 'rincian-item', 'mb-2');
            newItem.innerHTML = `
                <div class="col-6">
                    <input type="text" name="rincian[${index}][deskripsi]" class="form-control" placeholder="Deskripsi" required>
                </div>
                <div class="col-4">
                    <input type="number" name="rincian[${index}][subtotal]" class="form-control subtotal" placeholder="Subtotal" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm remove-rincian"><i class="fa fa-trash"></i></button>
                </div>
            `;
            rincianWrapper.appendChild(newItem);
            index++;
            updateTotal();
        });

        rincianWrapper.addEventListener('click', function(e) {
            if (e.target.closest('.remove-rincian')) {
                e.target.closest('.rincian-item').remove();
                updateTotal();
            }
        });

        rincianWrapper.addEventListener('input', function(e) {
            if (e.target.classList.contains('subtotal')) {
                updateTotal();
            }
        });
        updateTotal(); // Panggil saat halaman dimuat
    });
</script>
@endpush
