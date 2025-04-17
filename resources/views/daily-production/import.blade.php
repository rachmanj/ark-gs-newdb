@extends('templates.main')

@section('title_page')
    <h1>Import Daily Production</h1>
@endsection

@section('breadcrumb_title')
    daily-production
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Import Daily Production Data</h3>
                    <div class="card-tools">
                        <a href="{{ route('daily-production.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
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

                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('daily-production.import-excel') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="file">Excel File</label>
                                    <div class="custom-file">
                                        <input type="file" name="file"
                                            class="custom-file-input @error('file') is-invalid @enderror" id="file"
                                            accept=".xlsx, .xls">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-upload"></i> Import
                                    </button>
                                    <a href="{{ route('daily-production.index') }}" class="btn btn-sm btn-default">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h5 class="card-title">Import Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Excel Format Requirements:</h6>
                                    <ul>
                                        <li>File must be in <code>.xlsx</code> or <code>.xls</code> format</li>
                                        <li>The first row should contain column headers</li>
                                        <li><strong>Date Format:</strong>
                                            <ul>
                                                <li>Use YYYY-MM-DD format (e.g., 2023-01-31)</li>
                                                <li>Or use DD/MM/YYYY format (e.g., 31/01/2023)</li>
                                                <li>Or use standard Excel date format</li>
                                                <li>Avoid using text-formatted dates</li>
                                            </ul>
                                        </li>
                                        <li>Required columns:
                                            <ul>
                                                <li><code>date</code> - Date of production</li>
                                                <li><code>project</code> - Project name</li>
                                            </ul>
                                        </li>
                                        <li>Optional columns:
                                            <ul>
                                                <li><code>day_shift</code> - General day shift value</li>
                                                <li><code>night_shift</code> - General night shift value</li>
                                                <li><code>limestone_day_shift</code> - Limestone day shift value</li>
                                                <li><code>limestone_swing_shift</code> - Limestone swing shift value</li>
                                                <li><code>limestone_night_shift</code> - Limestone night shift value</li>
                                                <li><code>shalestone_day_shift</code> - Shalestone day shift value</li>
                                                <li><code>shalestone_swing_shift</code> - Shalestone swing shift value</li>
                                                <li><code>shalestone_night_shift</code> - Shalestone night shift value</li>
                                            </ul>
                                        </li>
                                    </ul>

                                    <p class="mt-3">
                                        <a href="{{ route('daily-production.download-template') }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download Sample Excel
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Display selected filename in the custom file input
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endsection
