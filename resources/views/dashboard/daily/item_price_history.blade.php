@extends('templates.main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Item Price History</h4>
                    <a href="{{ route('dashboard.daily.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="searchForm">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="item_code">Item Code</label>
                                <input type="text" class="form-control" id="item_code" name="item_code">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="description">Item Description</label>
                                <input type="text" class="form-control" id="description" name="description">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit_no">Unit Number</label>
                                <select class="form-control select2bs4" id="unit_no" name="unit_no">
                                    <option value="">Select Unit</option>
                                    @foreach ($unit_nos as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">Reset</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-sm" id="priceHistoryTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th class="text-center">PO Number</th>
                                <th>PO Date</th>
                                <th>Supplier</th>
                                <th>Unit No</th>
                                <th class="text-right">Qty</th>
                                <th>UOM</th>
                                <th class="text-right">Price (IDR)</th>
                                <th class="text-right">Total (IDR)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for PO Details -->
    <div class="modal fade" id="poDetailModal" tabindex="-1" role="dialog" aria-labelledby="poDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="poDetailModalLabel">Purchase Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>PO Information</h6>
                            <table class="table table-bordered table-striped">
                                <tbody id="poDetailsTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>PO Items</h6>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Price IDR</th>
                                        <th class="text-right">Total IDR</th>
                                    </tr>
                                </thead>

                                <tbody id="poItemsTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminlte/plugins/datatables/css/datatables.min.css') }}" />
    {{-- select2bs4 --}}
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables/datatables.min.js') }}"></script>
    {{-- select2bs4 --}}
    <script src="{{ asset('adminlte/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize select2bs4 for dropdowns
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Variable to track if initial search has been performed
            let initialSearchDone = false;

            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#priceHistoryTable')) {
                $('#priceHistoryTable').DataTable().destroy();
            }

            // Initialize DataTable with empty data (defer: true)
            let table = $('#priceHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthChange: true,
                pageLength: 10,
                responsive: true,
                deferLoading: 0, // Don't load data initially
                ajax: {
                    url: "{{ route('dashboard.item.price.history.results') }}",
                    type: 'POST',
                    data: function(d) {
                        // If reset was clicked without a search being performed, prevent any data from loading
                        if (!initialSearchDone) {
                            // Return dummy values that won't match anything
                            d.item_code = '_EMPTY_SEARCH_';
                            d.description = '_EMPTY_SEARCH_';
                            d.unit_no = '_EMPTY_SEARCH_';
                        } else {
                            d.item_code = $('#item_code').val();
                            d.description = $('#description').val();
                            d.unit_no = $('#unit_no').val();
                        }
                        d._token = '{{ csrf_token() }}';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'item_code',
                        name: 'item_code'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'po_number',
                        name: 'po_number',
                        className: 'text-center'
                    },
                    {
                        data: 'po_date',
                        name: 'po_date'
                    },
                    {
                        data: 'supplier_name',
                        name: 'supplier_name'
                    },
                    {
                        data: 'unit_no',
                        name: 'unit_no'
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        className: 'text-right'
                    },
                    {
                        data: 'uom',
                        name: 'uom'
                    },
                    {
                        data: 'formatted_price',
                        name: 'unit_price',
                        className: 'text-right'
                    },
                    {
                        data: 'formatted_total',
                        name: 'item_amount',
                        className: 'text-right'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [4, 'desc'] // Order by PO date (column 4) descending
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                    emptyTable: 'No data available'
                }
            });

            // Handle form submission
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                initialSearchDone = true;
                table.draw();
            });

            // Reset the form and table completely
            $('#resetBtn').on('click', function() {
                // Clear form inputs
                $('#item_code').val('');
                $('#description').val('');
                $('#unit_no').val('').trigger('change');

                // Reset the search state
                initialSearchDone = false;

                // Clear the table completely
                table.clear().draw();

                // Hide processing indicator
                $('.dataTables_processing').hide();
            });

            // Handle PO detail view
            $('#priceHistoryTable').on('click', '.show-po', function() {
                var id = $(this).data('id');

                // Clear modal content
                $('#poDetailsTable').empty();
                $('#poItemsTable').empty();

                // Fetch and show PO details
                $.ajax({
                    url: "{{ url('dashboard/search-po') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Display PO details
                            $.each(response.data.po_details, function(key, value) {
                                $('#poDetailsTable').append(
                                    '<tr><td width="30%"><strong>' + key +
                                    '</strong></td><td>' + value + '</td></tr>'
                                );
                            });

                            // Display PO items
                            $.each(response.data.po_items, function(index, item) {
                                $('#poItemsTable').append(
                                    '<tr>' +
                                    '<td>' + item.item_code + '</td>' +
                                    '<td>' + item.description + '</td>' +
                                    '<td class="text-right">' + item.qty + ' ' +
                                    item.uom + '</td>' +
                                    '<td class="text-right">' + (parseFloat(item
                                        .unit_price).toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })) + '</td>' +
                                    '<td class="text-right">' + (parseFloat(item
                                        .item_amount)).toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + '</td>' +
                                    '</tr>'
                                );
                            });

                            // Show the modal
                            $('#poDetailModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load PO details'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load PO details'
                        });
                    }
                });
            });
        });
    </script>
@endsection
