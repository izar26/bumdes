@extends('adminlte::page')

@section('title', 'Filter Neraca Saldo')

@section('content_header')
    <h1>Laporan Neraca Saldo</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filter Laporan
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('laporan.neraca-saldo.generate') }}" method="POST">
                @csrf
                <div class="row align-items-end">

                    {{-- Tipe Laporan (Bulan/Tanggal) --}}
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <div class="form-group mb-0">
                            <label for="filter_type" class="form-label">Tipe Laporan</label>
                            <select name="filter_type" id="filter_type" class="form-control" required>
                                <option value="monthly" {{ old('filter_type', 'monthly') == 'monthly' ? 'selected' : '' }}>
                                    Per Bulan
                                </option>
                                <option value="daily" {{ old('filter_type') == 'daily' ? 'selected' : '' }}>
                                    Per Tanggal
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Wadah untuk Input Dinamis (Bulan atau Tanggal) --}}
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        {{-- Filter Bulanan --}}
                        <div id="monthly_filter">
                            <div class="form-group mb-0">
                                <label for="month" class="form-label">Pilih Bulan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="month" name="month" id="month" class="form-control"
                                           value="{{ old('month', date('Y-m')) }}">
                                </div>
                            </div>
                        </div>
                        {{-- Filter Harian/Tanggal --}}
                        <div id="daily_filter" style="display: none;">
                            <div class="form-group mb-0">
                                <label for="report_date" class="form-label">Pilih Tanggal</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    </div>
                                    <input type="date" name="report_date" id="report_date" class="form-control"
                                           value="{{ old('report_date', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Unit Usaha --}}
                    @if(isset($unitUsahas) && $unitUsahas->isNotEmpty())
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                        <div class="form-group mb-0">
                            <label for="unit_usaha_id" class="form-label">Unit Usaha (Opsional)</label>
                            <select name="unit_usaha_id" id="unit_usaha_id" class="form-control">
                                <option value="">Semua Unit Usaha</option>
                                @foreach($unitUsahas as $unit)
                                    <option value="{{ $unit->unit_usaha_id }}" {{ old('unit_usaha_id') == $unit->unit_usaha_id ? 'selected' : '' }}>
                                        {{ $unit->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    {{-- Tombol Submit --}}
                    <div class="col-lg-2 col-md-6 col-sm-12 mb-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-eye"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterType = document.getElementById('filter_type');
        const monthlyFilter = document.getElementById('monthly_filter');
        const dailyFilter = document.getElementById('daily_filter');
        const monthInput = document.getElementById('month');
        const dateInput = document.getElementById('report_date');

        function toggleFilterInputs() {
            if (filterType.value === 'monthly') {
                monthlyFilter.style.display = 'block';
                dailyFilter.style.display = 'none';
                monthInput.required = true;
                dateInput.required = false;
            } else { // 'daily'
                monthlyFilter.style.display = 'none';
                dailyFilter.style.display = 'block';
                monthInput.required = false;
                dateInput.required = true;
            }
        }
        toggleFilterInputs();
        filterType.addEventListener('change', toggleFilterInputs);
    });
</script>
@endpush
