@extends('templates.main')

@section('title_page')
    <h1>Daily Production</h1>
@endsection

@section('breadcrumb_title')
    daily-production
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

                    <a href="{{ route('daily-production.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus-circle"></i> Add New
                    </a>
                    <a href="{{ route('daily-production.import') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>

                    {{-- @can('upload_data')
                        <a href="{{ route('daily-production.truncate') }}"
                            class="btn btn-sm btn-danger float-right {{ $is_data === 1 ? '' : 'disabled' }}"
                            onclick="return confirm('Are You sure You want to delete all records?')">
                            <i class="fas fa-trash"></i> Truncate Table
                        </a>
                    @endcan --}}

                    <a href="{{ route('daily-production.export-this-month') }}"
                        class="btn btn-sm btn-info {{ $is_data === 1 ? '' : 'disabled' }}">
                        <i class="fas fa-download"></i> Export This Month
                    </a>
                    <a href="{{ route('daily-production.export-this-year') }}"
                        class="btn btn-sm btn-info {{ $is_data === 1 ? '' : 'disabled' }}">
                        <i class="fas fa-download"></i> Export This Year
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="daily-production-table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Day Shift</th>
                                    <th>Night Shift</th>
                                    <th>Total</th>
                                    <th>MTD FF Actual</th>
                                    <th>MTD FF Plan</th>
                                    <th>MTD Rain Actual</th>
                                    <th>MTD Rain Plan</th>
                                    <th>MTD Haul Dist Actual</th>
                                    <th>MTD Haul Dist Plan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
            $('#daily-production-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('daily-production.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'day_shift',
                        name: 'day_shift'
                    },
                    {
                        data: 'night_shift',
                        name: 'night_shift'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let dayShift = data.day_shift || 0;
                            let nightShift = data.night_shift || 0;
                            return dayShift + nightShift;
                        }
                    },
                    {
                        data: 'mtd_ff_actual',
                        name: 'mtd_ff_actual'
                    },
                    {
                        data: 'mtd_ff_plan',
                        name: 'mtd_ff_plan'
                    },
                    {
                        data: 'mtd_rain_actual',
                        name: 'mtd_rain_actual'
                    },
                    {
                        data: 'mtd_rain_plan',
                        name: 'mtd_rain_plan'
                    },
                    {
                        data: 'mtd_haul_dist_actual',
                        name: 'mtd_haul_dist_actual'
                    },
                    {
                        data: 'mtd_haul_dist_plan',
                        name: 'mtd_haul_dist_plan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                fixedHeader: true,
                columnDefs: [{
                    "targets": [3, 4, 5, 6, 7, 8, 9, 10, 11],
                    "className": "text-right"
                }]
            });
        });
    </script>
@endsection
