@extends('adminlte::page')

@section('title', 'Penyusutan Aset BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-chart-line"></i> Penyusutan Aset BUMDes</h1>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title">Laporan Penyusutan Aset</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Laporan ini menyajikan nilai buku aset saat ini yang dihitung berdasarkan metode penyusutan yang telah ditentukan.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="penyusutanTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomor Inventaris</th>
                            <th>Nama Aset</th>
                            <th>Nilai Perolehan</th>
                            <th>Tgl. Perolehan</th>
                            <th>Metode</th>
                            <th>Masa Manfaat</th>
                            <th>Nilai Residu</th>
                            <th>Umur Aset</th>
                            <th>Penyusutan per Tahun</th>
                            <th>Akumulasi Penyusutan</th>
                            <th>Nilai Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $aset)
                            @php
                                $tahunSekarang = now()->year;
                                $tahunPerolehan = \Carbon\Carbon::parse($aset->tanggal_perolehan)->year;
                                $umurAset = $tahunSekarang - $tahunPerolehan;

                                $penyusutanPerTahun = 0;
                                if ($aset->masa_manfaat > 0) {
                                    $penyusutanPerTahun = ($aset->nilai_perolehan - $aset->nilai_residu) / $aset->masa_manfaat;
                                }

                                $akumulasiPenyusutan = $penyusutanPerTahun * $umurAset;
                                $nilaiSaatIni = $aset->nilai_perolehan - $akumulasiPenyusutan;
                                // Pastikan nilai buku tidak di bawah nilai residu
                                $nilaiSaatIni = max($nilaiSaatIni, $aset->nilai_residu);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $aset->nomor_inventaris ?? '-' }}</td>
                                <td>{{ $aset->nama_aset }}</td>
                                <td>Rp {{ number_format($aset->nilai_perolehan, 2, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($aset->tanggal_perolehan)->format('d M Y') }}</td>
                                <td>{{ $aset->metode_penyusutan ?? '-' }}</td>
                                <td>{{ $aset->masa_manfaat ?? '-' }} tahun</td>
                                <td>Rp {{ number_format($aset->nilai_residu, 2, ',', '.') }}</td>
                                <td>{{ $umurAset }} tahun</td>
                                <td>Rp {{ number_format($penyusutanPerTahun, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($akumulasiPenyusutan, 2, ',', '.') }}</td>
                                <td class="font-weight-bold">
                                    Rp {{ number_format($nilaiSaatIni, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">Tidak ada aset yang terdaftar untuk penyusutan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $('#penyusutanTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop
