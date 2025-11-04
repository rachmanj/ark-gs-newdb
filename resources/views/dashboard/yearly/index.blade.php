@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(Yearly)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> View comprehensive yearly performance reports</p>
@endsection

@section('breadcrumb_title')
    dashboard / yearly
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Year Selection Card -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Select Year to View Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('dashboard.yearly.display') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="text-muted">Select year</label>
                                    <div class="input-group">
                                        <select class="form-control" name="year">
                                            <option value="">-- select year --</option>
                                            <option value="this_year">This Year</option>
                                            @foreach ($years as $year)
                                                <option value="{{ $year->date }}">{{ date('Y', strtotime($year->date)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search mr-1"></i> View Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-info card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i>
                                Yearly Report Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info rounded-circle p-3 mr-3">
                                    <i class="fas fa-chart-bar text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Annual Performance Analysis</h5>
                                    <p class="text-muted mb-0 small">View comprehensive yearly breakdown of budget, PO sent,
                                        GRPO, and production metrics</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle p-3 mr-3">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Multi-Year Comparison</h5>
                                    <p class="text-muted mb-0 small">Compare performance across multiple years to identify
                                        long-term trends</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-link mr-1"></i>
                                Quick Links
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('dashboard.daily.index') }}"
                                        class="btn btn-block btn-outline-primary">
                                        <i class="fas fa-chart-line mr-1"></i> Daily Dashboard
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('dashboard.monthly.index') }}"
                                        class="btn btn-block btn-outline-success">
                                        <i class="fas fa-calendar mr-1"></i> Monthly Dashboard
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('dashboard.summary-by-unit') }}"
                                        class="btn btn-block btn-outline-info">
                                        <i class="fas fa-building mr-1"></i> Summary by Unit
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('dashboard.search.po') }}" class="btn btn-block btn-outline-warning">
                                        <i class="fas fa-search mr-1"></i> Search PO
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Year Preview Data Cards -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-eye mr-1"></i>
                                Current Year Preview ({{ date('Y') }})
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success">Live Data</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Cards - Full Width -->
            <!-- REGULER Card -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.yearly.preview.reguler')
                </div>
            </div>

            <!-- GRPO Card -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.yearly.preview.grpo')
                </div>
            </div>

            <!-- NPI Card -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.yearly.preview.npi')
                </div>
            </div>

            <!-- CAPEX Card -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.yearly.preview.capex')
                </div>
            </div>

            <!-- Multi-Year Performance Preview -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-success card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-area mr-1"></i>
                                Multi-Year Performance Trends (Last 5 Years)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="multiYearTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js -->
    <script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>

    <script>
        // Multi-Year Trends Chart with real data
        var ctx = document.getElementById('multiYearTrendsChart').getContext('2d');
        var multiYearTrendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($multiYearData['labels']),
                datasets: @json($multiYearData['datasets'])
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (IDR 000)'
                        },
                        ticks: {
                            callback: function(value, index, ticks) {
                                return 'IDR ' + value.toLocaleString('id-ID') + 'K';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Year'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: '5-Year Performance Comparison: Budget vs PO Sent vs GRPO',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'IDR ' + new Intl.NumberFormat('id-ID').format(context.parsed
                                        .y) + ' K';
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    </script>
@endsection
