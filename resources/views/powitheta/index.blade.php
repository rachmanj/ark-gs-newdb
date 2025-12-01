@extends('templates.main')

@section('title_page')
    <h1>PO With ETA</h1>
@endsection

@section('breadcrumb_title')
    powitheta
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    @if (Session::has('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ Session::get('error') }}
                        </div>
                    @endif
                    <a href="#"><b>THIS MONTH </b></a> |
                    <a href="{{ route('powitheta.index_this_year') }}">This Year</a> |
                    <a href="{{ route('dashboard.search.po') }}">Search PO</a>

                    @can('upload_data')
                        <button type="button" class="btn btn-sm btn-danger float-right {{ $is_data === 1 ? '' : 'disabled' }}"
                            onclick="confirmTruncate()" {{ $is_data === 1 ? '' : 'disabled' }}>
                            <i class="fas fa-trash"></i> Truncate Table
                        </button>
                        <button class="btn btn-sm btn-success float-right mx-2" data-toggle="modal"
                            data-target="#modal-upload_newdb" {{ $is_data === 1 ? 'disabled' : '' }}>
                            <i class="fas fa-upload"></i> Upload New DB
                        </button>
                        <button class="btn btn-sm btn-primary float-right mx-2" data-toggle="modal" data-target="#modal-sync"
                            {{ $is_data === 1 ? 'disabled' : '' }}><i class="fas fa-sync"></i> Sync from SAP</button>
                        <a href="#" class="btn btn-sm btn-success float-right mx-2 {{ $is_data === 1 ? '' : 'disabled' }}"
                            onclick="{{ $is_data === 1 ? 'convertToPO(event)' : 'return false' }}">
                            <i class="fas fa-upload"></i> Convert to PO
                        </a>
                    @endcan

                    <a href="{{ route('powitheta.export_this_month') }}"
                        class="btn btn-sm btn-info float-right {{ $is_data === 1 ? '' : 'disabled' }}">
                        <i class="fas fa-save"></i> Export to Excel
                    </a>
                    {{-- <a href="{{ route('powitheta.export_summary') }}"
                        class="btn btn-sm btn-primary float-right mx-2 {{ $is_data === 1 ? '' : 'disabled' }}">
                        <i class="fas fa-chart-bar"></i> Monthly Summary
                    </a> --}}
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="powitheta">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PO</th>
                                <th>DeliverDate</th>
                                <th>Project</th>
                                <th>UnitNo</th>
                                <th>Item</th>
                                <th>BudgetType</th>
                                <th>IDR</th>
                                <th>action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-upload_newdb">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> PO With ETA Upload New DB</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('powitheta.import_excel') }}" enctype="multipart/form-data" method="POST"
                    id="importForm">
                    @csrf
                    <div class="modal-body">
                        <label>Pilih file excel</label>
                        <div class="form-group">
                            <input type="file" name='file_upload' required class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="submitImport(event)">Upload</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <div class="modal fade" id="modal-upload_olddb">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> PO With ETA Upload Old DB</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('powitheta.import_oldDB') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="modal-body">
                        <label>Pilih file excel</label>
                        <div class="form-group">
                            <input type="file" name='file_upload' required class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary"> Upload</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <div class="modal fade" id="modal-sync">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Sync PO With ETA from SAP</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('powitheta.sync_from_sap') }}" method="POST" id="syncForm">
                    @csrf
                    <div class="modal-body">
                        <p>Sync PO With ETA data from SAP SQL Server. Default date range: 2024-12-01 to today.</p>
                        <div class="form-group">
                            <label>Start Date (Optional)</label>
                            <input type="date" name="start_date" class="form-control" 
                                value="{{ \Carbon\Carbon::parse('2024-12-01')->format('Y-m-d') }}">
                            <small class="form-text text-muted">Leave empty to use 2024-12-01</small>
                        </div>
                        <div class="form-group">
                            <label>End Date (Optional)</label>
                            <input type="date" name="end_date" class="form-control" 
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            <small class="form-text text-muted">Leave empty to use today</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary" onclick="submitSync(event)">Sync from SAP</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div> <!-- /.modal-dialog -->
    </div> <!-- /.modal -->
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminlte/plugins/datatables/css/datatables.min.css') }}" />
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables/datatables.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(function() {
            @if (Session::has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ Session::get('success') }}',
                    confirmButtonColor: '#3085d6',
                });
            @endif

            @if (Session::has('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ Session::get('error') }}',
                    confirmButtonColor: '#d33',
                });
            @endif

            $("#powitheta").DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('powitheta.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'po_no'
                    },
                    {
                        data: 'po_delivery_date'
                    },
                    {
                        data: 'project_code'
                    },
                    {
                        data: 'unit_no'
                    },
                    {
                        data: 'item_code'
                    },
                    {
                        data: 'budget_type'
                    },
                    {
                        data: 'item_amount'
                    },
                    {
                        data: 'action'
                    },
                ],
                fixedHeader: true,
                columnDefs: [{
                    "targets": [7],
                    "className": "text-right"
                }]
            })
        });

        function convertToPO(event) {
            event.preventDefault();

            // Check if button is disabled
            if ($(event.currentTarget).hasClass('disabled')) {
                return false;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to convert the data to Purchase Orders",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, convert it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Converting...',
                        html: 'Please wait while we process your request',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Start the conversion process
                    $.ajax({
                        url: "{{ route('powitheta.convert_to_po') }}",
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred during conversion.',
                            });
                        }
                    });
                }
            });
        }

        function submitImport(event) {
            event.preventDefault();

            // Get the form
            const form = document.getElementById('importForm');
            const formData = new FormData(form);

            // Validate file input
            const fileInput = form.querySelector('input[name="file_upload"]');
            if (!fileInput.files.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Please select a file to upload.',
                });
                return;
            }

            // Show loading state for import
            Swal.fire({
                title: 'Importing...',
                html: 'Please wait while we import your data',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit the form with AJAX
            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        // First show import success
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Successful!',
                            text: response.import_message,
                            confirmButtonText: 'Continue',
                            allowOutsideClick: false,
                            footer: response.file_cleaned ? 'Temporary file has been cleaned up' : ''
                        }).then(() => {
                            // Then show conversion result
                            if (response.convert_success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Conversion Complete!',
                                    text: response.convert_message,
                                }).then(() => {
                                    // Close modal and refresh page
                                    $('#modal-upload_newdb').modal('hide');
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Import Success, Conversion Failed',
                                    text: response.convert_message,
                                }).then(() => {
                                    // Close modal and refresh page
                                    $('#modal-upload_newdb').modal('hide');
                                    window.location.reload();
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred during upload.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                    });
                }
            });
        }

        function confirmTruncate() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete all PO With ETA records. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, truncate it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('powitheta.truncate') }}';
                }
            });
        }

        function submitSync(event) {
            event.preventDefault();

            const form = document.getElementById('syncForm');
            const startDate = form.querySelector('input[name="start_date"]').value;
            const endDate = form.querySelector('input[name="end_date"]').value;

            $('#modal-sync').modal('hide');

            let progress = 0;
            
            Swal.fire({
                title: 'Syncing from SAP...',
                html: `
                    <div style="text-align: left;">
                        <p style="margin-bottom: 10px;">Syncing data from SAP SQL Server...</p>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;"><strong>Date Range:</strong> ${startDate || '2024-12-01'} to ${endDate || 'Today'}</p>
                        <div style="background: #f0f0f0; border-radius: 10px; height: 25px; margin-bottom: 10px; overflow: hidden;">
                            <div id="progress-bar" style="background: linear-gradient(90deg, #3085d6 0%, #5dade2 100%); height: 100%; width: ${progress}%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                ${Math.round(progress)}%
                            </div>
                        </div>
                        <p style="font-size: 12px; color: #666; margin: 0;">Please wait, this may take a few moments...</p>
                    </div>
                `,
                icon: null,
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                Swal.update({
                    html: `
                        <div style="text-align: left;">
                            <p style="margin-bottom: 10px;">Syncing data from SAP SQL Server...</p>
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px;"><strong>Date Range:</strong> ${startDate || '2024-12-01'} to ${endDate || 'Today'}</p>
                            <div style="background: #f0f0f0; border-radius: 10px; height: 25px; margin-bottom: 10px; overflow: hidden;">
                                <div id="progress-bar" style="background: linear-gradient(90deg, #3085d6 0%, #5dade2 100%); height: 100%; width: ${progress}%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                    ${Math.round(progress)}%
                                </div>
                            </div>
                            <p style="font-size: 12px; color: #666; margin: 0;">Please wait, this may take a few moments...</p>
                        </div>
                    `
                });
            }, 200);

            const formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                
                Swal.update({
                    html: `
                        <div style="text-align: left;">
                            <p style="margin-bottom: 10px;">${data.success ? 'Syncing completed!' : 'Sync failed!'}</p>
                            <div style="background: #f0f0f0; border-radius: 10px; height: 25px; margin-bottom: 10px; overflow: hidden;">
                                <div style="background: ${data.success ? 'linear-gradient(90deg, #28a745 0%, #5cb85c 100%)' : 'linear-gradient(90deg, #dc3545 0%, #e74c3c 100%)'}; height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                    ${data.success ? '100%' : 'Error'}
                                </div>
                            </div>
                        </div>
                    `
                });

                setTimeout(() => {
                    let message = data.message;
                    if (data.convert_success !== undefined && data.convert_message) {
                        message += ' ' + data.convert_message;
                    }
                    
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Success!' : 'Error!',
                        text: message,
                        confirmButtonColor: data.success ? '#3085d6' : '#d33',
                    }).then(() => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while syncing from SAP. Please try again.',
                    confirmButtonColor: '#d33',
                });
            });
        }
    </script>
@endsection
