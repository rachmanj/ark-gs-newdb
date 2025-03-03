@extends('templates.main')

@section('title_page')
    <h1>MIGI (MI and GI)</h1>
@endsection

@section('breadcrumb_title')
    migi
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
                    <a href="#"><b>THIS MONTH </b></a> |
                    <a href="{{ route('migi.index_this_year') }}">This Year</a>

                    @can('upload_data')
                        <a href="{{ route('migi.truncate') }}"
                            class="btn btn-sm btn-danger float-right {{ $is_data === 1 ? '' : 'disabled' }}"
                            onclick="return confirm('Are You sure You want to delete all records?')"><i
                                class="fas fa-trash"></i> Truncate Table</a>
                        <button class="btn btn-sm btn-success float-right mx-2" data-toggle="modal" data-target="#modal-upload"
                            {{ $is_data === 1 ? 'disabled' : '' }}><i class="fas fa-upload"></i> Upload</button>
                    @endcan

                    <a href="{{ route('migi.export_this_month') }}"
                        class="btn btn-sm btn-info float-right {{ $is_data === 1 ? '' : 'disabled' }}"><i
                            class="fas fa-save"></i> Export to Excel</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="migi">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Doc No</th>
                                <th>Doc Type</th>
                                <th>Post D</th>
                                <th>Project</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Uom</th>
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
                    <h4 class="modal-title"> MIGI Upload</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('migi.import_excel') }}" enctype="multipart/form-data" method="POST"
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
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
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
            $("#migi").DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('migi.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'doc_no'
                    },
                    {
                        data: 'doc_type'
                    },
                    {
                        data: 'posting_date'
                    },
                    {
                        data: 'project_code'
                    },
                    {
                        data: 'item_code'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'uom'
                    },
                ],
                fixedHeader: true,
                columnDefs: [{
                    "targets": [6],
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
    </script>
@endsection
