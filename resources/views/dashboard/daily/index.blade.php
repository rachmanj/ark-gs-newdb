@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(This Month)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Report Date: {{ $report_date }}</p>
@endsection

@section('breadcrumb_title')
    dashboard / daily
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Quick Stats Cards -->
            <div class="row">
                @include('dashboard.daily.row1')
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('dashboard.summary-by-unit') }}" class="btn btn-primary btn-sm mr-2 mb-2">
                                    <i class="fas fa-chart-bar mr-1"></i> View Summary by Unit
                                </a>
                                <a href="{{ route('dashboard.search.po') }}" class="btn btn-info btn-sm mb-2">
                                    <i class="fas fa-search mr-1"></i> Search PO
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Overview Charts -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Performance Overview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:250px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card card-success card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Budget Allocation
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:250px;">
                                <canvas id="budgetPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular & GRPO Data -->
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
                            @include('dashboard.daily.reguler')
                        </div>
                    </div>
                </div>

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
                            @include('dashboard.daily.grpo')
                        </div>
                    </div>
                </div>
            </div>

            <!-- NPI & CAPEX Data -->
            <div class="row">
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
                            @include('dashboard.daily.npi')
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-danger card-outline shadow-sm">
                        <div class="card-header border-0 d-flex align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-1"></i>
                                CAPEX
                            </h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @include('dashboard.daily.capex')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends Chart -->
            {{-- <div class="row">
                <div class="col-12 mb-4">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-area mr-1"></i>
                                Monthly Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="trendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Performance Chart
        var performanceCtx = document.getElementById('performanceChart').getContext('2d');
        var performanceChart = new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($reguler_daily['reguler'] as $item)
                        '{{ $item['project'] }}',
                    @endforeach
                ],
                datasets: [{
                        label: 'PO Sent',
                        data: [
                            @foreach ($reguler_daily['reguler'] as $item)
                                {{ $item['sent_amount'] / 1000 }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(60, 141, 188, 0.8)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Budget',
                        data: [
                            @foreach ($reguler_daily['reguler'] as $item)
                                {{ $item['budget'] / 1000 }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(210, 214, 222, 0.8)',
                        borderColor: 'rgba(210, 214, 222, 1)',
                        borderWidth: 1,
                        type: 'bar'
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
                            text: 'Amount (IDR 000)'
                        }
                    }
                }
            }
        });

        // Budget Pie Chart
        var budgetCtx = document.getElementById('budgetPieChart').getContext('2d');
        var budgetPieChart = new Chart(budgetCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($reguler_daily['reguler'] as $item)
                        '{{ $item['project'] }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($reguler_daily['reguler'] as $item)
                            {{ $item['budget'] / 1000 }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(60, 141, 188, 0.8)',
                        'rgba(0, 192, 239, 0.8)',
                        'rgba(0, 166, 90, 0.8)',
                        'rgba(243, 156, 18, 0.8)',
                        'rgba(221, 75, 57, 0.8)',
                        'rgba(162, 94, 245, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart
        var trendsCtx = document.getElementById('trendsChart').getContext('2d');
        var trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                        label: 'PO Sent',
                        data: [65, 59, 80, 81, 56, 55, 40, 56, 76, 85, 90,
                            {{ $reguler_daily['sent_total'] / 1000 }}
                        ],
                        fill: false,
                        borderColor: 'rgba(60, 141, 188, 1)',
                        tension: 0.1
                    },
                    {
                        label: 'GRPO',
                        data: [28, 48, 40, 19, 86, 27, 90, 85, 91, 52, 73,
                            {{ $grpo_daily['total_grpo_amount'] / 1000 }}
                        ],
                        fill: false,
                        borderColor: 'rgba(0, 166, 90, 1)',
                        tension: 0.1
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
                            text: 'Amount (IDR 000)'
                        }
                    }
                }
            }
        });
    </script>
@endsection
