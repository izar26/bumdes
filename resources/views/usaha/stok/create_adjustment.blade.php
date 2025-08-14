@extends('adminlte::page')

@section('title', 'Buat Penyesuaian Stok')

@section('content_header')
    <h1>Buat Penyesuaian Stok</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Penyesuaian Stok Produk</h3>
        </div>
        <form id="stok-adjustment-form" action="{{ route('usaha.stok.store') }}" method="POST">
            @csrf
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

                <div class="form-group">
                    <label for="produk_id">Produk:</label>
                    {{-- DROPDOWN INI HANYA MENAMPILKAN PRODUK DARI UNIT USAHA YANG DIKELOLA USER --}}
                    <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produks as $produk)
                            <option value="{{ $produk->produk_id }}" {{ old('produk_id') == $produk->produk_id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }} - Stok Saat Ini: {{ $produk->stok->jumlah_stok ?? '0' }} {{ $produk->satuan_unit ?? ''}}
                            </option>
                        @endforeach
                    </select>
                    @error('produk_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="unit_usaha_id_display">Unit Usaha (Otomatis berdasarkan Produk):</label>
                    <select id="unit_usaha_id_display" class="form-control" disabled>
                        @foreach ($unitUsahas as $unitUsaha)
                            <option value="{{ $unitUsaha->unit_usaha_id }}" {{ old('unit_usaha_id') == $unitUsaha->unit_usaha_id ? 'selected' : '' }}>
                                {{ $unitUsaha->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- INPUT HIDDEN INI YANG AKAN MENGIRIMKAN NILAI UNIT USAHA KE CONTROLLER --}}
                <input type="hidden" name="unit_usaha_id" id="unit_usaha_id_hidden" value="{{ old('unit_usaha_id') }}">

                <div class="form-group">
                    <label for="jenis_penyesuaian">Jenis Penyesuaian:</label>
                    <select name="jenis_penyesuaian" id="jenis_penyesuaian" class="form-control @error('jenis_penyesuaian') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="tambah" {{ old('jenis_penyesuaian') == 'tambah' ? 'selected' : '' }}>Penambahan Stok</option>
                        <option value="kurang" {{ old('jenis_penyesuaian') == 'kurang' ? 'selected' : '' }}>Pengurangan Stok</option>
                    </select>
                    @error('jenis_penyesuaian')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jumlah_penyesuaian">Jumlah Penyesuaian:</label>
                    <input type="number" name="jumlah_penyesuaian" id="jumlah_penyesuaian" class="form-control @error('jumlah_penyesuaian') is-invalid @enderror" value="{{ old('jumlah_penyesuaian') }}" min="1" required>
                    @error('jumlah_penyesuaian')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="lokasi_penyimpanan">Lokasi Penyimpanan (Opsional):</label>
                    <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control @error('lokasi_penyimpanan') is-invalid @enderror" value="{{ old('lokasi_penyimpanan') }}" maxlength="255">
                    @error('lokasi_penyimpanan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="alasan_penyesuaian">Alasan Penyesuaian (Opsional):</label>
                    <textarea name="alasan_penyesuaian" id="alasan_penyesuaian" class="form-control @error('alasan_penyesuaian') is-invalid @enderror" rows="3">{{ old('alasan_penyesuaian') }}</textarea>
                    @error('alasan_penyesuaian')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Penyesuaian</button>
                <a href="{{ route('usaha.stok.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        const produksData = @json($produks->keyBy('produk_id'));

        document.getElementById('produk_id').addEventListener('change', function() {
            const selectedProdukId = this.value;
            const unitUsahaSelectDisplay = document.getElementById('unit_usaha_id_display');
            const unitUsahaInputHidden = document.getElementById('unit_usaha_id_hidden');

            if (selectedProdukId && produksData[selectedProdukId]) {
                const produk = produksData[selectedProdukId];
                const unitUsahaId = produk.unit_usaha_id;

                unitUsahaSelectDisplay.value = unitUsahaId;
                unitUsahaInputHidden.value = unitUsahaId;
            } else {
                unitUsahaSelectDisplay.value = '';
                unitUsahaInputHidden.value = '';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const produkId = document.getElementById('produk_id');
            if (produkId.value) {
                produkId.dispatchEvent(new Event('change'));
            }
        });
    </script>
@stop
