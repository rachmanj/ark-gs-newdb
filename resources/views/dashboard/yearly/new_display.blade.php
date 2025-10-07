@extends('templates.main')

@section('title_page')
    <h1>Dashboard <small>(Yearly)</small></h1>
@endsection

@section('breadcrumb_title')
    dashboard / yearly
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
    @endif

    <!-- JavaScript for enhanced functionality -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for better dropdown UX
            $('#yearSelect').select2({
                placeholder: 'Select Year',
                allowClear: true,
                width: '100%'
            });

            // Add loading indicator on form submission
            $('#yearForm').on('submit', function() {
                $('#loadingIndicator').show();
                $('#dashboardContent').hide();
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
            });
        });

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
