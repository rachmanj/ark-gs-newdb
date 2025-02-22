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
            <div class="row mb-3">
                <div class="col-12">
                    <a href="{{ route('summary.export') }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel mr-2"></i>Export to Excel
                    </a>
                    <a href="{{ route('summary.export.pdf') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    @include('dashboard.summary-by-unit._table')
                </div>
            </div>

        </div>
    </div>
@endsection

@section('styles')
    <style>
        .table-wrapper {
            position: relative;
            max-height: 500px;
            overflow: auto;
            margin: 0;
        }

        .sticky-table {
            position: relative;
            border-collapse: separate;
            border-spacing: 0;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f4f4f4;
            z-index: 1;
        }

        .sticky-col {
            position: sticky;
            background-color: white;
            border-right: 1px solid #dee2e6 !important;
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
            background-color: #f2f2f2;
        }

        .table-striped tbody tr:nth-of-type(even) .sticky-col {
            background-color: #fff;
        }
    </style>
@endsection
