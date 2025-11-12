@extends('adminlte::page')

@section('title', 'Rekapitulasi Tagihan')

@section('plugins.DataTables', true)

@section('content_header')
    <h1 class="m-0 text-dark">Rekapitulasi Tagihan</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-body">
            {{-- FORM FILTER PERIODE --}}
            <div class="text-center mb-4">
                <h4>REKAPITULASI TAGIHAN SPAMDes</h4>
                <h5>PERIODE: {{ strtoupper($nama_bulan[$bulan_terpilih]) }} {{ $tahun_terpilih }}</h5>
            </div>
            <form action="{{ route('usaha.tagihan.rekap') }}" method="GET" class="form-inline mb-4">
                <div class="form-group">
                    <label for="periode_bulan" class="mr-2">Periode:</label>
                    <select name="periode_bulan" id="periode_bulan" class="form-control">
                        @foreach ($nama_bulan as $bulan => $nama)
                            <option value="{{ $bulan }}" {{ $bulan_terpilih == $bulan ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mx-2">
                    <select name="periode_tahun" id="periode_tahun" class="form-control">
                        @for ($tahun = date('Y'); $tahun >= date('Y') - 3; $tahun--)
                            <option value="{{ $tahun }}" {{ $tahun_terpilih == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mr-2">
            <label for="petugas_id" class="mr-2">Petugas:</label>
            <select name="petugas_id" id="petugas_id" class="form-control">
                <option value="">-- Semua Petugas --</option>
                @foreach ($semua_petugas as $petugas)
                    <option value="{{ $petugas->id }}" {{ $petugas_terpilih == $petugas->id ? 'selected' : '' }}>{{ $petugas->nama_petugas }}</option>
                @endforeach
            </select>
        </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>

                {{-- TOMBOL CETAK MANUAL BARU --}}
                <a href="{{ route('usaha.tagihan.rekap-cetak', request()->query()) }}" target="_blank" class="btn btn-secondary ml-2">
                    <i class="fa fa-print"></i> Cetak Laporan
                </a>
            </form>



            {{-- RINGKASAN TOTAL --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pemasukan (Lunas & Cicil)</span>
                            <span class="info-box-number">Rp. {{ number_format($total_pemasukan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Piutang (Belum Lunas)</span>
                            <span class="info-box-number">Rp. {{ number_format($total_belum_lunas, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>


            {{-- TABEL DETAIL (TANPA DATATABLES) --}}
            <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="rekap-table">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>NO</th>
                            <th>NOMOR ID</th>
                            <th>NAMA</th>
                            <th>LOKASI</th>
                            <th>Ket</th>
                            <th>METER LALU</th>
                            <th>METER KINI</th>
                            <th>JML (m³)</th>
                            <th>ADM</th>
                            <th>PML</th>
                            <th>TAGIHAN</th>
                            <th>TUNGGAKAN</th>
                            <th>DENDA</th>
                            <th>JML DENDA</th>
                            <th>TOTAL DIBAYAR</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semua_tagihan as $index => $tagihan)
                            @php
                                $adm = $tagihan->rincian->where('deskripsi', 'Biaya Administrasi')->sum('subtotal');
                                $pml = $tagihan->rincian->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal');
                                $jml_denda = $tagihan->tunggakan + $tagihan->denda;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">00{{  $tagihan->pelanggan_id ?? '' }}</td>
                                <td>{{ $tagihan->pelanggan->nama ?? 'N/A' }}</td>
                                <td>{{ $tagihan->pelanggan->alamat ?? '' }}</td>
                                <td class="text-center">{{ $tagihan->pelanggan->golongan ?? 'R' }}</td>
                                <td class="text-center">{{ number_format($tagihan->meter_awal) }}</td>
                                <td class="text-center">{{ number_format($tagihan->meter_akhir) }}</td>
                                <td class="text-center">{{ $tagihan->total_pemakaian_m3 }}</td>
                                <td class="text-right">{{ number_format($adm) }}</td>
                                <td class="text-right">{{ number_format($pml) }}</td>
                                <td class="text-right">{{ number_format($tagihan->subtotal_pemakaian) }}</td>
                                <td class="text-right">{{ number_format($tagihan->tunggakan) }}</td>
                                <td class="text-right">{{ number_format($tagihan->denda) }}</td>
                                <td class="text-right">{{ number_format($jml_denda) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($tagihan->total_harus_dibayar) }}</td>
                                <td class="text-center">{{ $tagihan->status_pembayaran }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center">Tidak ada data untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
@if($semua_tagihan->isNotEmpty())
<tfoot class="bg-light font-weight-bold">
    <tr>
        {{-- PERBAIKAN: Colspan diubah dari 7 menjadi 5 agar sejajar --}}
        <td colspan="5" class="text-center">TOTAL</td>

        {{-- Kolom total untuk METER LALU --}}
        <td class="text-right">{{ number_format($semua_tagihan->sum('meter_awal')) }}</td>

        {{-- Kolom total untuk METER KINI --}}
        <td class="text-right">{{ number_format($semua_tagihan->sum('meter_akhir')) }}</td>

        {{-- (Sisa kolom total lainnya tidak berubah, posisinya sekarang sudah benar) --}}
        <td class="text-right">{{ number_format($semua_tagihan->sum('total_pemakaian_m3')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Administrasi')->sum('subtotal')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->pluck('rincian')->flatten()->where('deskripsi', 'Biaya Pemeliharaan')->sum('subtotal')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('subtotal_pemakaian')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('denda')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('tunggakan') + $semua_tagihan->sum('denda')) }}</td>
        <td class="text-right">{{ number_format($semua_tagihan->sum('total_harus_dibayar')) }}</td>
        <td></td>
    </tr>
</tfoot>
@endif

                </table>
            </div>
        </div>
    </div>
@stop
@section('js')
<script>
    $(document).ready(function() {
        $('#rekap-table').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" },

            // PERBAIKAN: Hapus 'B' untuk menghilangkan tombol
            "dom": 'lfrtip',

            // Hapus atau komentari seluruh array "buttons"
            // "buttons": ["copy", "csv", "excel", "pdf", "print"]
        });
    });
</script>
@stop
