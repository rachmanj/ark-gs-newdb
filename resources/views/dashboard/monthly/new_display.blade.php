@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(Monthly)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Report for: {{ date('F Y', strtotime($month)) }}</p>
@endsection

@section('breadcrumb_title')
    dashboard / monthly
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Month Selection Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Monthly Report Selection
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('dashboard.monthly.display') }}" method="POST">
                                @csrf
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label class="text-muted">Select month and year</label>
                                            <div class="input-group">
                                                <input type="month" name="month" class="form-control"
                                                    value="{{ $month }}">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-sync-alt mr-1"></i> Update
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-right mt-3 mt-md-0">
                                        <a href="{{ route('dashboard.monthly.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                                        </a>
                                        <a href="#" class="btn btn-outline-info ml-2" onclick="window.print()">
                                            <i class="fas fa-print mr-1"></i> Print Report
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Overview Chart -->
            <div class="row">
                <!-- Key Metrics Cards -->
                <div class="col-12 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($data['reguler']['sent_total'] / 1000, 2) }}</h3>
                                    <p>Total PO Sent (IDR 000)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($data['reguler']['budget_total'] / 1000, 2) }}</h3>
                                    <p>Total Budget (IDR 000)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($data['grpo']['total_grpo_amount'] / 1000, 2) }}</h3>
                                    <p>Total GRPO (IDR 000)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card card-success card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                PO Sent Distribution by Project
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:250px;">
                                <canvas id="monthlyOverviewChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular & CAPEX Data -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-info card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice-dollar mr-1"></i>
                                REGULER <small>(IDR 000)</small>
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @include('dashboard.monthly.reguler')
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-danger card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-1"></i>
                                CAPEX <small>(IDR 000)</small>
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @include('dashboard.monthly.capex')
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRPO & NPI Data -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-info card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-exchange-alt mr-1"></i>
                                PO SENT vs GRPO <small>(IDR 000)</small>
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @include('dashboard.monthly.grpo')
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-warning card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb mr-1"></i>
                                NPI
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @include('dashboard.monthly.npi')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Comparison -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Year-to-Year Comparison
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:250px;">
                                <canvas id="historicalComparisonChart"></canvas>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Monthly Overview Chart - Distribution of PO sent by project
        var ctx = document.getElementById('monthlyOverviewChart').getContext('2d');
        var monthlyOverviewChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($data['reguler']['reguler_monthly'] as $item)
                        '{{ $item['project'] }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($data['reguler']['reguler_monthly'] as $item)
                            {{ $item['sent_amount'] / 1000 }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(60, 141, 188, 0.8)', // Blue
                        'rgba(0, 166, 90, 0.8)', // Green
                        'rgba(243, 156, 18, 0.8)', // Yellow
                        'rgba(221, 75, 57, 0.8)', // Red
                        'rgba(149, 165, 166, 0.8)', // Gray
                        'rgba(162, 94, 245, 0.8)' // Purple
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'PO Sent Distribution by Project ({{ date('F Y', strtotime($month)) }})',
                        font: {
                            size: 14
                        }
                    },
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('id-ID').format(context.parsed) + ' K';
                                }
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((context.parsed / total) * 100);
                                return label + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Historical Comparison Chart - Using real data
        var historicalCtx = document.getElementById('historicalComparisonChart').getContext('2d');
        var historicalComparisonChart = new Chart(historicalCtx, {
            type: 'bar',
            data: {
                labels: @json($yearlyData['labels']),
                datasets: @json($yearlyData['datasets'])
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
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Performance Comparison: {{ $yearlyData['previousYear'] }} vs {{ $yearlyData['currentYear'] }}',
                        font: {
                            size: 14
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
                                    label += new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' K';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
