@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(This Month)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> Report Date: {{ $report_date }}</p>
@endsection

@section('breadcrumb_title')
    dashboard / daily
@endsection

@section('styles')
    <!-- Animate.css -->
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" /> --}}
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/animate.min.css') }}">
    <style>
        .animate__animated {
            animation-duration: 0.8s;
        }

        .progress-bar {
            transition: width 1s ease-in-out;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .badge-dot {
            width: 10px;
            height: 10px;
            display: inline-block;
            border-radius: 50%;
        }

        .badge-dot.badge-success {
            background-color: #28a745;
        }

        .badge-dot.badge-danger {
            background-color: #dc3545;
        }

        .badge-dot.badge-warning {
            background-color: #ffc107;
        }

        /* Compact table styles */
        .table-compact td,
        .table-compact th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        /* Table shadow */
        .table-shadow {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.25rem;
            overflow: hidden;
        }

        /* Card body padding for tables */
        .card-table .card-body {
            padding: 0.75rem;
        }

        /* Daily production table styles */
        #current-month .table {
            font-size: 0.8rem;
            width: 100%;
            table-layout: fixed;
        }

        #current-month .table th,
        #current-month .table td {
            padding: 0.2rem 0.3rem;
            vertical-align: middle;
        }

        /* Make day columns narrower */
        #current-month .table th:nth-child(n+3),
        #current-month .table td:nth-child(n+3) {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Horizontal scrolling container */
        .horizontal-scroll {
            overflow-x: auto;
            position: relative;
            max-width: 100%;
        }

        /* Fixed width for first column */
        .sticky-col.first-col {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
            left: 0;
            position: sticky;
            z-index: 2;
            background-color: #fff;
        }

        /* Fixed width for second column */
        .sticky-col.second-col {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            left: 120px;
            position: sticky;
            z-index: 2;
            background-color: #fff;
        }

        /* Different background for odd rows */
        #current-month .table tbody tr:nth-child(odd) td.sticky-col {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Different background for header rows */
        #current-month .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 3;
        }

        /* Header cells that are also sticky columns need higher z-index */
        #current-month .table thead th.sticky-col {
            z-index: 4;
        }

        /* Shadow for sticky columns */
        #current-month .table .sticky-col.second-col {
            box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.1);
        }

        /* Total row styling */
        #current-month .table tr.font-weight-bold td {
            border-top: 2px solid #dee2e6;
            background-color: #f8f9fa !important;
        }

        /* Project name truncation for small screens */
        .project-name {
            max-width: 100px;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sticky-col.first-col {
                width: 100px;
                min-width: 100px;
                max-width: 100px;
            }

            .sticky-col.second-col {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                left: 100px;
            }

            .project-name {
                max-width: 80px;
            }

            #current-month .table th:nth-child(n+3),
            #current-month .table td:nth-child(n+3) {
                width: 35px;
                min-width: 35px;
                max-width: 35px;
            }
        }

        /* Improve scrollbar visibility */
        .horizontal-scroll::-webkit-scrollbar {
            height: 8px;
            background-color: #f1f1f1;
        }

        .horizontal-scroll::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }

        .horizontal-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #a8a8a8;
        }

        /* Trial ribbon */
        .ribbon-container {
            position: relative;
            overflow: hidden;
        }

        .ribbon {
            position: absolute;
            right: -35px;
            top: 15px;
            z-index: 10;
            background-color: #ff9800;
            color: white;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            width: 120px;
            transform: rotate(45deg);
            padding: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Different positioning for chart ribbons */
        .chart-ribbon {
            right: -32px;
            top: 12px;
        }
    </style>
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
                    <div class="card shadow-sm border-0 animate__animated animate__fadeIn">
                        <div class="card-body p-3 bg-gradient-light">
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('dashboard.summary-by-unit') }}" class="btn btn-primary btn-sm mr-2 mb-2">
                                    <i class="fas fa-chart-bar mr-1"></i> View Summary by Unit
                                </a>
                                <a href="{{ route('dashboard.search.po') }}" class="btn btn-info btn-sm mr-2 mb-2">
                                    <i class="fas fa-search mr-1"></i> Search PO
                                </a>
                                <a href="{{ route('dashboard.item.price.history') }}"
                                    class="btn btn-success btn-sm mr-2 mb-2">
                                    <i class="fas fa-history mr-1"></i> Search Item Price History
                                    {{-- <span class="badge badge-danger ml-1">New</span> --}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Overview Charts -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 animate__animated animate__fadeInLeft">
                        <div class="card-header bg-gradient-primary text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Performance Overview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 animate__animated animate__fadeInRight">
                        <div class="card-header bg-gradient-success text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Budget Allocation
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="budgetPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Main Data -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.daily.combined_main_data')
                </div>
            </div>

            <!-- Daily Production Data Section -->
            @include('dashboard.daily.production')

            <!-- CAPEX Data -->
            <div class="row">
                <div class="col-12 mb-4">
                    @include('dashboard.daily.capex')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Moment.js -->
    <script src="{{ asset('adminlte/plugins/moment/moment-2-29.js') }}"></script>
    <!-- Chart.js -->
    <script src="{{ asset('adminlte/plugins/chart.js/Chart-4.js') }}"></script>
    <!-- Chart.js Adapter for Moment.js -->
    <script src="{{ asset('adminlte/plugins/chart.js/chartjs-adapter-moment.min.js') }}"></script>

    <script>
        // Add animation to the progress bars after page load
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for animations to complete
            setTimeout(function() {
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(function(bar) {
                    const width = bar.getAttribute('aria-valuenow');
                    bar.style.width = Math.min(width, 100) + '%';
                });
            }, 1500);
        });

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
                animation: {
                    duration: 2000
                },
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
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000
                },
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
        var trendsCtx = document.getElementById('trendsChart');
        if (trendsCtx) {
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
                    animation: {
                        duration: 2000
                    },
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
        }
    </script>

    <!-- Production Charts JS -->
    <script>
        // URL for dashboard data
        const dashboardDataUrl = '{{ route('daily-production.dashboard-data') }}';
    </script>
    <script src="{{ asset('js/dashboard/production-charts.js') }}"></script>
@endsection
