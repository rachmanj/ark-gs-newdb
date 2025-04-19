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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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

            <!-- Daily Production Data - Current Month -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
                        <div class="ribbon chart-ribbon">Trial</div>
                        <div class="card-header bg-gradient-info text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Daily Production Overview - Current Month
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="dailyProductionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Production Data -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
                        <div class="ribbon chart-ribbon">Trial</div>
                        <div class="card-header bg-gradient-success text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i>
                                Monthly Production Overview - {{ now()->year }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="yearlyProductionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Production Table -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
                        <div class="ribbon">Trial</div>
                        <div class="card-header bg-gradient-info text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-table mr-1"></i>
                                Production Summary by Project
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="productionTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="current-month-tab" data-toggle="tab"
                                        href="#current-month" role="tab" aria-controls="current-month"
                                        aria-selected="true">This Month</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="yearly-tab" data-toggle="tab" href="#yearly-data" role="tab"
                                        aria-controls="yearly-data" aria-selected="false">{{ now()->year }} Summary</a>
                                </li>
                            </ul>
                            <div class="tab-content pt-3" id="productionTabsContent">
                                <div class="tab-pane fade show active" id="current-month" role="tabpanel"
                                    aria-labelledby="current-month-tab">
                                    <div class="table-responsive-xl horizontal-scroll">
                                        <table
                                            class="table table-bordered table-striped table-hover table-compact table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center align-middle sticky-col first-col">Project</th>
                                                    <th class="text-center align-middle sticky-col second-col">Total</th>
                                                    <th class="text-center day-column" id="day-columns">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="dailyProductionTableBody">
                                                <tr>
                                                    <td colspan="33" class="text-center">
                                                        <div class="spinner-border" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                        <p class="mt-2">Loading production data...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="yearly-data" role="tabpanel"
                                    aria-labelledby="yearly-tab">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-compact">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center">Project</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Monthly Average</th>
                                                </tr>
                                            </thead>
                                            <tbody id="yearlyProductionTableBody">
                                                <tr>
                                                    <td colspan="3" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

    <!-- Daily Production Chart -->
    <script>
        // Create color palette for the lines
        const colorPalette = [
            'rgba(54, 162, 235, 1)', // Blue
            'rgba(255, 99, 132, 1)', // Red
            'rgba(75, 192, 192, 1)', // Green
            'rgba(255, 159, 64, 1)', // Orange
            'rgba(153, 102, 255, 1)', // Purple
            'rgba(255, 205, 86, 1)', // Yellow
            'rgba(201, 203, 207, 1)' // Grey
        ];

        // Fetch production data
        fetch('{{ route('daily-production.dashboard-data') }}')
            .then(response => response.json())
            .then(data => {
                // Initialize current month chart data
                if (data.current_month && data.current_month.chart_data) {
                    renderCurrentMonthChart(data.current_month);
                    populateCurrentMonthTable(data.current_month);
                } else {
                    showNoDataMessage('dailyProductionChart', 'dailyProductionTableBody');
                }

                // Initialize yearly chart data
                if (data.yearly && data.yearly.chart_data) {
                    renderYearlyChart(data.yearly);
                    populateYearlyTable(data.yearly);
                } else {
                    showNoDataMessage('yearlyProductionChart', 'yearlyProductionTableBody');
                }
            })
            .catch(error => {
                console.error('Error fetching production data:', error);
                showErrorMessage('dailyProductionChart', 'dailyProductionTableBody');
                showErrorMessage('yearlyProductionChart', 'yearlyProductionTableBody');
            });

        // Render current month chart
        function renderCurrentMonthChart(monthData) {
            const chartData = {
                datasets: []
            };

            // Generate all days in the current month
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate(); // Last day of current month

            // Create array of all days in the month (1-31)
            const allDays = Array.from({
                length: daysInMonth
            }, (_, i) => i + 1);

            // Process data for each project
            monthData.chart_data.forEach((series, index) => {
                // Initialize data array with zeros for all days
                const formattedData = allDays.map(day => ({
                    x: day,
                    y: 0
                }));

                // Fill in actual data where available
                series.data.forEach(point => {
                    const date = new Date(point.x);
                    const dayNumber = date.getDate();
                    // Find the corresponding day in our formatted data and update it
                    const dayIndex = formattedData.findIndex(item => item.x === dayNumber);
                    if (dayIndex !== -1) {
                        formattedData[dayIndex].y = point.y;
                    }
                });

                // Create dataset for each project
                chartData.datasets.push({
                    label: series.name,
                    data: formattedData,
                    borderColor: colorPalette[index % colorPalette.length],
                    backgroundColor: colorPalette[index % colorPalette.length].replace('1)', '0.1)'),
                    tension: 0.3,
                    fill: false,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5
                });
            });

            // Create and render chart
            const dailyProductionChart = new Chart(
                document.getElementById('dailyProductionChart').getContext('2d'), {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1500
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                title: {
                                    display: true,
                                    text: 'Day of Month'
                                },
                                min: 1,
                                max: daysInMonth,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        // Return only the day number
                                        return value;
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Total Production'
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: function(tooltipItems) {
                                        return 'Day ' + tooltipItems[0].parsed.x;
                                    },
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                    }
                                }
                            },
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            title: {
                                display: true,
                                text: monthData.month_name,
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                }
            );
        }

        // Render yearly chart
        function renderYearlyChart(yearlyData) {
            const chartData = {
                labels: yearlyData.months,
                datasets: []
            };

            // Configure datasets
            yearlyData.chart_data.forEach((series, index) => {
                // Create dataset for each project
                chartData.datasets.push({
                    label: series.name,
                    data: series.data,
                    backgroundColor: colorPalette[index % colorPalette.length].replace('1)', '0.7)'),
                    borderColor: colorPalette[index % colorPalette.length],
                    borderWidth: 1
                });
            });

            // Create and render chart
            const yearlyProductionChart = new Chart(
                document.getElementById('yearlyProductionChart').getContext('2d'), {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1500
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Total Production'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw.toLocaleString();
                                    }
                                }
                            },
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            title: {
                                display: true,
                                text: 'Monthly Production - ' + yearlyData.year,
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                }
            );
        }

        // Populate current month table
        function populateCurrentMonthTable(monthData) {
            const tableBody = document.getElementById('dailyProductionTableBody');
            const dayColumns = document.getElementById('day-columns');

            // Clear loading content
            tableBody.innerHTML = '';

            if (!monthData.table_data || monthData.table_data.length === 0 || !monthData.dates || monthData.dates.length ===
                0) {
                tableBody.innerHTML =
                    '<tr><td colspan="33" class="text-center">No production data available for this month</td></tr>';
                dayColumns.innerHTML = '';
                return;
            }

            // Sort dates chronologically
            const sortedDates = [...monthData.dates].sort();

            // Create day column headers - replace the single placeholder th with multiple th elements
            const dayColumnsParent = dayColumns.parentElement;
            dayColumns.remove(); // Remove the placeholder

            // Add a th for each date
            sortedDates.forEach(date => {
                // Get just the day number
                const dayNumber = new Date(date).getDate();

                const newTh = document.createElement('th');
                newTh.className = 'text-center';
                newTh.textContent = dayNumber;
                dayColumnsParent.appendChild(newTh);
            });

            // Populate the table rows
            monthData.table_data.forEach((project, index) => {
                // Create row
                const row = document.createElement('tr');

                // Project name and summary columns with proper sticky classes
                let rowHtml = `
                    <td class="sticky-col first-col">
                        <span class="badge-dot" style="background-color: ${colorPalette[index % colorPalette.length]}"></span>
                        <span class="project-name">${project.name}</span>
                    </td>
                    <td class="text-right sticky-col second-col">${project.total.toLocaleString('en-US', {maximumFractionDigits: 1, minimumFractionDigits: 1})}</td>
                `;

                // Add values for each day
                sortedDates.forEach(date => {
                    const value = project.days[date] || 0;
                    // Format with 1 decimal place
                    const formattedValue = value > 0 ? value.toLocaleString('en-US', {
                        maximumFractionDigits: 1,
                        minimumFractionDigits: 1
                    }) : '-';
                    rowHtml += `<td class="text-right">${formattedValue}</td>`;
                });

                row.innerHTML = rowHtml;
                tableBody.appendChild(row);
            });

            // Add a total row
            const totalRow = document.createElement('tr');
            totalRow.classList.add('font-weight-bold', 'bg-light');

            // Project name and summary columns for total row
            const grandTotal = monthData.table_data.reduce((sum, project) => sum + project.total, 0);
            let totalRowHtml = `
                <td class="sticky-col first-col">TOTAL</td>
                <td class="text-right sticky-col second-col">${grandTotal.toLocaleString('en-US', {maximumFractionDigits: 1, minimumFractionDigits: 1})}</td>
            `;

            // Add total for each day
            sortedDates.forEach(date => {
                const dailyTotal = monthData.table_data.reduce((sum, project) => sum + (project.days[date] || 0),
                    0);
                const formattedDailyTotal = dailyTotal > 0 ?
                    dailyTotal.toLocaleString('en-US', {
                        maximumFractionDigits: 1,
                        minimumFractionDigits: 1
                    }) : '-';
                totalRowHtml += `<td class="text-right">${formattedDailyTotal}</td>`;
            });

            totalRow.innerHTML = totalRowHtml;
            tableBody.appendChild(totalRow);

            // Add horizontal scroll hints if needed
            const tableContainer = document.querySelector('.horizontal-scroll');
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                const scrollHint = document.createElement('div');
                scrollHint.className = 'text-muted small text-right mt-1';
                scrollHint.innerHTML = '<i class="fas fa-arrows-alt-h mr-1"></i>Scroll horizontally to view all days';
                tableContainer.parentNode.appendChild(scrollHint);
            }
        }

        // Populate yearly table
        function populateYearlyTable(yearlyData) {
            const tableBody = document.getElementById('yearlyProductionTableBody');
            tableBody.innerHTML = ''; // Clear loading message

            if (yearlyData.table_data && yearlyData.table_data.length > 0) {
                yearlyData.table_data.forEach((project, index) => {
                    // Calculate monthly average - only count months with data
                    const monthsWithData = project.months.filter(val => val > 0).length ||
                        1; // Avoid division by zero
                    const monthlyAverage = (project.total / monthsWithData).toFixed(2);

                    // Create row
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <span class="badge-dot" style="background-color: ${colorPalette[index % colorPalette.length]}"></span>
                            ${project.name}
                        </td>
                        <td class="text-right">${project.total.toLocaleString()}</td>
                        <td class="text-right">${monthlyAverage.toLocaleString()}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML =
                    '<tr><td colspan="3" class="text-center">No yearly production data available</td></tr>';
            }
        }

        // Show no data message
        function showNoDataMessage(chartId, tableId) {
            document.getElementById(chartId).parentNode.innerHTML =
                '<div class="alert alert-info m-3">No production data available for the selected period</div>';
            document.getElementById(tableId).innerHTML =
                '<tr><td colspan="3" class="text-center">No production data available</td></tr>';
        }

        // Show error message
        function showErrorMessage(chartId, tableId) {
            document.getElementById(chartId).parentNode.innerHTML =
                '<div class="alert alert-danger m-3">Error loading production data</div>';
            document.getElementById(tableId).innerHTML =
                '<tr><td colspan="3" class="text-center">Error loading production data</td></tr>';
        }
    </script>
@endsection
