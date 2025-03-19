@extends('templates.main')

@section('title_page')
    <h1>Dashboard <span class="text-muted font-weight-light">(Monthly)</span></h1>
    <p class="text-muted"><i class="far fa-calendar-alt mr-1"></i> View monthly performance reports</p>
@endsection

@section('breadcrumb_title')
    dashboard / monthly
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Month Selection Card -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Select Month to View Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('dashboard.monthly.display') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="text-muted">Select month and year</label>
                                    <div class="input-group">
                                        <input type="month" name="month" class="form-control"
                                            value="{{ date('Y-m') }}">
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
                                Monthly Report Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info rounded-circle p-3 mr-3">
                                    <i class="fas fa-chart-bar text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Comprehensive Analysis</h5>
                                    <p class="text-muted mb-0 small">View detailed monthly breakdown of performance across
                                        all projects</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle p-3 mr-3">
                                    <i class="fas fa-history text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Historical Comparison</h5>
                                    <p class="text-muted mb-0 small">Compare performance with previous months to identify
                                        trends</p>
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
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('dashboard.daily.index') }}"
                                        class="btn btn-block btn-outline-primary">
                                        <i class="fas fa-chart-line mr-1"></i> Daily Dashboard
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('dashboard.summary-by-unit') }}"
                                        class="btn btn-block btn-outline-info">
                                        <i class="fas fa-building mr-1"></i> Summary by Unit
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('dashboard.search.po') }}" class="btn btn-block btn-outline-success">
                                        <i class="fas fa-search mr-1"></i> Search PO
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Preview -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-success card-outline shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-area mr-1"></i>
                                Monthly Performance Preview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="monthlyTrendsChart"></canvas>
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
        // Monthly Trends Chart with real data
        var ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
        var monthlyTrendsChart = new Chart(ctx, {
            type: 'line',
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
