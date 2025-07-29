@extends('adminlte::page')

@section('title', 'Dashboard BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-chart-line"></i> Dashboard BUMDes</h1>
@stop

@section('content')
    {{-- Ringkasan Kinerja Keuangan --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="Rp 125.000.000" text="Total Pendapatan (Bulan Ini)" icon="fas fa-dollar-sign text-white"
                theme="success" url="#" url-text="Lihat Detail Pendapatan" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="Rp 70.000.000" text="Total Pengeluaran (Bulan Ini)" icon="fas fa-hand-holding-usd text-white"
                theme="danger" url="#" url-text="Lihat Detail Pengeluaran" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="Rp 55.000.000" text="Laba Bersih (Bulan Ini)" icon="fas fa-chart-bar text-white"
                theme="info" url="#" url-text="Lihat Laporan Laba Rugi" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="Rp 500.000.000" text="Aset BUMDes" icon="fas fa-money-bill-wave text-white"
                theme="primary" url="#" url-text="Lihat Neraca Keuangan" />
        </div>
    </div>

    {{-- Grafik Tren Keuangan --}}
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Grafik Tren Keuangan (6 Bulan Terakhir)" theme="lightblue" icon="fas fa-chart-area" collapsible>
                <canvas id="financeTrendChart" style="height:300px"></canvas>
            </x-adminlte-card>
        </div>
    </div>

    {{-- Status Unit-Unit Usaha --}}
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Status Unit-Unit Usaha" theme="teal" icon="fas fa-store" collapsible>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Unit Usaha</th>
                            <th>Status</th>
                            <th>Kinerja (Bulan Ini)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Unit Simpan Pinjam</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td><span class="text-success"><i class="fas fa-arrow-up"></i> +15%</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">Detail</a></td>
                        </tr>
                        <tr>
                            <td>Unit Pertanian</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td><span class="text-warning"><i class="fas fa-minus"></i> Stabil</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">Detail</a></td>
                        </tr>
                        <tr>
                            <td>Unit Wisata Desa</td>
                            <td><span class="badge badge-danger">Pasif</span></td>
                            <td><span class="text-danger"><i class="fas fa-arrow-down"></i> -5%</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">Detail</a></td>
                        </tr>
                        <tr>
                            <td>Unit Perdagangan</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td><span class="text-success"><i class="fas fa-arrow-up"></i> +10%</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">Detail</a></td>
                        </tr>
                    </tbody>
                </table>
            </x-adminlte-card>
        </div>

        {{-- Notifikasi dan Pengingat Penting --}}
        <div class="col-md-4">
            <x-adminlte-card title="Notifikasi & Pengingat" theme="warning" icon="fas fa-bell" collapsible>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-exclamation-circle text-danger"></i> Pembayaran pinjaman jatuh tempo: Bpk. Andi (28 Juli)
                        <span class="float-right text-muted text-sm">2 hari lagi</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-alt text-info"></i> Rapat koordinasi unit usaha: 30 Juli 2025
                        <span class="float-right text-muted text-sm">4 hari lagi</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> Laporan keuangan bulan Juni telah disetujui.
                        <span class="float-right text-muted text-sm">Kemarin</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-info-circle text-primary"></i> Perlu verifikasi data anggota baru.
                        <span class="float-right text-muted text-sm">1 jam lalu</span>
                    </li>
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* General styling for cards and small boxes */
        .card, .small-box {
            border-radius: 10px; /* Slightly more rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            transition: all 0.3s ease-in-out; /* Smooth transition for hover effects */
        }

        .card:hover, .small-box:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
            transform: translateY(-3px); /* Slight lift on hover */
        }

        /* Custom styling for info boxes within small-box */
        .small-box .inner h3 {
            font-weight: 700; /* Make titles bolder */
            font-size: 2.2rem; /* Slightly larger font for main numbers */
        }

        .small-box .icon {
            font-size: 70px; /* Larger icons */
            top: 15px; /* Adjust icon position */
        }

        /* Table styling */
        .table-bordered th, .table-bordered td {
            border-color: #dee2e6 !important; /* Ensure border color is consistent */
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03); /* Lighter stripe effect */
        }

        /* List group styling for notifications */
        .list-group-item {
            border-left: 4px solid transparent; /* Add a subtle left border */
            transition: all 0.2s ease-in-out;
        }

        .list-group-item:hover {
            background-color: #f8f9fa; /* Light background on hover */
            border-left-color: #007bff; /* Highlight on hover */
        }

        .list-group-item i {
            margin-right: 10px; /* Space out icons in list items */
        }

        /* Chart specific styling */
        canvas {
            background-color: #ffffff; /* Ensure chart background is white */
            border-radius: 8px;
            padding: 10px;
        }

        /* Adjust content header for better spacing */
        .content-header {
            padding: 20px 15px;
        }
    </style>
@stop

@section('js')
    {{-- Memuat Chart.js untuk grafik --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Data dummy untuk grafik tren keuangan (Pendapatan vs Pengeluaran)
            const ctx = document.getElementById('financeTrendChart').getContext('2d');
            const financeTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'], // Bulan
                    datasets: [
                        {
                            label: 'Pendapatan',
                            data: [100, 110, 95, 120, 115, 125], // dalam jutaan
                            borderColor: 'rgba(40, 167, 69, 1)', // Hijau
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Pengeluaran',
                            data: [60, 65, 55, 70, 68, 70], // dalam jutaan
                            borderColor: 'rgba(220, 53, 69, 1)', // Merah
                            backgroundColor: 'rgba(220, 53, 69, 0.2)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah (Juta Rp)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
        console.log('Dashboard BUMDes loaded!');
    </script>
@stop
