@extends('templates.main')

@section('title_page')
    <h1>Dashboard <small>(Yearly)</small></h1>
@endsection

@section('breadcrumb_title')
    dashboard / yearly
@endsection

@section('styles')
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
    <style>
        .chart-container {
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
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .chart-subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .chart-actions {
            display: flex;
            gap: 8px;
        }

        .chart-export-btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chart-export-btn:hover {
            background: #f8f9fa;
            border-color: #007bff;
            color: #007bff;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Year Selection
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.yearly.display') }}" method="POST" id="yearForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="yearSelect">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Select Year
                                    </label>
                                    <div class="input-group">
                                        <select class="form-control select2" name="year" id="yearSelect">
                                            <option value="">-- Select Year --</option>
                                            <option value="this_year" {{ old('year') == 'this_year' ? 'selected' : '' }}>
                                                This Year</option>
                                            @foreach ($years as $year)
                                                <option value="{{ $year->date }}"
                                                    {{ old('year') == $year->date ? 'selected' : '' }}>
                                                    {{ date('Y', strtotime($year->date)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-search mr-1"></i>
                                                Go!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Note:</strong> Select a year to view detailed yearly dashboard analytics
                                    including budget performance, PO tracking, and production metrics.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (isset($data))
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            {{ $year_title === 'This Year' ? 'This Year' : 'Year : ' . date('Y', strtotime($year_title)) }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" onclick="exportDashboard()">
                                <i class="fas fa-download mr-1"></i>
                                Export
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Loading dashboard data...</p>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboardContent">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4">
                    @include('dashboard.yearly.reguler')
                </div>

                <div class="col-lg-6 col-md-12 mb-4">
                    @include('dashboard.yearly.capex')
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4">
                    @include('dashboard.yearly.grpo')
                </div>

                <div class="col-lg-6 col-md-12 mb-4">
                    @include('dashboard.yearly.npi')
                </div>
            </div>
        </div>

        <!-- Quick Stats Summary -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Quick Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Budget</span>
                                        <span class="info-box-number" id="totalBudget">IDR
                                            {{ number_format($data['reguler']['budget_total'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-shopping-cart"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total PO Sent</span>
                                        <span class="info-box-number" id="totalPOSent">IDR
                                            {{ number_format($data['reguler']['sent_total'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-percentage"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Budget Performance</span>
                                        <span class="info-box-number"
                                            id="budgetPerformance">{{ number_format(($data['reguler']['percentage'] ?? 0) * 100, 1) }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-industry"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Production Index</span>
                                        <span class="info-box-number"
                                            id="productionIndex">{{ number_format($data['npi']['total_percentage'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
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
                            <div class="col-lg-8 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-chart-bar text-primary"></i>
                                                Budget vs PO Sent Performance
                                            </div>
                                            <div class="chart-subtitle">Compare budget allocation with actual PO sent
                                                amounts</div>
                                        </div>
                                        <div class="chart-actions">
                                            <button class="chart-export-btn" onclick="exportChart('budgetChart', 'png')">
                                                <i class="fas fa-download"></i> PNG
                                            </button>
                                            <button class="chart-export-btn" onclick="exportChart('budgetChart', 'svg')">
                                                <i class="fas fa-file-code"></i> SVG
                                            </button>
                                        </div>
                                    </div>
                                    <div id="budgetChart"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-chart-pie text-success"></i>
                                                Budget Distribution
                                            </div>
                                            <div class="chart-subtitle">Project budget allocation breakdown</div>
                                        </div>
                                    </div>
                                    <div id="budgetPieChart"></div>
                                </div>
                            </div>
                        </div>

                        <!-- GRPO Completion Chart -->
                        <div class="row mt-3">
                            <div class="col-lg-8 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-tasks text-info"></i>
                                                GRPO Completion Rate by Project
                                            </div>
                                            <div class="chart-subtitle">Percentage of PO received (GRPO vs PO Sent)</div>
                                        </div>
                                        <div class="chart-actions">
                                            <button class="chart-export-btn" onclick="exportChart('grpoChart', 'png')">
                                                <i class="fas fa-download"></i> PNG
                                            </button>
                                        </div>
                                    </div>
                                    <div id="grpoChart"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-tachometer-alt text-warning"></i>
                                                Overall GRPO Rate
                                            </div>
                                            <div class="chart-subtitle">Total completion percentage</div>
                                        </div>
                                    </div>
                                    <div id="grpoGaugeChart"></div>
                                </div>
                            </div>
                        </div>

                        <!-- NPI Production Index Chart -->
                        <div class="row mt-3">
                            <div class="col-lg-6 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-industry text-danger"></i>
                                                NPI Production Index
                                            </div>
                                            <div class="chart-subtitle">In vs Out production efficiency</div>
                                        </div>
                                        <div class="chart-actions">
                                            <button class="chart-export-btn" onclick="exportChart('npiChart', 'png')">
                                                <i class="fas fa-download"></i> PNG
                                            </button>
                                        </div>
                                    </div>
                                    <div id="npiChart"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-exchange-alt text-purple"></i>
                                                Production Flow Analysis
                                            </div>
                                            <div class="chart-subtitle">Incoming vs Outgoing quantities</div>
                                        </div>
                                    </div>
                                    <div id="npiScatterChart"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Overview Chart -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="chart-container">
                                    <div class="chart-header">
                                        <div>
                                            <div class="chart-title">
                                                <i class="fas fa-chart-area text-success"></i>
                                                Overall Performance Dashboard
                                            </div>
                                            <div class="chart-subtitle">Comprehensive view of all metrics</div>
                                        </div>
                                        <div class="chart-actions">
                                            <button class="chart-export-btn" onclick="exportChart('radarChart', 'png')">
                                                <i class="fas fa-download"></i> PNG
                                            </button>
                                        </div>
                                    </div>
                                    <div id="radarChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <!-- ApexCharts JavaScript Library -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

    <!-- JavaScript for enhanced functionality -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for better dropdown UX (if available)
            if ($.fn.select2) {
                $('#yearSelect').select2({
                    placeholder: 'Select Year',
                    allowClear: true,
                    width: '100%'
                });
            }

            // Add loading indicator on form submission
            $('#yearForm').on('submit', function() {
                $('#loadingIndicator').show();
                $('#dashboardContent').hide();
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
            });

            @if (isset($data))
                // Initialize all charts when data is available
                // Wait a bit to ensure ApexCharts is fully loaded
                setTimeout(function() {
                    if (typeof initializeCharts === 'function') {
                        initializeCharts();
                    }
                }, 500);
            @endif
        });

        @if (isset($data))
            // Chart instances storage
            let chartInstances = {};

            // Prepare data from backend
            const dashboardData = {
                reguler: {!! json_encode($data['reguler']['reguler_yearly']) !!},
                reguler_total: {{ $data['reguler']['budget_total'] }},
                reguler_sent_total: {{ $data['reguler']['sent_total'] }},
                capex: {!! json_encode($data['capex']['capex']) !!},
                grpo: {!! json_encode($data['grpo']['grpo_yearly']) !!},
                grpo_percentage: {{ $data['grpo']['total_percentage'] }},
                npi: {!! json_encode($data['npi']['npi']) !!}
            };

            // Initialize all charts
            function initializeCharts() {
                createBudgetChart();
                createBudgetPieChart();
                createGRPOChart();
                createGRPOGaugeChart();
                createNPIChart();
                createNPIScatterChart();
                createRadarChart();
            }

            // 1. Budget Performance Bar Chart
            function createBudgetChart() {
                const projects = dashboardData.reguler.map(item => item.project);
                const budgets = dashboardData.reguler.map(item => item.budget / 1000); // Convert to thousands
                const poSent = dashboardData.reguler.map(item => item.sent_amount / 1000);

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
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: false,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return 'IDR ' + val.toFixed(0) + 'K';
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '10px',
                            colors: ["#304758"]
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: projects,
                        title: {
                            text: 'Projects'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Amount (IDR in Thousands)'
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toFixed(0) + 'K';
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "IDR " + (val * 1000).toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    },
                    colors: ['#00E396', '#008FFB'],
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left'
                    }
                };

                chartInstances.budgetChart = new ApexCharts(document.querySelector("#budgetChart"), options);
                chartInstances.budgetChart.render();
            }

            // 2. Budget Distribution Pie Chart
            function createBudgetPieChart() {
                const projects = dashboardData.reguler.map(item => item.project);
                const budgets = dashboardData.reguler.map(item => item.budget);

                const options = {
                    series: budgets,
                    chart: {
                        type: 'donut',
                        height: 380,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    labels: projects,
                    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "IDR " + val.toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
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

                chartInstances.budgetPieChart = new ApexCharts(document.querySelector("#budgetPieChart"), options);
                chartInstances.budgetPieChart.render();
            }

            // 3. GRPO Completion Rate Chart
            function createGRPOChart() {
                const projects = dashboardData.grpo.map(item => item.project);
                const percentages = dashboardData.grpo.map(item => (item.percentage * 100).toFixed(2));
                const grpoAmounts = dashboardData.grpo.map(item => item.grpo_amount);
                const poAmounts = dashboardData.grpo.map(item => item.po_sent_amount);

                const options = {
                    series: [{
                        name: 'Completion Rate',
                        data: percentages
                    }],
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            dataLabels: {
                                position: 'top'
                            },
                            distributed: true,
                            colors: {
                                ranges: [{
                                    from: 0,
                                    to: 80,
                                    color: '#FF4560'
                                }, {
                                    from: 80,
                                    to: 95,
                                    color: '#FEB019'
                                }, {
                                    from: 95,
                                    to: 100,
                                    color: '#00E396'
                                }]
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val + "%";
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    xaxis: {
                        categories: projects,
                        title: {
                            text: 'Projects'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Completion Rate (%)'
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toFixed(0) + '%';
                            }
                        },
                        max: 110
                    },
                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const project = projects[dataPointIndex];
                            const percentage = percentages[dataPointIndex];
                            const grpo = grpoAmounts[dataPointIndex];
                            const po = poAmounts[dataPointIndex];

                            return '<div class="px-3 py-2">' +
                                '<strong>' + project + '</strong><br/>' +
                                'Completion: <strong>' + percentage + '%</strong><br/>' +
                                'GRPO: IDR ' + grpo.toLocaleString('id-ID') + '<br/>' +
                                'PO Sent: IDR ' + po.toLocaleString('id-ID') +
                                '</div>';
                        }
                    },
                    legend: {
                        show: false
                    }
                };

                chartInstances.grpoChart = new ApexCharts(document.querySelector("#grpoChart"), options);
                chartInstances.grpoChart.render();
            }

            // 4. GRPO Gauge Chart
            function createGRPOGaugeChart() {
                const percentage = (dashboardData.grpo_percentage * 100).toFixed(1);

                const options = {
                    series: [parseFloat(percentage)],
                    chart: {
                        type: 'radialBar',
                        height: 350,
                        offsetY: -10
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 135,
                            dataLabels: {
                                name: {
                                    fontSize: '16px',
                                    color: '#888',
                                    offsetY: 120
                                },
                                value: {
                                    offsetY: 76,
                                    fontSize: '36px',
                                    color: '#111',
                                    formatter: function(val) {
                                        return val + "%";
                                    }
                                }
                            },
                            track: {
                                background: '#e7e7e7',
                                strokeWidth: '97%',
                                margin: 5
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#00E396'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                        }
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                    labels: ['Overall GRPO Rate'],
                    colors: ['#008FFB']
                };

                chartInstances.grpoGaugeChart = new ApexCharts(document.querySelector("#grpoGaugeChart"), options);
                chartInstances.grpoGaugeChart.render();
            }

            // 5. NPI Production Index Chart
            function createNPIChart() {
                const projects = dashboardData.npi.map(item => item.project);
                const incomingQty = dashboardData.npi.map(item => item.incoming_qty);
                const outgoingQty = dashboardData.npi.map(item => item.outgoing_qty);
                const indexes = dashboardData.npi.map(item => item.percentage.toFixed(2));

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
                        height: 400,
                        stacked: false,
                        toolbar: {
                            show: true
                        }
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
                        categories: projects,
                        title: {
                            text: 'Projects'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Quantity (Units)'
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toLocaleString('id-ID');
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const project = projects[dataPointIndex];
                            const incoming = incomingQty[dataPointIndex];
                            const outgoing = outgoingQty[dataPointIndex];
                            const index = indexes[dataPointIndex];

                            return '<div class="px-3 py-2">' +
                                '<strong>' + project + '</strong><br/>' +
                                'In: <strong>' + incoming.toLocaleString('id-ID') + '</strong> units<br/>' +
                                'Out: <strong>' + outgoing.toLocaleString('id-ID') + '</strong> units<br/>' +
                                'Index: <strong>' + index + '</strong>' +
                                '</div>';
                        }
                    },
                    colors: ['#00E396', '#FF4560'],
                    legend: {
                        position: 'top'
                    }
                };

                chartInstances.npiChart = new ApexCharts(document.querySelector("#npiChart"), options);
                chartInstances.npiChart.render();
            }

            // 6. NPI Scatter Chart
            function createNPIScatterChart() {
                const scatterData = dashboardData.npi.map(item => ({
                    x: item.outgoing_qty,
                    y: item.incoming_qty,
                    z: item.percentage,
                    project: item.project
                }));

                const options = {
                    series: [{
                        name: 'Projects',
                        data: scatterData
                    }],
                    chart: {
                        type: 'scatter',
                        height: 400,
                        zoom: {
                            enabled: true,
                            type: 'xy'
                        },
                        toolbar: {
                            show: true
                        }
                    },
                    xaxis: {
                        title: {
                            text: 'Outgoing Quantity'
                        },
                        tickAmount: 10,
                        labels: {
                            formatter: function(val) {
                                return val.toLocaleString('id-ID');
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Incoming Quantity'
                        },
                        tickAmount: 7,
                        labels: {
                            formatter: function(val) {
                                return val.toLocaleString('id-ID');
                            }
                        }
                    },
                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const point = scatterData[dataPointIndex];
                            return '<div class="px-3 py-2">' +
                                '<strong>' + point.project + '</strong><br/>' +
                                'In: <strong>' + point.y.toLocaleString('id-ID') + '</strong> units<br/>' +
                                'Out: <strong>' + point.x.toLocaleString('id-ID') + '</strong> units<br/>' +
                                'Index: <strong>' + point.z.toFixed(2) + '</strong>' +
                                '</div>';
                        }
                    },
                    markers: {
                        size: 12,
                        colors: ['#008FFB'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 15
                        }
                    },
                    grid: {
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    }
                };

                chartInstances.npiScatterChart = new ApexCharts(document.querySelector("#npiScatterChart"), options);
                chartInstances.npiScatterChart.render();
            }

            // 7. Radar Chart for Overall Performance
            function createRadarChart() {
                const projects = dashboardData.reguler.map(item => item.project);

                // Normalize metrics to 0-100 scale for better visualization
                const budgetPerf = dashboardData.reguler.map(item => Math.min((item.percentage * 100), 100));
                const grpoPerf = dashboardData.grpo.map(item => item.percentage * 100);
                const npiPerf = dashboardData.npi.map(item => Math.min(item.percentage * 100, 100));

                const options = {
                    series: [{
                        name: 'Budget Performance',
                        data: budgetPerf
                    }, {
                        name: 'GRPO Completion',
                        data: grpoPerf
                    }, {
                        name: 'NPI Index (scaled)',
                        data: npiPerf
                    }],
                    chart: {
                        type: 'radar',
                        height: 450,
                        toolbar: {
                            show: true
                        }
                    },
                    xaxis: {
                        categories: projects
                    },
                    yaxis: {
                        show: true,
                        max: 120,
                        tickAmount: 6
                    },
                    stroke: {
                        width: 2
                    },
                    fill: {
                        opacity: 0.2
                    },
                    markers: {
                        size: 4
                    },
                    legend: {
                        position: 'bottom'
                    },
                    colors: ['#008FFB', '#00E396', '#FEB019'],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toFixed(1) + '%';
                            }
                        }
                    }
                };

                chartInstances.radarChart = new ApexCharts(document.querySelector("#radarChart"), options);
                chartInstances.radarChart.render();
            }

            // Export chart functionality
            function exportChart(chartId, format) {
                const chartKey = chartId;
                if (chartInstances[chartKey]) {
                    chartInstances[chartKey].dataURI().then(({
                        imgURI
                    }) => {
                        const link = document.createElement('a');
                        link.href = imgURI;
                        link.download = chartId + '_' + new Date().getTime() + '.' + format;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                }
            }
        @endif

        // Export functionality
        function exportDashboard() {
            // Get current year selection
            const selectedYear = $('#yearSelect').val();

            if (!selectedYear) {
                alert('Please select a year first!');
                return;
            }

            // Show export options modal
            showExportModal(selectedYear);
        }

        function showExportModal(selectedYear) {
            const modalHtml = `
        <div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-download mr-2"></i>
                            Export Dashboard Data
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Select export format for ${selectedYear === 'this_year' ? 'This Year' : selectedYear}:</p>
                        <div class="form-group">
                            <label>Export Format:</label>
                            <div class="row">
                                <div class="col-4">
                                    <button type="button" class="btn btn-success btn-block" onclick="performExport('${selectedYear}', 'excel')">
                                        <i class="fas fa-file-excel mr-1"></i>
                                        Excel
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-danger btn-block" onclick="performExport('${selectedYear}', 'pdf')">
                                        <i class="fas fa-file-pdf mr-1"></i>
                                        PDF
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-info btn-block" onclick="performExport('${selectedYear}', 'csv')">
                                        <i class="fas fa-file-csv mr-1"></i>
                                        CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Remove existing modal if any
            $('#exportModal').remove();

            // Add modal to body
            $('body').append(modalHtml);

            // Show modal
            $('#exportModal').modal('show');
        }

        function performExport(selectedYear, format) {
            // Hide modal
            $('#exportModal').modal('hide');

            // Show loading indicator
            const loadingHtml = `
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Preparing ${format.toUpperCase()} export...
        </div>
    `;
            $('.card-success .card-body').prepend(loadingHtml);

            // Create form and submit
            const form = $('<form>', {
                'method': 'POST',
                'action': '{{ route('dashboard.yearly.export') }}'
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'year',
                'value': selectedYear
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'format',
                'value': format
            }));

            $('body').append(form);
            form.submit();
            form.remove();

            // Remove loading indicator after a delay
            setTimeout(() => {
                $('.alert-info').remove();
            }, 3000);
        }
    </script>
@endsection
