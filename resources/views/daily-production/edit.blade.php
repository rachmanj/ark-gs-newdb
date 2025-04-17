@extends('templates.main')

@section('title_page')
    <h1>Edit Daily Production</h1>
@endsection

@section('breadcrumb_title')
    daily-production
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Daily Production</h3>
                    <div class="card-tools">
                        <a href="{{ route('daily-production.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('daily-production.update', $dailyProduction->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" name="date" id="date"
                                        class="form-control @error('date') is-invalid @enderror"
                                        value="{{ old('date', $dailyProduction->date->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project">Project</label>
                                    <input type="text" name="project" id="project"
                                        class="form-control @error('project') is-invalid @enderror"
                                        value="{{ old('project', $dailyProduction->project) }}" required>
                                    @error('project')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header bg-primary">
                                <h4 class="card-title">General Production</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="day_shift">Day Shift</label>
                                            <input type="number" name="day_shift" id="day_shift"
                                                class="form-control @error('day_shift') is-invalid @enderror"
                                                value="{{ old('day_shift', $dailyProduction->day_shift) }}">
                                            @error('day_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="night_shift">Night Shift</label>
                                            <input type="number" name="night_shift" id="night_shift"
                                                class="form-control @error('night_shift') is-invalid @enderror"
                                                value="{{ old('night_shift', $dailyProduction->night_shift) }}">
                                            @error('night_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header bg-info">
                                <h4 class="card-title">MTD Values</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_ff_actual">MTD FF Actual</label>
                                            <input type="number" name="mtd_ff_actual" id="mtd_ff_actual"
                                                class="form-control @error('mtd_ff_actual') is-invalid @enderror"
                                                value="{{ old('mtd_ff_actual', $dailyProduction->mtd_ff_actual) }}">
                                            @error('mtd_ff_actual')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_ff_plan">MTD FF Plan</label>
                                            <input type="number" name="mtd_ff_plan" id="mtd_ff_plan"
                                                class="form-control @error('mtd_ff_plan') is-invalid @enderror"
                                                value="{{ old('mtd_ff_plan', $dailyProduction->mtd_ff_plan) }}">
                                            @error('mtd_ff_plan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_rain_actual">MTD Rain Actual</label>
                                            <input type="number" name="mtd_rain_actual" id="mtd_rain_actual"
                                                class="form-control @error('mtd_rain_actual') is-invalid @enderror"
                                                value="{{ old('mtd_rain_actual', $dailyProduction->mtd_rain_actual) }}">
                                            @error('mtd_rain_actual')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_rain_plan">MTD Rain Plan</label>
                                            <input type="number" name="mtd_rain_plan" id="mtd_rain_plan"
                                                class="form-control @error('mtd_rain_plan') is-invalid @enderror"
                                                value="{{ old('mtd_rain_plan', $dailyProduction->mtd_rain_plan) }}">
                                            @error('mtd_rain_plan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_haul_dist_actual">MTD Haul Dist Actual</label>
                                            <input type="number" name="mtd_haul_dist_actual" id="mtd_haul_dist_actual"
                                                class="form-control @error('mtd_haul_dist_actual') is-invalid @enderror"
                                                value="{{ old('mtd_haul_dist_actual', $dailyProduction->mtd_haul_dist_actual) }}">
                                            @error('mtd_haul_dist_actual')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mtd_haul_dist_plan">MTD Haul Dist Plan</label>
                                            <input type="number" name="mtd_haul_dist_plan" id="mtd_haul_dist_plan"
                                                class="form-control @error('mtd_haul_dist_plan') is-invalid @enderror"
                                                value="{{ old('mtd_haul_dist_plan', $dailyProduction->mtd_haul_dist_plan) }}">
                                            @error('mtd_haul_dist_plan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header bg-success">
                                <h4 class="card-title">Limestone Production</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="limestone_day_shift">Day Shift</label>
                                            <input type="number" name="limestone_day_shift" id="limestone_day_shift"
                                                class="form-control @error('limestone_day_shift') is-invalid @enderror"
                                                value="{{ old('limestone_day_shift', $dailyProduction->limestone_day_shift) }}">
                                            @error('limestone_day_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="limestone_swing_shift">Swing Shift</label>
                                            <input type="number" name="limestone_swing_shift" id="limestone_swing_shift"
                                                class="form-control @error('limestone_swing_shift') is-invalid @enderror"
                                                value="{{ old('limestone_swing_shift', $dailyProduction->limestone_swing_shift) }}">
                                            @error('limestone_swing_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="limestone_night_shift">Night Shift</label>
                                            <input type="number" name="limestone_night_shift" id="limestone_night_shift"
                                                class="form-control @error('limestone_night_shift') is-invalid @enderror"
                                                value="{{ old('limestone_night_shift', $dailyProduction->limestone_night_shift) }}">
                                            @error('limestone_night_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header bg-warning">
                                <h4 class="card-title">Shalestone Production</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="shalestone_day_shift">Day Shift</label>
                                            <input type="number" name="shalestone_day_shift" id="shalestone_day_shift"
                                                class="form-control @error('shalestone_day_shift') is-invalid @enderror"
                                                value="{{ old('shalestone_day_shift', $dailyProduction->shalestone_day_shift) }}">
                                            @error('shalestone_day_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="shalestone_swing_shift">Swing Shift</label>
                                            <input type="number" name="shalestone_swing_shift"
                                                id="shalestone_swing_shift"
                                                class="form-control @error('shalestone_swing_shift') is-invalid @enderror"
                                                value="{{ old('shalestone_swing_shift', $dailyProduction->shalestone_swing_shift) }}">
                                            @error('shalestone_swing_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="shalestone_night_shift">Night Shift</label>
                                            <input type="number" name="shalestone_night_shift"
                                                id="shalestone_night_shift"
                                                class="form-control @error('shalestone_night_shift') is-invalid @enderror"
                                                value="{{ old('shalestone_night_shift', $dailyProduction->shalestone_night_shift) }}">
                                            @error('shalestone_night_shift')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('daily-production.index') }}" class="btn btn-sm btn-default">
                                <i class="fas fa-times-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
