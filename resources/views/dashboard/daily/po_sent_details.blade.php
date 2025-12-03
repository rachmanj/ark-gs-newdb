@extends('templates.main')

@section('title_page')
    <h1>PO Sent Details</h1>
@endsection

@section('breadcrumb_title')
    <a href="{{ route('dashboard.daily.index') }}">Dashboard</a> / PO Sent Details
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
    .table thead th {
        vertical-align: middle;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,.125);
    }
    .badge-pill {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .details-control {
        cursor: pointer;
    }
    .details-control i {
        transition: all 0.3s ease;
    }
    .details-control i:hover {
        transform: scale(1.2);
    }
    tr.shown {
        background-color: #f8f9fa;
    }
    table.dataTable tbody tr.shown > td.details-control {
        background-color: #e9ecef;
    }
    .child-table {
        background-color: #ffffff;
        padding: 15px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice mr-2"></i>
                    <strong>{{ $projectDisplay }}</strong> - {{ $monthName }} {{ $year }} 
                    @if($budgetType)
                        <span class="badge badge-light ml-2">{{ $budgetType }}</span>
                    @endif
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" id="exportExcel">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="po-table" class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="3%"></th>
                                <th width="3%">No</th>
                                <th>PO Number</th>
                                <th>Create Date</th>
                                <th>Delivery Date</th>
                                <th>Vendor</th>
                                <th>Unit No</th>
                                <th class="text-center">Items</th>
                                <th class="text-right">Total Amount</th>
                                <th>ETA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="8" class="text-right">Total:</th>
                                <th class="text-right"><span id="total-amount">0</span></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

<script>
function formatItemsTable(items) {
    var html = '<div class="child-table p-3 bg-light">';
    html += '<h6 class="mb-3"><i class="fas fa-list mr-2"></i>Line Items (' + items.length + ')</h6>';
    html += '<table class="table table-sm table-bordered table-hover mb-0 bg-white">';
    html += '<thead class="thead-light">';
    html += '<tr>';
    html += '<th width="5%">No</th>';
    html += '<th>Item Code</th>';
    html += '<th>Description</th>';
    html += '<th class="text-center">Qty</th>';
    html += '<th class="text-center">UOM</th>';
    html += '<th class="text-right">Unit Price</th>';
    html += '<th class="text-right">Item Amount</th>';
    html += '<th>PR No</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    var subtotal = 0;
    items.forEach(function(item, index) {
        subtotal += parseFloat(item.item_amount);
        html += '<tr>';
        html += '<td>' + (index + 1) + '</td>';
        html += '<td><code>' + item.item_code + '</code></td>';
        html += '<td>' + item.description + '</td>';
        html += '<td class="text-center">' + item.qty + '</td>';
        html += '<td class="text-center">' + item.uom + '</td>';
        html += '<td class="text-right">' + parseFloat(item.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
        html += '<td class="text-right font-weight-bold">' + parseFloat(item.item_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
        html += '<td>' + (item.pr_no || '-') + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '<tfoot class="font-weight-bold bg-light">';
    html += '<tr>';
    html += '<td colspan="6" class="text-right">Subtotal:</td>';
    html += '<td class="text-right">' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
    html += '<td></td>';
    html += '</tr>';
    html += '</tfoot>';
    html += '</table>';
    html += '</div>';
    return html;
}

$(document).ready(function() {
    var table = $('#po-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("dashboard.po.sent.details") }}',
            data: {
                project: '{{ $project }}',
                year: '{{ $year }}',
                month: '{{ $month }}',
                budget_type: '{{ $budgetType }}'
            }
        },
        columns: [
            {
                className: 'details-control text-center',
                orderable: false,
                data: null,
                defaultContent: '<i class="fas fa-plus-circle text-primary" style="cursor: pointer;"></i>',
                width: '30px'
            },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'po_no', name: 'po_no' },
            { data: 'formatted_create_date', name: 'create_date' },
            { data: 'formatted_delivery_date', name: 'po_delivery_date' },
            { data: 'vendor_name', name: 'vendor_name' },
            { data: 'unit_no', name: 'unit_no' },
            { data: 'item_count', name: 'item_count', className: 'text-center' },
            { data: 'formatted_total_amount', name: 'total_amount', className: 'text-right' },
            { data: 'formatted_eta', name: 'po_eta' },
            { data: 'po_delivery_status', name: 'po_delivery_status' }
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        drawCallback: function(settings) {
            var api = this.api();
            var total = 0;
            
            api.column(8, { search: 'applied' }).data().each(function(value) {
                var numValue = parseFloat(value.replace(/,/g, ''));
                if (!isNaN(numValue)) {
                    total += numValue;
                }
            });
            
            $('#total-amount').html(total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
    });

    $('#po-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var icon = $(this).find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
        } else {
            var itemsJson = row.data().items_detail;
            var decodedJson = $('<textarea/>').html(itemsJson).text();
            var items = JSON.parse(decodedJson);
            row.child(formatItemsTable(items)).show();
            tr.addClass('shown');
            icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
        }
    });

    $('#exportExcel').on('click', function() {
        var params = new URLSearchParams({
            project: '{{ $project }}',
            year: '{{ $year }}',
            month: '{{ $month }}',
            budget_type: '{{ $budgetType }}'
        });
        
        window.location.href = '{{ route("dashboard.po.sent.details") }}?' + params.toString() + '&export=excel';
    });
});
</script>
@endsection
