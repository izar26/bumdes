@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    {{-- Judul dinamis berdasarkan peran --}}
    @hasanyrole('manajer_unit_usaha|admin_unit_usaha')
        <h1><i class="fas fa-fw fa-store"></i> Dashboard Unit Usaha</h1>
    @else
        <h1><i class="fas fa-fw fa-tachometer-alt"></i> Dashboard BUMDes</h1>
    @endhasanyrole
@stop

@section('content')
    {{-- Ringkasan Kinerja Keuangan --}}
    <div class="row financial-summary">
        {{-- Total Pendapatan Bulan Ini --}}
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">{{ number_format($totalPendapatanBulanIni ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <p>Total Pendapatan</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <a href="{{ route('laporan.laba-rugi.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        {{-- Total Beban Bulan Ini --}}
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-danger">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">{{ number_format($totalBebanBulanIni ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <p>Total Beban</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                <a href="{{ route('laporan.laba-rugi.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        {{-- Laba Bersih Bulan Ini --}}
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">{{ number_format($labaBulanIni ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <p>Laba Bersih</p>
                    <small class="period-text">Bulan Ini</small>
                </div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                <a href="{{ route('laporan.laba-rugi.index') }}" class="small-box-footer">Lihat Laporan <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        {{-- Total Kas & Bank --}}
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <div class="d-flex align-items-center financial-text-container">
                        <span class="currency-symbol">Rp</span>
                        <div class="amount-scroll-container">
                            <h3 class="amount">{{ number_format($totalKasBank ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <p>Total Kas & Bank</p>
                    <small class="period-text">Saat Ini</small>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <a href="{{ route('laporan.neraca.index') }}" class="small-box-footer">Lihat Neraca <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Keuangan --}}
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Grafik Tren Keuangan 6 Bulan Terakhir" theme="lightblue" icon="fas fa-chart-area" collapsible>
                <div style="height: 300px; padding: 10px;">
                    <canvas id="financeTrendChart"></canvas>
                </div>
            </x-adminlte-card>
        </div>
    </div>

    <div class="row">
        {{-- Kartu Kinerja Unit Usaha hanya tampil untuk peran admin/direksi --}}
        @unlessrole('manajer_unit_usaha|admin_unit_usaha')
        <div class="col-md-8">
            <x-adminlte-card title="Kinerja Unit Usaha (Bulan Ini)" theme="teal" icon="fas fa-store" collapsible>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Unit Usaha</th>
                                <th>Status</th>
                                <th>Pendapatan</th>
                                <th>Beban</th>
                                <th>Laba/Rugi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kinerjaUnitUsaha as $unit)
                                <tr>
                                    <td>{{ $unit['nama'] }}</td>
                                    <td><span class="badge badge-{{ $unit['status'] == 'Aktif' ? 'success' : 'danger' }}">{{ $unit['status'] }}</span></td>
                                    <td class="text-success">Rp {{ number_format($unit['pendapatan'], 0, ',', '.') }}</td>
                                    <td class="text-danger">Rp {{ number_format($unit['beban'], 0, ',', '.') }}</td>
                                    <td class="font-weight-bold {{ $unit['laba'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                        Rp {{ number_format($unit['laba'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data unit usaha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
        @endunlessrole

        {{-- Notifikasi akan mengambil lebar penuh jika kartu kinerja tidak tampil --}}
        <div class="@hasanyrole('manajer_unit_usaha|admin_unit_usaha') col-md-12 @else col-md-4 @endhasanyrole">
            <x-adminlte-card title="Notifikasi & Tugas" theme="warning" icon="fas fa-bell" collapsible>
                <div class="notification-list">
                    @forelse ($notifications as $notif)
                        <div class="alert alert-{{ $notif['type'] }} alert-dismissible">
                            <i class="{{ $notif['icon'] }} mr-2"></i>
                            <span>{!! $notif['message'] !!}</span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @empty
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Tidak ada notifikasi atau tugas baru.</span>
                        </div>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('css')
    {{-- CSS tidak berubah, tetap sama seperti sebelumnya --}}
    <style>
        .financial-summary .small-box { border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); transition: all 0.3s ease; border: none; overflow: hidden; }
        .financial-summary .small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .financial-summary .inner { padding: 20px; color: white; }
        .financial-summary .financial-text-container { display: flex; align-items: center; width: 100%; }
        .financial-summary .currency-symbol { font-size: 1rem; margin-right: 5px; align-self: flex-start; white-space: nowrap; }
        .financial-summary .amount-scroll-container { flex-grow: 1; overflow: hidden; white-space: nowrap; }
        .financial-summary .amount { display: inline-block; white-space: nowrap; transition: transform 0.3s ease-in-out; font-size: 1.6rem; font-weight: 700; margin: 0; line-height: 1; }
        @keyframes slide-left { 0%, 30% { transform: translateX(0); } 70%, 100% { transform: translateX(var(--slide-distance)); } }
        .financial-summary .amount.slide-active { animation: slide-left 8s linear infinite alternate; }
        .financial-summary .inner p { font-size: 1rem; margin: 10px 0 5px 0; font-weight: 600; }
        .financial-summary .period-text { font-size: 0.85rem; opacity: 0.9; display: block; }
        .financial-summary .small-box-footer { color: rgba(255,255,255,0.8); background: rgba(0,0,0,0.1); padding: 10px 0; text-decoration: none; font-weight: 500; }
        .financial-summary .small-box-footer:hover { background: rgba(0,0,0,0.2); color: white; }
        .card { border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px; overflow: hidden; }
        .notification-list .alert { border-radius: 8px; padding: 12px 15px; margin-bottom: 10px; position: relative; }
        canvas { width: 100% !important; height: 100% !important; }
        @media (max-width: 768px) { .financial-summary .amount { font-size: 1.3rem; } .financial-summary .currency-symbol { font-size: 0.9rem; } }
    </style>
@stop

@section('js')
    {{-- JS tidak berubah, tetap sama seperti sebelumnya --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const chartData = @json($grafikData ?? ['labels' => [], 'pendapatan' => [], 'beban' => []]);
            const ctx = document.getElementById('financeTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Pendapatan', data: chartData.pendapatan, borderColor: 'rgba(40, 167, 69, 1)', backgroundColor: 'rgba(40, 167, 69, 0.1)', fill: true, tension: 0.3, borderWidth: 2 },
                        { label: 'Beban', data: chartData.beban, borderColor: 'rgba(220, 53, 69, 1)', backgroundColor: 'rgba(220, 53, 69, 0.1)', fill: true, tension: 0.3, borderWidth: 2 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { padding: 20, usePointStyle: true } }, tooltip: { callbacks: { label: (c) => `${c.dataset.label}: Rp ${c.parsed.y.toLocaleString('id-ID')}` } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => `Rp ${v.toLocaleString('id-ID')}` } } }
                }
            });
            function checkMarquee() {
                document.querySelectorAll('.amount-scroll-container').forEach(c => {
                    const a = c.querySelector('.amount');
                    if(a && a.scrollWidth > c.offsetWidth) {
                        a.style.setProperty('--slide-distance', `${c.offsetWidth - a.scrollWidth}px`);
                        a.classList.add('slide-active');
                    } else if(a) {
                        a.classList.remove('slide-active');
                    }
                });
            }
            checkMarquee();
            $(window).on('resize', checkMarquee);
        });
    </script>
@stop
