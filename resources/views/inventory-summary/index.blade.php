@extends('templates.main')

@section('title_page')
    <h1>Inventory Summary
        <span class="text-muted font-weight-light">
            @if ($snapshot)
                (Snapshot: {{ $snapshot->snapshot_date->format('d M Y') }})
            @endif
        </span>
    </h1>
    <p class="text-muted"><i class="far fa-clock mr-1"></i> Latest successful SAP inventory snapshot</p>
@endsection

@section('breadcrumb_title')
    dashboard / inventory summary
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">

            @if ($warningMessage)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning shadow-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ $warningMessage }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPI Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($kpi['total_items']) }}</h3>
                            <p>Total Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 title="{{ \App\Http\Controllers\InventorySummaryController::formatRupiah($kpi['total_value']) }}">
                                {{ \App\Http\Controllers\InventorySummaryController::formatCompactValue($kpi['total_value']) }}
                            </h3>
                            <p>Total Value</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($kpi['warehouse_count']) }}</h3>
                            <p>Warehouses</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3 style="font-size:1.6rem;">
                                {{ $kpi['snapshot_date'] ? $kpi['snapshot_date']->format('d M Y') : '-' }}
                            </h3>
                            <p>Snapshot Date</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-warehouse mr-1"></i> Value by Warehouse
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:320px;">
                                <canvas id="warehouseValueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-info text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-project-diagram mr-1"></i> Instock by Project
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:320px;">
                                <canvas id="projectInstockChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-success text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-tags mr-1"></i> Value by Category
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:320px;">
                                <canvas id="categoryValueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-secondary text-white border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i> Total Value Trend (Last 12 Months)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:320px;">
                                <canvas id="monthlyTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pivot Tables -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-table mr-1"></i> Instock Qty: Project &times; Category
                            </h3>
                        </div>
                        <div class="card-body p-0" style="overflow-x:auto;">
                            <table class="table table-sm table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        @foreach ($instockPivot['categories'] as $category)
                                            <th class="text-right">{{ $category }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($instockPivot['projects'] as $project)
                                        <tr>
                                            <td>{{ $project }}</td>
                                            @foreach ($instockPivot['categories'] as $category)
                                                @php $cell = $instockPivot['matrix'][$project][$category] ?? null; @endphp
                                                <td class="text-right">
                                                    {{ $cell === null ? '-' : number_format($cell, 4, ',', '.') }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($instockPivot['categories']) + 1 }}" class="text-center text-muted">
                                                No data available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-table mr-1"></i> Value: Project &times; Category
                            </h3>
                        </div>
                        <div class="card-body p-0" style="overflow-x:auto;">
                            <table class="table table-sm table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        @foreach ($valuePivot['categories'] as $category)
                                            <th class="text-right">{{ $category }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($valuePivot['projects'] as $project)
                                        <tr>
                                            <td>{{ $project }}</td>
                                            @foreach ($valuePivot['categories'] as $category)
                                                @php $cell = $valuePivot['matrix'][$project][$category] ?? null; @endphp
                                                <td class="text-right">
                                                    {{ $cell === null ? '-' : number_format($cell, 2, ',', '.') }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($valuePivot['categories']) + 1 }}" class="text-center text-muted">
                                                No data available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js (v4, same asset as dashboard.daily.index) -->
    <script src="{{ asset('adminlte/plugins/chart.js/Chart-4.js') }}"></script>

    <script>
        var idrFormatter = new Intl.NumberFormat('id-ID');

        var warehouseChartData = @json($warehouseChart);
        var projectInstockChartData = @json($projectInstockChart);
        var categoryValueChartData = @json($categoryValueChart);
        var monthlyTrendChartData = @json($monthlyTrendChart);

        var palette = [
            'rgba(60, 141, 188, 0.8)', 'rgba(0, 166, 90, 0.8)', 'rgba(243, 156, 18, 0.8)',
            'rgba(221, 75, 57, 0.8)', 'rgba(162, 94, 245, 0.8)', 'rgba(0, 192, 239, 0.8)',
            'rgba(111, 66, 193, 0.8)', 'rgba(32, 201, 151, 0.8)', 'rgba(253, 126, 20, 0.8)',
            'rgba(23, 162, 184, 0.8)', 'rgba(108, 117, 125, 0.8)', 'rgba(214, 51, 132, 0.8)'
        ];

        function colorAt(i) {
            return palette[i % palette.length];
        }

        new Chart(document.getElementById('warehouseValueChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: warehouseChartData.labels,
                datasets: [{
                    label: 'Value',
                    data: warehouseChartData.values,
                    backgroundColor: warehouseChartData.labels.map((l, i) => colorAt(i))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + idrFormatter.format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return idrFormatter.format(value);
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('projectInstockChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: projectInstockChartData.labels,
                datasets: [{
                    label: 'Instock',
                    data: projectInstockChartData.values,
                    backgroundColor: projectInstockChartData.labels.map((l, i) => colorAt(i))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return idrFormatter.format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return idrFormatter.format(value);
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('categoryValueChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: categoryValueChartData.labels,
                datasets: [{
                    data: categoryValueChartData.values,
                    backgroundColor: categoryValueChartData.labels.map((l, i) => colorAt(i))
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                return label + ': Rp ' + idrFormatter.format(context.parsed);
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('monthlyTrendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: monthlyTrendChartData.labels,
                datasets: [{
                    label: 'Total Value',
                    data: monthlyTrendChartData.values,
                    fill: false,
                    spanGaps: false,
                    borderColor: 'rgba(60, 141, 188, 1)',
                    backgroundColor: 'rgba(60, 141, 188, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y === null ? 'No snapshot' : 'Rp ' + idrFormatter.format(
                                    context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return idrFormatter.format(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
