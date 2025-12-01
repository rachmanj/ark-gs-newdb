@extends('templates.main')

@section('title_page')
    <h1>GRPO</h1>
@endsection

@section('breadcrumb_title')
    grpo
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
                    <a href="{{ route('grpo.index_this_year') }}">This Year</a>

                    @can('upload_data')
                        <button type="button" class="btn btn-sm btn-danger float-right {{ $is_data === 1 ? '' : 'disabled' }}"
                            onclick="confirmTruncate()" {{ $is_data === 1 ? '' : 'disabled' }}><i
                                class="fas fa-trash"></i> Truncate Table</button>
                        <button class="btn btn-sm btn-success float-right mx-2" data-toggle="modal" data-target="#modal-upload"
                            {{ $is_data === 1 ? 'disabled' : '' }}><i class="fas fa-upload"></i> Upload</button>
                        <button class="btn btn-sm btn-primary float-right mx-2" data-toggle="modal" data-target="#modal-sync"
                            {{ $is_data === 1 ? 'disabled' : '' }}><i class="fas fa-sync"></i> Sync from SAP</button>
                    @endcan

                    <a href="{{ route('grpo.export_this_month') }}"
                        class="btn btn-sm btn-info float-right {{ $is_data === 1 ? '' : 'disabled' }}"><i
                            class="fas fa-save"></i> Export to Excel</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="grpo">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PO No</th>
                                <th>GRPO No</th>
                                <th>GRPO D</th>
                                <th>Vendor Code</th>
                                <th>Unit No</th>
                                <th>Item Code</th>
                                <th>UOM</th>
                                <th>Qty</th>
                                {{-- <th>Curr</th> --}}
                                <th>Amount</th>
                                <th>Project</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-upload">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> GRPO Upload</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('grpo.import_excel') }}" enctype="multipart/form-data" method="POST"
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
                        <button type="submit" class="btn btn-sm btn-primary" onclick="submitImport(event)">Upload</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div> <!-- /.modal-dialog -->
    </div> <!-- /.modal -->

    <div class="modal fade" id="modal-sync">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Sync GRPO from SAP</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('grpo.sync_from_sap') }}" method="POST" id="syncForm">
                    @csrf
                    <div class="modal-body">
                        <p>Sync GRPO data from SAP SQL Server. Default date range: First day of current year to today.</p>
                        <div class="form-group">
                            <label>Start Date (Optional)</label>
                            <input type="date" name="start_date" class="form-control" 
                                value="{{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}">
                            <small class="form-text text-muted">Leave empty to use first day of current year</small>
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
            // Show SweetAlert for success/error messages
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

            $("#grpo").DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('grpo.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'po_no'
                    },
                    {
                        data: 'grpo_no'
                    },
                    {
                        data: 'grpo_date'
                    },
                    {
                        data: 'vendor_code'
                    },
                    {
                        data: 'unit_no'
                    },
                    {
                        data: 'item_code'
                    },
                    {
                        data: 'uom'
                    },
                    {
                        data: 'qty'
                    },
                    // {data: 'grpo_currency'},
                    {
                        data: 'item_amount'
                    },
                    {
                        data: 'project_code'
                    },
                ],
                fixedHeader: true,
                columnDefs: [{
                    "targets": [8, 9],
                    "className": "text-right"
                }]
            })
        });

        function submitImport(event) {
            event.preventDefault();

            // Show loading state
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

            // Submit the form
            document.getElementById('importForm').submit();
        }

        function confirmTruncate() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete all GRPO records. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, truncate it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('grpo.truncate') }}';
                }
            });
        }

        function submitSync(event) {
            event.preventDefault();

            const form = document.getElementById('syncForm');
            const startDate = form.querySelector('input[name="start_date"]').value;
            const endDate = form.querySelector('input[name="end_date"]').value;

            // Close the modal first
            $('#modal-sync').modal('hide');

            // Show progress bar immediately
            let progress = 0;
            
            Swal.fire({
                title: 'Syncing from SAP...',
                html: `
                    <div style="text-align: left;">
                        <p style="margin-bottom: 10px;">Syncing data from SAP SQL Server...</p>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;"><strong>Date Range:</strong> ${startDate || 'First day of current year'} to ${endDate || 'Today'}</p>
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
                if (progress > 90) progress = 90; // Don't go to 100% until done
                
                Swal.update({
                    html: `
                        <div style="text-align: left;">
                            <p style="margin-bottom: 10px;">Syncing data from SAP SQL Server...</p>
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px;"><strong>Date Range:</strong> ${startDate || 'First day of current year'} to ${endDate || 'Today'}</p>
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

            // Prepare form data
            const formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');

            // Submit via AJAX
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
                
                // Complete progress bar
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
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Success!' : 'Error!',
                        text: data.message,
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
