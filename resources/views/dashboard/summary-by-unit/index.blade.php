@extends('templates.main')

@section('title_page')
    <h1>Summary by Unit</h1>
@endsection

@section('breadcrumb_title')
    dashboard / summary by unit
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Header with Back Button -->
            <div class="row mb-2">
                <div class="col-12">
                    <a href="{{ route('dashboard.daily.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <i class="fas fa-filter mr-2"></i>Filter Options
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="unit-search" class="text-sm">Select Unit No:</label>
                                        <select id="unit-search" class="form-control form-control-sm select2">
                                            <option value="">All Units</option>
                                            @php
                                                $unitNumbers = collect();
                                                foreach ($unitSummary['months'] as $month) {
                                                    foreach ($month['units'] as $unit) {
                                                        $unitNumbers->push($unit['unit_no']);
                                                    }
                                                }
                                                $unitNumbers = $unitNumbers->unique()->sort()->values();
                                            @endphp

                                            @foreach ($unitNumbers as $unitNo)
                                                <option value="{{ $unitNo }}">{{ $unitNo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Month Range:</label>
                                        <div class="d-flex">
                                            <select id="month-from" class="form-control form-control-sm mr-2">
                                                <option value="1">January</option>
                                                <option value="2">February</option>
                                                <option value="3">March</option>
                                                <option value="4">April</option>
                                                <option value="5">May</option>
                                                <option value="6">June</option>
                                                <option value="7">July</option>
                                                <option value="8">August</option>
                                                <option value="9">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                            <span class="align-self-center mx-1">to</span>
                                            <select id="month-to" class="form-control form-control-sm ml-2">
                                                <option value="1">January</option>
                                                <option value="2">February</option>
                                                <option value="3">March</option>
                                                <option value="4">April</option>
                                                <option value="5">May</option>
                                                <option value="6">June</option>
                                                <option value="7">July</option>
                                                <option value="8">August</option>
                                                <option value="9">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12" selected>December</option>
                                            </select>
                                            <button id="apply-month-filter" class="btn btn-sm btn-primary ml-2">
                                                <i class="fas fa-filter mr-1"></i>Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex justify-content-end align-items-end">
                                    <div class="btn-group">
                                        <a href="{{ route('summary.export') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-excel mr-1"></i>Excel
                                        </a>
                                        <a href="{{ route('summary.export.pdf') }}" class="btn btn-danger btn-sm">
                                            <i class="fas fa-file-pdf mr-1"></i>PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-info" id="filter-status">
                                                <i class="fas fa-info-circle mr-1"></i>Showing all units
                                            </span>
                                            <button id="clear-unit" class="btn btn-outline-primary btn-xs ml-2">
                                                <i class="fas fa-times mr-1"></i>Clear Unit
                                            </button>
                                            <button id="reset-all-filters" class="btn btn-outline-secondary btn-xs ml-2">
                                                <i class="fas fa-redo-alt mr-1"></i>Reset All Filters
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-muted small" id="unit-count"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="row">
                <div class="col-lg-9">
                    <div class="card card-primary card-outline shadow">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>Summary by Unit <small>(IDR 000)</small>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="summary-table-container">
                                @include('dashboard.summary-by-unit._table')
                            </div>
                            <!-- Loading Spinner -->
                            <div id="loader" class="text-center p-4 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2">Loading data...</p>
                            </div>
                            <!-- No Results Message -->
                            <div id="no-results" class="alert alert-info m-3 d-none">
                                <i class="fas fa-info-circle mr-2"></i>No units found matching your search criteria.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <!-- Statistics Card -->
                    <div class="card card-info card-outline shadow">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-2"></i>Statistics
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3">
                                <div class="info-box bg-gradient-info mb-3">
                                    <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Units</span>
                                        <span class="info-box-number" id="stats-total-units">
                                            @php
                                                $unitNumbers = collect();
                                                foreach ($unitSummary['months'] as $month) {
                                                    foreach ($month['units'] as $unit) {
                                                        $unitNumbers->push($unit['unit_no']);
                                                    }
                                                }
                                                echo $unitNumbers->unique()->count();
                                            @endphp
                                        </span>
                                    </div>
                                </div>

                                <div class="info-box bg-gradient-success mb-3">
                                    <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total POs</span>
                                        <span class="info-box-number" id="stats-total-pos">
                                            @php
                                                $totalPOs = 0;
                                                foreach ($unitSummary['yearly_totals'] as $unit) {
                                                    $totalPOs += $unit['total_po_count'];
                                                }
                                                echo number_format($totalPOs);
                                            @endphp
                                        </span>
                                    </div>
                                </div>

                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Year</span>
                                        <span class="info-box-number">{{ date('Y') }}</span>
                                        <div class="progress">
                                            @php
                                                $dayOfYear = date('z');
                                                $daysInYear = date('L') ? 366 : 365;
                                                $percentComplete = round(($dayOfYear / $daysInYear) * 100);
                                            @endphp
                                            <div class="progress-bar" style="width: {{ $percentComplete }}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ $percentComplete }}% of year completed
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend Card -->
                    <div class="card card-secondary card-outline shadow">
                        <div class="card-header py-2">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>Legend
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            <div class="callout callout-info py-2">
                                <p class="mb-0"><small><strong>IDR 000</strong> means values are in thousands of
                                        IDR</small></p>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge badge-primary mr-2">15.75</span>
                                <small>Amount in IDR thousands</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-secondary mr-2">5 PO</span>
                                <small>Number of Purchase Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <style>
        /* Table Styles */
        .table-wrapper {
            position: relative;
            max-height: 600px;
            overflow: auto;
            margin: 0;
            border-radius: 0.25rem;
        }

        .sticky-table {
            position: relative;
            border-collapse: separate;
            border-spacing: 0;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            font-weight: 600;
            color: #495057;
        }

        .sticky-col {
            position: sticky;
            background-color: white;
            border-right: 1px solid #dee2e6 !important;
            z-index: 2;
        }

        /* Fix for header and first column intersection */
        .sticky-col.sticky-header {
            z-index: 3;
        }

        /* Add shadow effects */
        .sticky-col::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0));
            pointer-events: none;
        }

        .sticky-header::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -4px;
            height: 4px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0));
            pointer-events: none;
        }

        /* Ensure borders are visible */
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        /* Fix background colors for zebra striping */
        .table-striped tbody tr:nth-of-type(odd) .sticky-col {
            background-color: #f8f9fa;
        }

        .table-striped tbody tr:nth-of-type(even) .sticky-col {
            background-color: #fff;
        }

        /* Hover effect on table rows */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.075);
        }

        .table-hover tbody tr:hover .sticky-col {
            background-color: rgba(0, 123, 255, 0.075);
        }

        /* Transitions for smooth interactions */
        .form-control,
        .btn {
            transition: all 0.3s ease;
        }

        /* Highlight active row */
        tr.highlight-row {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        tr.highlight-row .sticky-col {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        /* Custom Select2 Styles */
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.8125rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.8125rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: calc(1.8125rem + 2px) !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: "Select a unit",
                allowClear: true
            });

            // Initialize variables
            let currentUnitFilter = '';
            let currentMonthStart = 1;
            let currentMonthEnd = 12;
            updateUnitCount();

            // Unit search functionality
            $('#unit-search').on('change', function() {
                currentUnitFilter = $(this).val().trim().toLowerCase();
                applyFilters();
                updateFilterStatus();
            });

            // Clear unit button
            $('#clear-unit').on('click', function() {
                $('#unit-search').val('').trigger('change');
                currentUnitFilter = '';
                applyFilters();
                updateFilterStatus();
                showToast('Unit filter cleared', 'info');
            });

            // Month range filter
            $('#apply-month-filter').on('click', function() {
                currentMonthStart = parseInt($('#month-from').val());
                currentMonthEnd = parseInt($('#month-to').val());

                // Swap if start is greater than end
                if (currentMonthStart > currentMonthEnd) {
                    const temp = currentMonthStart;
                    currentMonthStart = currentMonthEnd;
                    currentMonthEnd = temp;

                    // Update the select values for visual feedback
                    $('#month-from').val(currentMonthStart);
                    $('#month-to').val(currentMonthEnd);
                }

                applyFilters();
                updateFilterStatus();
            });

            // Reset all filters
            $('#reset-all-filters').on('click', function() {
                // Reset unit filter
                $('#unit-search').val('').trigger('change');
                currentUnitFilter = '';

                // Reset month filter
                $('#month-from').val(1);
                $('#month-to').val(12);
                currentMonthStart = 1;
                currentMonthEnd = 12;

                // Apply changes
                applyFilters();
                updateFilterStatus();

                // Show success message
                showToast('All filters have been reset');
            });

            // Function to apply all active filters
            function applyFilters() {
                // Get all table headers (months)
                const $headers = $('#summary-table-container thead th');
                const monthHeaders = [];

                // Skip first and last columns (Unit No and Total)
                for (let i = 1; i < $headers.length - 1; i++) {
                    const monthName = $($headers[i]).text().trim();
                    const monthIndex = getMonthIndex(monthName);
                    monthHeaders.push({
                        index: i,
                        name: monthName,
                        monthIndex: monthIndex
                    });
                }

                // First filter visible months by month range
                monthHeaders.forEach(month => {
                    if (month.monthIndex >= currentMonthStart && month.monthIndex <= currentMonthEnd) {
                        $($headers[month.index]).removeClass('d-none');
                    } else {
                        $($headers[month.index]).addClass('d-none');
                    }
                });

                // Then filter rows by unit number
                let visibleCount = 0;
                $('#summary-table-container tbody tr').each(function() {
                    const unitNo = $(this).find('td:first').text().trim().toLowerCase();
                    const unitMatches = currentUnitFilter === '' || unitNo.includes(currentUnitFilter);

                    if (unitMatches) {
                        $(this).removeClass('d-none');
                        visibleCount++;

                        // Hide/show cells based on month range
                        $(this).find('td').each(function(cellIndex) {
                            // Skip first column (Unit No) and last column (Total)
                            if (cellIndex > 0 && cellIndex < $(this).parent().find('td').length -
                                1) {
                                const headerVisible = !$($headers[cellIndex]).hasClass('d-none');
                                if (headerVisible) {
                                    $(this).removeClass('d-none');
                                } else {
                                    $(this).addClass('d-none');
                                }
                            }
                        });
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    $('#no-results').removeClass('d-none');
                } else {
                    $('#no-results').addClass('d-none');
                }

                updateUnitCount();
            }

            // Function to update filter status indicator
            function updateFilterStatus() {
                let statusText = '';
                let activeFilters = [];

                if (currentUnitFilter !== '') {
                    activeFilters.push(`Unit: "${currentUnitFilter}"`);
                }

                if (currentMonthStart !== 1 || currentMonthEnd !== 12) {
                    const monthStartName = getMonthName(currentMonthStart);
                    const monthEndName = getMonthName(currentMonthEnd);
                    activeFilters.push(`Months: ${monthStartName} - ${monthEndName}`);
                }

                if (activeFilters.length > 0) {
                    statusText = `Filters: ${activeFilters.join(', ')}`;
                    $('#filter-status').removeClass('badge-info').addClass('badge-warning');
                } else {
                    statusText = 'Showing all units';
                    $('#filter-status').removeClass('badge-warning').addClass('badge-info');
                }

                $('#filter-status').html(`<i class="fas fa-filter mr-1"></i>${statusText}`);
            }

            // Function to update unit count
            function updateUnitCount() {
                const visibleCount = $('#summary-table-container tbody tr:not(.d-none)').length;
                const totalCount = $('#summary-table-container tbody tr').length;
                $('#unit-count').text(`Showing ${visibleCount} of ${totalCount} units`);
            }

            // Helper function to convert month name to index
            function getMonthIndex(monthName) {
                const months = {
                    'Jan': 1,
                    'Feb': 2,
                    'Mar': 3,
                    'Apr': 4,
                    'May': 5,
                    'Jun': 6,
                    'Jul': 7,
                    'Aug': 8,
                    'Sep': 9,
                    'Oct': 10,
                    'Nov': 11,
                    'Dec': 12
                };
                return months[monthName.trim()] || 0;
            }

            // Helper function to convert month index to name
            function getMonthName(monthIndex) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];
                return months[monthIndex - 1] || '';
            }

            // Toast notification function
            function showToast(message, type = 'success') {
                // Create toast container if it doesn't exist
                if ($('#toast-container').length === 0) {
                    $('body').append(
                        '<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>'
                    );
                }

                // Generate a unique ID for this toast
                const toastId = 'toast-' + Date.now();

                // Set the appropriate class based on type
                let typeClass = 'bg-success';
                if (type === 'error') typeClass = 'bg-danger';
                if (type === 'warning') typeClass = 'bg-warning';
                if (type === 'info') typeClass = 'bg-info';

                // Create toast HTML
                const toast = `
                    <div id="${toastId}" class="toast ${typeClass} text-white" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000" style="min-width: 250px;">
                        <div class="toast-header">
                            <strong class="mr-auto">Notification</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;

                // Add toast to container and show it
                $('#toast-container').append(toast);
                $(`#${toastId}`).toast('show');

                // Remove toast after it's hidden
                $(`#${toastId}`).on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Highlight row on hover for better UX
            $('#summary-table-container').on('mouseenter', 'tbody tr', function() {
                $(this).addClass('highlight-row');
            }).on('mouseleave', 'tbody tr', function() {
                $(this).removeClass('highlight-row');
            });

            // Make card header collapsible
            $('.card-header').css('cursor', 'pointer').on('click', function() {
                const $cardBody = $(this).siblings('.card-body');
                $cardBody.slideToggle(300);

                // Toggle icon if present
                const $icon = $(this).find('.card-tools .btn-tool i');
                if ($icon.length) {
                    $icon.toggleClass('fa-minus fa-plus');
                }
            });
        });
    </script>
@endsection
