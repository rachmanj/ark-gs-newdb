@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(Monthly)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Report for: {{ date('F Y', strtotime($month)) }}</p>
@endsection

@section('breadcrumb_title')
    dashboard / monthly
@endsection

@section('styles')
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/apexcharts/apexcharts.css') }}">
    <style>
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 0.25rem;
        }

        .progress-bar {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 20px;
            color: #fff;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
        }

        .table-striped tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .trend-indicator {
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .trend-up {
            color: #28a745;
        }

        .trend-down {
            color: #dc3545;
        }

        .trend-neutral {
            color: #6c757d;
        }

        .chart-container-apex {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .chart-subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        @media print {

            .card-tools,
            .btn,
            .small-box .icon {
                display: none !important;
            }

            .progress {
                border: 1px solid #dee2e6;
            }
        }
    </style>
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

            <!-- REGULER -->
            <div class="row">
                <div class="col-12 mb-4">
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
            </div>

            <!-- PO SENT vs GRPO -->
            <div class="row">
                <div class="col-12 mb-4">
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
            </div>

            <!-- NPI Index -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card card-warning card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb mr-1"></i>
                                NPI Index
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

            <!-- CAPEX -->
            <div class="row">
                <div class="col-12 mb-4">
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

            <!-- Interactive ApexCharts Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Interactive Analytics & Visualizations
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Budget Performance Chart -->
                            <div class="row">
                                <div class="col-lg-8 col-md-12 mb-3">
                                    <div class="chart-container-apex">
                                        <div class="chart-header">
                                            <div>
                                                <div class="chart-title">
                                                    <i class="fas fa-chart-bar text-primary"></i>
                                                    Budget vs PO Sent (Monthly)
                                                </div>
                                                <div class="chart-subtitle">Compare budget allocation with actual spending
                                                </div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="exportMonthlyChart('monthlyBudgetChart', 'png')">
                                                <i class="fas fa-download"></i> Export
                                            </button>
                                        </div>
                                        <div id="monthlyBudgetChart"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12 mb-3">
                                    <div class="chart-container-apex">
                                        <div class="chart-header">
                                            <div>
                                                <div class="chart-title">
                                                    <i class="fas fa-chart-pie text-success"></i>
                                                    Budget Distribution
                                                </div>
                                                <div class="chart-subtitle">Project allocation breakdown</div>
                                            </div>
                                        </div>
                                        <div id="monthlyBudgetPieChart"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- GRPO & NPI Charts -->
                            <div class="row">
                                <div class="col-lg-6 col-md-12 mb-3">
                                    <div class="chart-container-apex">
                                        <div class="chart-header">
                                            <div>
                                                <div class="chart-title">
                                                    <i class="fas fa-tasks text-info"></i>
                                                    GRPO Completion Rate
                                                </div>
                                                <div class="chart-subtitle">Goods receipt performance by project</div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="exportMonthlyChart('monthlyGrpoChart', 'png')">
                                                <i class="fas fa-download"></i> Export
                                            </button>
                                        </div>
                                        <div id="monthlyGrpoChart"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12 mb-3">
                                    <div class="chart-container-apex">
                                        <div class="chart-header">
                                            <div>
                                                <div class="chart-title">
                                                    <i class="fas fa-industry text-warning"></i>
                                                    NPI Production Index
                                                </div>
                                                <div class="chart-subtitle">Production efficiency analysis</div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-warning"
                                                onclick="exportMonthlyChart('monthlyNpiChart', 'png')">
                                                <i class="fas fa-download"></i> Export
                                            </button>
                                        </div>
                                        <div id="monthlyNpiChart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Comparison -->
            <div class="row mt-4">
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
    <script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script>

    <!-- ApexCharts -->
    <script src="{{ asset('adminlte/plugins/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize popovers
            $('[data-toggle="popover"]').popover();

            // Initialize ApexCharts
            setTimeout(function() {
                initializeMonthlyCharts();
            }, 500);
        });

        // Chart instances storage
        let monthlyChartInstances = {};

        // Prepare data from backend
        const monthlyData = {
            reguler: {!! json_encode($data['reguler']['reguler_monthly']) !!},
            grpo: {!! json_encode($data['grpo']['grpo']) !!},
            npi: {!! json_encode($data['npi']['npi']) !!}
        };

        // Initialize all monthly charts
        function initializeMonthlyCharts() {
            createMonthlyBudgetChart();
            createMonthlyBudgetPieChart();
            createMonthlyGrpoChart();
            createMonthlyNpiChart();
        }

        // 1. Monthly Budget Performance Chart
        function createMonthlyBudgetChart() {
            const projects = monthlyData.reguler.map(item => item.project);
            const budgets = monthlyData.reguler.map(item => item.budget / 1000);
            const poSent = monthlyData.reguler.map(item => item.sent_amount / 1000);

            const options = {
                series: [{
                    name: 'Budget',
                    data: budgets
                }, {
                    name: 'PO Sent',
                    data: poSent
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return 'IDR ' + val.toFixed(0) + 'K';
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '9px',
                        colors: ["#304758"]
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: projects
                },
                yaxis: {
                    title: {
                        text: 'Amount (IDR in Thousands)'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "IDR " + (val * 1000).toLocaleString('id-ID');
                        }
                    }
                },
                colors: ['#00E396', '#008FFB'],
                legend: {
                    position: 'top'
                }
            };

            monthlyChartInstances.monthlyBudgetChart = new ApexCharts(
                document.querySelector("#monthlyBudgetChart"),
                options
            );
            monthlyChartInstances.monthlyBudgetChart.render();
        }

        // 2. Monthly Budget Pie Chart
        function createMonthlyBudgetPieChart() {
            const projects = monthlyData.reguler.map(item => item.project);
            const budgets = monthlyData.reguler.map(item => item.budget);

            const options = {
                series: budgets,
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: projects,
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "IDR " + val.toLocaleString('id-ID');
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            monthlyChartInstances.monthlyBudgetPieChart = new ApexCharts(
                document.querySelector("#monthlyBudgetPieChart"),
                options
            );
            monthlyChartInstances.monthlyBudgetPieChart.render();
        }

        // 3. Monthly GRPO Chart
        function createMonthlyGrpoChart() {
            const projects = monthlyData.grpo.map(item => item.project);
            const percentages = monthlyData.grpo.map(item => (item.percentage * 100).toFixed(2));

            const options = {
                series: [{
                    name: 'Completion Rate',
                    data: percentages
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        distributed: true,
                        horizontal: false,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: percentages.map(p => {
                    return p >= 95 ? '#00E396' :
                        (p >= 80 ? '#00A1DB' :
                            (p >= 60 ? '#FEB019' : '#FF4560'));
                }),
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + '%';
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: projects
                },
                yaxis: {
                    title: {
                        text: 'Completion Rate (%)'
                    },
                    max: 110
                },
                legend: {
                    show: false
                }
            };

            monthlyChartInstances.monthlyGrpoChart = new ApexCharts(
                document.querySelector("#monthlyGrpoChart"),
                options
            );
            monthlyChartInstances.monthlyGrpoChart.render();
        }

        // 4. Monthly NPI Chart
        function createMonthlyNpiChart() {
            const projects = monthlyData.npi.map(item => item.project);
            const incomingQty = monthlyData.npi.map(item => item.incoming_qty);
            const outgoingQty = monthlyData.npi.map(item => item.outgoing_qty);

            const options = {
                series: [{
                    name: 'Incoming',
                    data: incomingQty
                }, {
                    name: 'Outgoing',
                    data: outgoingQty
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: projects
                },
                yaxis: {
                    title: {
                        text: 'Quantity (Units)'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toLocaleString('id-ID') + ' units';
                        }
                    }
                },
                colors: ['#00E396', '#FF4560'],
                legend: {
                    position: 'top'
                }
            };

            monthlyChartInstances.monthlyNpiChart = new ApexCharts(
                document.querySelector("#monthlyNpiChart"),
                options
            );
            monthlyChartInstances.monthlyNpiChart.render();
        }

        // Export monthly chart functionality
        function exportMonthlyChart(chartId, format) {
            if (monthlyChartInstances[chartId]) {
                monthlyChartInstances[chartId].dataURI().then(({
                    imgURI
                }) => {
                    const link = document.createElement('a');
                    link.href = imgURI;
                    link.download = chartId + '_{{ $month }}_' + new Date().getTime() + '.' + format;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            }
        }

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
