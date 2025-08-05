@extends('adminlte::page')

@section('title', 'Dashboard BUMDes')

@section('content_header')
    <h1><i class="fas fa-fw fa-chart-line"></i> Dashboard BUMDes</h1>
@stop

@section('content')
    {{-- Ringkasan Kinerja Keuangan --}}
    <div class="row financial-summary">
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">125.000.000.000.000.000</h3> {{-- Contoh angka yang sangat panjang --}}
                        </div>
                    </div>
                    <p>Total Pendapatan</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-danger">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">70.000.000</h3>
                        </div>
                    </div>
                    <p>Total Pengeluaran</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">55.000.000</h3>
                        </div>
                    </div>
                    <p>Laba Bersih</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Laporan <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">500.000.000</h3>
                        </div>
                    </div>
                    <p>Total Aset</p>
                    <small class="period-text">BUMDes</small>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Neraca <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Keuangan --}}
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card
                title="Grafik Tren Keuangan 6 Bulan Terakhir"
                theme="lightblue"
                icon="fas fa-chart-area"
                collapsible>
                <div style="height: 300px; padding: 10px;">
                    <canvas id="financeTrendChart"></canvas>
                </div>
            </x-adminlte-card>
        </div>
    </div>

    {{-- Status Unit-Unit Usaha --}}
    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card
                title="Status Unit Usaha"
                theme="teal"
                icon="fas fa-store"
                collapsible>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 35%">Unit Usaha</th>
                                <th style="width: 15%">Status</th>
                                <th style="width: 25%">Kinerja</th>
                                <th style="width: 25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Unit Simpan Pinjam</td>
                                <td><span class="badge badge-success">Aktif</span></td>
                                <td><span class="text-success"><i class="fas fa-arrow-up"></i> +15%</span></td>
                                <td><a href="#" class="btn btn-sm btn-info">Detail</a></td>
                            </tr>
                            <tr>
                                <td>Unit Pertanian</td>
                                <td><span class="badge badge-success">Aktif</span></td>
                                <td><span class="text-warning"><i class="fas fa-minus"></i> Stabil</span></td>
                                <td><a href="#" class="btn btn-sm btn-info">Detail</a></td>
                            </tr>
                            <tr>
                                <td>Unit Wisata Desa</td>
                                <td><span class="badge badge-danger">Pasif</span></td>
                                <td><span class="text-danger"><i class="fas fa-arrow-down"></i> -5%</span></td>
                                <td><a href="#" class="btn btn-sm btn-info">Detail</a></td>
                            </tr>
                            <tr>
                                <td>Unit Perdagangan</td>
                                <td><span class="badge badge-success">Aktif</span></td>
                                <td><span class="text-success"><i class="fas fa-arrow-up"></i> +10%</span></td>
                                <td><a href="#" class="btn btn-sm btn-info">Detail</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>

        {{-- Notifikasi dan Pengingat Penting --}}
        <div class="col-md-4">
            <x-adminlte-card
                title="Notifikasi & Pengingat"
                theme="warning"
                icon="fas fa-bell"
                collapsible>
                <div class="notification-list">
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>Pembayaran pinjaman jatuh tempo: Bpk. Andi (28 Juli)</span>
                        <small class="float-right">2 hari lagi</small>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <div class="alert alert-info alert-dismissible">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span>Rapat koordinasi unit usaha: 30 Juli 2025</span>
                        <small class="float-right">4 hari lagi</small>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Laporan keuangan bulan Juni telah disetujui</span>
                        <small class="float-right">Kemarin</small>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <div class="alert alert-primary alert-dismissible">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Perlu verifikasi data anggota baru</span>
                        <small class="float-right">1 jam lalu</small>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                </div>
            </div>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* General styling */
        .content-header {
            padding: 20px 15px;
            margin-bottom: 20px;
        }

        /* Improved Financial Summary Cards */
        .financial-summary .small-box {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .financial-summary .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .financial-summary .inner {
            padding: 20px;
            color: white;
        }

        /* Kontainer utama flexbox untuk Rp dan angka */
        .financial-summary .financial-text-container {
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Simbol mata uang yang tetap di tempatnya */
        .financial-summary .currency-symbol {
            font-size: 1rem;
            margin-right: 5px; /* Memberi sedikit jarak */
            align-self: flex-start;
            white-space: nowrap;
        }

        /* Kontainer "jendela" untuk teks yang akan digulir */
        .financial-summary .amount-scroll-container {
            flex-grow: 1; /* Kontainer mengambil sisa ruang */
            overflow: hidden;
            white-space: nowrap;
        }
        
        /* Elemen angka yang akan digeser */
        .financial-summary .amount {
            display: inline-block;
            white-space: nowrap;
            /* Tambahkan transisi agar pergerakan lebih halus */
            transition: transform 0.3s ease-in-out;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }
        
        /* Keyframes untuk animasi geser. Jarak geser sekarang dinamis */
        @keyframes slide-left {
            0% { transform: translateX(0); }
            30% { transform: translateX(0); } /* Berhenti sebentar di awal */
            70% { transform: translateX(var(--slide-distance)); }
            100% { transform: translateX(var(--slide-distance)); } /* Berhenti sebentar di akhir */
        }
        
        /* Kelas yang akan ditambahkan oleh JavaScript untuk mengaktifkan animasi */
        .financial-summary .amount.slide-active {
            animation: slide-left 8s linear infinite alternate;
        }

        .financial-summary .inner p {
            font-size: 1rem;
            margin: 10px 0 5px 0;
            font-weight: 600;
        }

        .financial-summary .period-text {
            font-size: 0.85rem;
            opacity: 0.9;
            display: block;
        }

        .financial-summary .small-box-footer {
            color: rgba(255,255,255,0.8);
            background: rgba(0,0,0,0.1);
            padding: 10px 0;
            text-decoration: none;
            font-weight: 500;
        }

        .financial-summary .small-box-footer:hover {
            background: rgba(0,0,0,0.2);
            color: white;
        }

        /* Rest of your CSS remains the same */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        /* Table improvements */
        .table {
            font-size: 0.95rem;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        /* Notification styling */
        .notification-list .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 10px;
            position: relative;
        }

        .notification-list .alert i {
            margin-right: 8px;
        }

        .notification-list .alert small {
            font-size: 0.8rem;
        }

        /* Chart container */
        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .financial-summary .amount {
                font-size: 1.3rem;
            }

            .financial-summary .currency-symbol {
                font-size: 0.9rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Financial Trend Chart
            const ctx = document.getElementById('financeTrendChart').getContext('2d');
            const financeTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [
                        {
                            label: 'Pendapatan',
                            data: [100, 110, 95, 120, 115, 125],
                            borderColor: 'rgba(40, 167, 69, 1)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Pengeluaran',
                            data: [60, 65, 55, 70, 68, 70],
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID') + ' jt';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID') + ' jt';
                                }
                            }
                        }
                    }
                }
            });

            // Fungsi untuk memeriksa overflow dan menerapkan animasi marquee
            function checkMarquee() {
                const containers = document.querySelectorAll('.financial-text-container');
                containers.forEach(container => {
                    const amountScrollContainer = container.querySelector('.amount-scroll-container');
                    const amountElement = container.querySelector('.amount');

                    if (!amountElement || !amountScrollContainer) return;

                    // Bandingkan lebar konten angka dengan lebar kontainer geser
                    const isOverflowing = amountElement.scrollWidth > amountScrollContainer.offsetWidth;
                    
                    if (isOverflowing) {
                        // Hitung jarak geser yang diperlukan
                        const slideDistance = amountScrollContainer.offsetWidth - amountElement.scrollWidth;
                        
                        // Set jarak geser sebagai variabel CSS
                        amountElement.style.setProperty('--slide-distance', `${slideDistance}px`);
                        
                        // Aktifkan animasi
                        amountElement.classList.add('slide-active');
                    } else {
                        // Nonaktifkan animasi jika tidak overflow
                        amountElement.classList.remove('slide-active');
                        // Pastikan transform kembali ke posisi awal
                        amountElement.style.transform = 'translateX(0)';
                    }
                });
            }

            // Panggil fungsi saat halaman dimuat
            checkMarquee();
            
            // Panggil fungsi setiap kali jendela diubah ukurannya (dengan sedikit jeda)
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(checkMarquee, 200);
            });
        });
    </script>
@stop
