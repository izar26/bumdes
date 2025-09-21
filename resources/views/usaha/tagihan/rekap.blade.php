@extends('adminlte::page')

@section('title', 'Rekapitulasi Tagihan')

@section('content_header')
    <h1 class="m-0 text-dark">Rekapitulasi Tagihan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('usaha.tagihan.rekap') }}" method="GET" class="form-inline mb-4">
                <div class="form-group">
                    <label for="periode_bulan" class="mr-2">Periode:</label>
                    <select name="periode_bulan" id="periode_bulan" class="form-control">
                        @php $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan_terpilih == $i ? 'selected' : '' }}>{{ $nama_bulan[$i] }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mx-2">
                    <select name="periode_tahun" id="periode_tahun" class="form-control">
                        @for ($tahun = date('Y'); $tahun >= date('Y') - 3; $tahun--)
                            <option value="{{ $tahun }}" {{ $tahun_terpilih == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
            </form>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pemasukan</span>
                            <span class="info-box-number">Rp. {{ number_format($total_pemasukan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Belum Lunas</span>
                            <span class="info-box-number">Rp. {{ number_format($total_belum_lunas, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-3 text-muted">Data di atas adalah rekapitulasi untuk tagihan yang dibuat pada periode **{{ $nama_bulan[$bulan_terpilih] }} {{ $tahun_terpilih }}**.</p>
        </div>
    </div>
@stop

@section('js')
    {{-- Di sini Anda bisa menambahkan JavaScript jika diperlukan, misalnya untuk grafik --}}
@stop
