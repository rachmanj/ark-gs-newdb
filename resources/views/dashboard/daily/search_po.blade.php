@extends('templates.main')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Search Purchase Orders</h4>
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
                                <label for="doc_num">Document Number</label>
                                <input type="text" class="form-control" id="doc_num" name="doc_num">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="project_code">Project Code</label>
                                <select class="form-control select2bs4" id="project_code" name="project_code">
                                    <option value="">Select Project</option>
                                    @foreach ($project_codes as $code)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="supplier_name">Supplier</label>
                                <select class="form-control select2bs4" id="supplier_name" name="supplier_name">
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pr_no">PR Number</label>
                                <input type="text" class="form-control" id="pr_no" name="pr_no">
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

                <div class="table-responsive">
                    <table class="table table-bordered" id="poTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>PO No</th>
                                <th>PO Date</th>
                                <th>Supplier</th>
                                <th>PR No</th>
                                <th>Unit No</th>
                                <th>Project Code</th>
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

            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#poTable')) {
                $('#poTable').DataTable().destroy();
            }

            let table = $('#poTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                lengthChange: true,
                pageLength: 10,
                responsive: true,
                ajax: {
                    url: "{{ route('dashboard.search.po.results') }}",
                    type: 'POST',
                    data: function(d) {
                        d.doc_num = $('#doc_num').val();
                        d.supplier_name = $('#supplier_name').val();
                        d.pr_no = $('#pr_no').val();
                        d.unit_no = $('#unit_no').val();
                        d.project_code = $('#project_code').val();
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
                        data: 'doc_num',
                        name: 'doc_num'
                    },
                    {
                        data: 'formatted_date',
                        name: 'doc_date'
                    },
                    {
                        data: 'supplier_name',
                        name: 'supplier_name'
                    },
                    {
                        data: 'pr_no',
                        name: 'pr_no'
                    },
                    {
                        data: 'unit_no',
                        name: 'unit_no'
                    },
                    {
                        data: 'project_code',
                        name: 'project_code'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                    emptyTable: 'No data available'
                }
            });

            // Handle form submission
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Handle reset button
            $('#resetBtn').on('click', function() {
                // Reset all form inputs
                $('#doc_num').val('');
                $('#supplier_name').val('').trigger('change');
                $('#pr_no').val('');
                $('#unit_no').val('').trigger('change');
                $('#project_code').val('').trigger('change');

                // Clear and reset the table
                table.clear().draw();
            });

            // Handle PO detail button click
            $('#poTable').on('click', '.show-po', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('dashboard.search.po.show', '') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Populate PO Details
                            let detailsHtml = '';
                            for (let [key, value] of Object.entries(response.data.po_details)) {
                                detailsHtml += `<tr>
                                <th>${key}</th>
                                <td>${value}</td>
                            </tr>`;
                            }
                            $('#poDetailsTable').html(detailsHtml);

                            // Populate PO Items
                            let itemsHtml = '';
                            response.data.po_items.forEach(item => {
                                itemsHtml += `<tr>
                                <td>${item.item_code}</td>
                                <td>${item.description}</td>
                                <td class="text-right">${item.qty}</td>
                                <td class="text-right">${parseFloat(item.unit_price).toLocaleString('id-ID')}</td>
                                <td class="text-right">${parseFloat(item.item_amount).toLocaleString('id-ID')}</td>
                            </tr>`;
                            });
                            $('#poItemsTable').html(itemsHtml);

                            $('#poDetailModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to load PO details', 'error');
                    }
                });
            });
        });
    </script>
@endsection
