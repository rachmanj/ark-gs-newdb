@extends('templates.main')

@section('title_page')
    <h1>Production Plans</h1>
@endsection

@section('breadcrumb_title')
    production plans
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modal-create">
                            <i class="fas fa-plus"></i> Add New Production Plan
                        </button>
                        <a href="{{ route('production-plan.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="production-plans-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Period</th>
                                <th>Product</th>
                                <th>UOM</th>
                                <th>Quantity</th>
                                <th>Project</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="modal-create">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Production Plan</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm">
                    @csrf
                    <div class="modal-body">
                        <div id="create-error-bag" class="alert alert-danger d-none">
                            <ul id="create-error-list"></ul>
                        </div>

                        <div class="form-group">
                            <label for="year">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="year" name="year"
                                value="{{ date('Y') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="month">Month <span class="text-danger">*</span></label>
                            <select class="form-control" id="month" name="month" required>
                                <option value="">Select Month</option>
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
                        </div>

                        <div class="form-group">
                            <label for="product">Product <span class="text-danger">*</span></label>
                            <select class="form-control" id="product" name="product" required>
                                <option value="">Select Product</option>
                                <option value="ob">OB</option>
                                <option value="coal">Coal</option>
                                <option value="fuel factor">Fuel Factor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="uom">UOM <span class="text-danger">*</span></label>
                            <select class="form-control" id="uom" name="uom" required>
                                <option value="">Select UOM</option>
                                <option value="bcm">BCM</option>
                                <option value="ton">Ton</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="qty">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="qty" name="qty"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="project">Project <span class="text-danger">*</span></label>
                            <select class="form-control" id="project" name="project" required>
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->code }}">
                                        {{ $project->code }} {{ $project->name ? '- ' . $project->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="modal-edit">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Production Plan</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div id="edit-error-bag" class="alert alert-danger d-none">
                            <ul id="edit-error-list"></ul>
                        </div>

                        <div class="form-group">
                            <label for="edit_year">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_year" name="year" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_month">Month <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_month" name="month" required>
                                <option value="">Select Month</option>
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
                        </div>

                        <div class="form-group">
                            <label for="edit_product">Product <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_product" name="product" required>
                                <option value="">Select Product</option>
                                <option value="ob">OB</option>
                                <option value="coal">Coal</option>
                                <option value="fuel factor">Fuel Factor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_uom">UOM <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_uom" name="uom" required>
                                <option value="">Select UOM</option>
                                <option value="bcm">BCM</option>
                                <option value="ton">Ton</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_qty">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="edit_qty" name="qty"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="edit_project">Project <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_project" name="project" required>
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->code }}">
                                        {{ $project->code }} {{ $project->name ? '- ' . $project->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal-label"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal-label">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this production plan?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminlte/plugins/datatables/css/datatables.min.css') }}" />
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/toastr/toastr.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables/datatables.min.js') }}"></script>
    <!-- Toastr -->
    <script src="{{ asset('adminlte/plugins/toastr/toastr.min.js') }}"></script>

    <script>
        $(function() {
            // Set up CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const table = $('#production-plans-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('production-plan.data') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'period',
                        name: 'period',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'product',
                        name: 'product',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1);
                        }
                    },
                    {
                        data: 'uom',
                        name: 'uom',
                        render: function(data) {
                            return data.toUpperCase();
                        }
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        render: function(data) {
                            return new Intl.NumberFormat('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(data);
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            // Initialize toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Create Form Submit
            $('#createForm').submit(function(e) {
                e.preventDefault();

                $('#create-error-bag').addClass('d-none');
                $('#create-error-list').html('');

                $.ajax({
                    url: "{{ route('production-plan.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modal-create').modal('hide');
                        $('#createForm')[0].reset();
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            $('#create-error-bag').removeClass('d-none');
                            let errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(key) {
                                $('#create-error-list').append('<li>' + errors[key][0] +
                                    '</li>');
                            });
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Edit Item
            window.editItem = function(id) {
                $('#edit-error-bag').addClass('d-none');
                $('#edit-error-list').html('');

                $.ajax({
                    url: `/production-plan/${id}/edit-data`,
                    method: 'GET',
                    success: function(response) {
                        $('#edit_id').val(response.id);
                        $('#edit_year').val(response.year);
                        $('#edit_month').val(response.month);
                        $('#edit_product').val(response.product);
                        $('#edit_uom').val(response.uom);
                        $('#edit_qty').val(response.qty);
                        $('#edit_project').val(response.project);
                        $('#modal-edit').modal('show');
                    }
                });
            };

            // Update Form Submit
            $('#editForm').submit(function(e) {
                e.preventDefault();
                const id = $('#edit_id').val();

                $('#edit-error-bag').addClass('d-none');
                $('#edit-error-list').html('');

                $.ajax({
                    url: `/production-plan/${id}`,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modal-edit').modal('hide');
                        table.ajax.reload();
                        toastr.success(response.success);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            $('#edit-error-bag').removeClass('d-none');
                            let errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(key) {
                                $('#edit-error-list').append('<li>' + errors[key][0] +
                                    '</li>');
                            });
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Delete Item
            let deleteId = null;

            window.deleteItem = function(id) {
                deleteId = id;
                $('#delete-modal').modal('show');
            };

            $('#confirm-delete').click(function() {
                if (deleteId) {
                    $.ajax({
                        url: `/production-plan/${deleteId}`,
                        type: 'DELETE',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            $('#delete-modal').modal('hide');
                            table.ajax.reload();
                            toastr.success(result.success);
                        },
                        error: function() {
                            toastr.error(
                                'An error occurred during deletion. Please try again.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
