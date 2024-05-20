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
            <a href="#"><b>THIS MONTH </b></a> | 
            <a href="{{ route('powitheta.index_this_year') }}">This Year</a>
            
            @can('upload_data')
            <a href="{{ route('powitheta.truncate') }}" class="btn btn-sm btn-danger float-right {{ $is_data === 1 ? "" : "disabled" }}" onclick="return confirm('Are You sure You want to delete all records?')"><i class="fas fa-trash"></i> Truncate Table</a>
            <button class="btn btn-sm btn-success float-right mx-2" data-toggle="modal" data-target="#modal-upload_newdb" {{ $is_data === 1 ? "disabled" : "" }}><i class="fas fa-upload"></i> Upload New DB</button>
            {{-- <button class="btn btn-sm btn-success float-right mx-2" data-toggle="modal" data-target="#modal-upload_olddb" {{ $is_data === 1 ? "disabled" : "" }}><i class="fas fa-upload"></i> Upload Old DB</button> --}}
            @endcan

            <a href="{{ route('powitheta.export_this_month') }}" class="btn btn-sm btn-info float-right {{ $is_data === 1 ? "" : "disabled" }}"><i class="fas fa-save"></i> Export to Excel</a>
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
          <form action="{{ route('powitheta.import_excel') }}" enctype="multipart/form-data" method="POST">
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
@endsection

@section('styles')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('adminlte/plugins/datatables/css/datatables.min.css') }}"/>
@endsection

@section('scripts')
  <!-- DataTables  & Plugins -->
  <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('adminlte/plugins/datatables/datatables.min.js') }}"></script>

  <script>
    $(function () {
      $("#powitheta").DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('powitheta.data') }}',
        columns: [
          {data: 'DT_RowIndex', orderable: false, searchable: false},
          {data: 'po_no'},
          {data: 'po_delivery_date'},
          {data: 'project_code'},
          {data: 'unit_no'},
          {data: 'item_code'},
          {data: 'budget_type'},
          {data: 'item_amount'},
          {data: 'action'},
        ],
        fixedHeader: true,
        columnDefs: [
              {
                "targets": [7],
                "className": "text-right"
              }
            ]
      })
    });
  </script>

@endsection