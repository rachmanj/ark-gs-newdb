@extends('templates.main')

@section('title_page')
    <h1>View Production Plan</h1>
@endsection

@section('breadcrumb_title')
    view production plan
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <a href="{{ route('production-plan.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('production-plan.edit', $productionPlan->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 20%">ID</th>
                                    <td>{{ $productionPlan->id }}</td>
                                </tr>
                                <tr>
                                    <th>Period</th>
                                    <td>{{ sprintf('%02d-%d', $productionPlan->month, $productionPlan->year) }}</td>
                                </tr>
                                <tr>
                                    <th>Product</th>
                                    <td>{{ ucfirst($productionPlan->product) }}</td>
                                </tr>
                                <tr>
                                    <th>UOM</th>
                                    <td>{{ strtoupper($productionPlan->uom) }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ number_format($productionPlan->qty, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Project</th>
                                    <td>{{ $productionPlan->project_name }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $productionPlan->created_at->format('d F Y, H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $productionPlan->updated_at->format('d F Y, H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
