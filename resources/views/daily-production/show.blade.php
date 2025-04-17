@extends('templates.main')

@section('title_page')
    <h1>Daily Production Details</h1>
@endsection

@section('breadcrumb_title')
    daily-production
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Production Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('daily-production.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('daily-production.edit', $dailyProduction->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>General Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Date</th>
                                    <td>{{ $dailyProduction->date->format('d-M-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Project</th>
                                    <td>{{ $dailyProduction->project }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h5 class="card-title">General Production</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Day Shift</th>
                                            <td>{{ $dailyProduction->day_shift ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Night Shift</th>
                                            <td>{{ $dailyProduction->night_shift ?? 0 }}</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <th>Total</th>
                                            <td>{{ ($dailyProduction->day_shift ?? 0) + ($dailyProduction->night_shift ?? 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h5 class="card-title">Limestone Production</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Day Shift</th>
                                            <td>{{ $dailyProduction->limestone_day_shift ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Swing Shift</th>
                                            <td>{{ $dailyProduction->limestone_swing_shift ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Night Shift</th>
                                            <td>{{ $dailyProduction->limestone_night_shift ?? 0 }}</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <th>Total</th>
                                            <td>
                                                {{ ($dailyProduction->limestone_day_shift ?? 0) +
                                                    ($dailyProduction->limestone_swing_shift ?? 0) +
                                                    ($dailyProduction->limestone_night_shift ?? 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h5 class="card-title">Shalestone Production</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Day Shift</th>
                                            <td>{{ $dailyProduction->shalestone_day_shift ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Swing Shift</th>
                                            <td>{{ $dailyProduction->shalestone_swing_shift ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>Night Shift</th>
                                            <td>{{ $dailyProduction->shalestone_night_shift ?? 0 }}</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <th>Total</th>
                                            <td>
                                                {{ ($dailyProduction->shalestone_day_shift ?? 0) +
                                                    ($dailyProduction->shalestone_swing_shift ?? 0) +
                                                    ($dailyProduction->shalestone_night_shift ?? 0) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Grand Total:
                                        {{ ($dailyProduction->day_shift ?? 0) +
                                            ($dailyProduction->night_shift ?? 0) +
                                            ($dailyProduction->limestone_day_shift ?? 0) +
                                            ($dailyProduction->limestone_swing_shift ?? 0) +
                                            ($dailyProduction->limestone_night_shift ?? 0) +
                                            ($dailyProduction->shalestone_day_shift ?? 0) +
                                            ($dailyProduction->shalestone_swing_shift ?? 0) +
                                            ($dailyProduction->shalestone_night_shift ?? 0) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form action="{{ route('daily-production.destroy', $dailyProduction->id) }}" method="POST"
                            class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
