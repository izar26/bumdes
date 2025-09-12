@extends('adminlte::page')

@section('title', 'Input Tagihan Massal')

@section('content_header')
    <h1 class="m-0 text-dark">Input Tagihan Massal</h1>
@stop

@section('content')
    {{-- Form untuk Filter Periode --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('usaha.tagihan.halamanInputMassal') }}" method="GET" class="form-inline">
                <div class="form-group">
                    <label for="periode_bulan" class="mr-2">Periode:</label>
                    <select name="periode_bulan" id="periode_bulan" class="form-control" required>
                        @php
                            $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                        @endphp
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan_terpilih == $i ? 'selected' : '' }}>
                                {{ $nama_bulan[$i] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mx-2">
                    <select name="periode_tahun" id="periode_tahun" class="form-control" required>
                        @for ($tahun = date('Y'); $tahun >= date('Y') - 3; $tahun--)
                            <option value="{{ $tahun }}" {{ $tahun_terpilih == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Tampilkan Data
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    {{-- Tabel Input Massal --}}
    <div class="card">
        <div class="card-header">
            Menampilkan data untuk periode: **{{ $nama_bulan[$bulan_terpilih] }} {{ $tahun_terpilih }}**
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 25%;">Nama Pelanggan</th>
                            <th style="width: 15%;">Meter Awal (m³)</th>
                            <th style="width: 15%;">Meter Akhir (m³)</th>
                            <th style="width: 15%;">Pemakaian (m³)</th>
                            <th style="width: 25%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_tabel as $index => $baris)
                            {{-- Setiap baris adalah form sendiri --}}
                            <tr class="{{ $baris->sudah_disimpan ? 'table-success' : '' }}">
                                <form action="{{ route('usaha.tagihan.simpanBarisMassal') }}" method="POST">
                                    @csrf
                                    {{-- Data tersembunyi yang perlu dikirim --}}
                                    <input type="hidden" name="pelanggan_id" value="{{ $baris->pelanggan->id }}">
                                    <input type="hidden" name="periode_tagihan" value="{{ \Carbon\Carbon::create($tahun_terpilih, $bulan_terpilih, 1)->toDateString() }}">
                                    <input type="hidden" name="meter_awal" value="{{ $baris->meter_awal }}">

                                    <td class="text-center align-middle">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        {{ $baris->pelanggan->nama }} <br>
                                        <small class="text-muted">{{ $baris->pelanggan->alamat }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <input type="number" class="form-control-plaintext text-center" value="{{ $baris->meter_awal }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="{{ $baris->meter_awal }}"
                                               name="meter_akhir"
                                               class="form-control text-center meter-akhir-input"
                                               value="{{ $baris->meter_akhir }}"
                                               {{ $baris->sudah_disimpan ? 'readonly' : 'required' }}
                                               data-meter-awal="{{ $baris->meter_awal }}">
                                    </td>
                                    <td class="text-center align-middle pemakaian-column">
                                        {{-- Akan diisi oleh JavaScript --}}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($baris->sudah_disimpan)
                                            <span class="text-success"><i class="fa fa-check-circle"></i> Tersimpan</span>
                                            {{-- Tambahkan link untuk edit jika diperlukan --}}
                                        @else
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa fa-save"></i> Hitung & Simpan
                                            </button>
                                        @endif
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    // Script untuk menghitung pemakaian secara real-time saat input meter akhir
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.meter-akhir-input');

        function calculateUsage(inputElement) {
            const meterAkhir = parseFloat(inputElement.value);
            const meterAwal = parseFloat(inputElement.dataset.meterAwal);
            const pemakaianCell = inputElement.closest('tr').querySelector('.pemakaian-column');

            if (!isNaN(meterAkhir) && meterAkhir >= meterAwal) {
                const pemakaian = meterAkhir - meterAwal;
                pemakaianCell.textContent = pemakaian.toFixed(0) + ' m³';
            } else {
                pemakaianCell.textContent = '-';
            }
        }

        inputs.forEach(function(input) {
            // Hitung saat halaman dimuat untuk data yang sudah ada
            if (input.value) {
                calculateUsage(input);
            }
            // Tambahkan event listener untuk input
            input.addEventListener('input', function() {
                calculateUsage(this);
            });
        });
    });
</script>
@endpush
