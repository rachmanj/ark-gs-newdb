@extends('templates.main')

@section('title_page')
    <h1>Dashboard <small>(This Month)</small></h1>
    Report Date: {{ $report_date }}
@endsection

@section('breadcrumb_title')
    dashboard / daily
@endsection

@section('content')
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                @include('dashboard.daily.row1')
            </div>

            {{-- <hr> --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <a href="{{ route('dashboard.summary-by-unit') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-chart-bar mr-1"></i> View Summary by Unit
                    </a>
                    <a href="{{ route('dashboard.search.po') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-chart-bar mr-1"></i> Search PO
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    {{-- @include('dashboard.daily.budget') --}}
                    @include('dashboard.daily.reguler')
                </div>

                <div class="col-6">
                    @include('dashboard.daily.grpo')
                </div>

            </div>

            <hr>

            <div class="row">
                <div class="col-6">
                    @include('dashboard.daily.npi')
                </div>

                <div class="col-6">
                    @include('dashboard.daily.capex')
                </div>
            </div>

        </div>
    </div>
@endsection
