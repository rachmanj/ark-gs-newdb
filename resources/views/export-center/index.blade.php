@extends('templates.main')

@section('title_page')
    <h1>Export Center</h1>
@endsection

@section('breadcrumb_title')
    Export Center
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Export Modules to Excel</h3>
                </div>
                <div class="card-body">
                    <div id="export-center-error" class="alert alert-danger d-none"></div>

                    <form id="export-center-form" action="{{ route('export-center.download') }}" method="GET">
                        <div class="form-group">
                            <label>Modules</label>
                            <div>
                                @foreach ($modules as $module)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="modules[]"
                                            id="module-{{ $module['code'] }}" value="{{ $module['code'] }}" checked>
                                        <label class="form-check-label" for="module-{{ $module['code'] }}">
                                            {{ $module['label'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_month">Start Month</label>
                                    <input type="month" class="form-control" id="start_month" name="start_month"
                                        value="{{ date('Y-m') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_month">End Month</label>
                                    <input type="month" class="form-control" id="end_month" name="end_month"
                                        value="{{ date('Y-m') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary" id="btn-last-month">Bulan
                                    Lalu</button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-this-month">Bulan
                                    Ini</button>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-excel mr-1"></i> Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            function pad(n) {
                return n < 10 ? '0' + n : '' + n;
            }

            function formatYearMonth(date) {
                return date.getFullYear() + '-' + pad(date.getMonth() + 1);
            }

            document.getElementById('btn-this-month').addEventListener('click', function() {
                var now = new Date();
                var value = formatYearMonth(now);
                document.getElementById('start_month').value = value;
                document.getElementById('end_month').value = value;
            });

            document.getElementById('btn-last-month').addEventListener('click', function() {
                var now = new Date();
                var lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                var value = formatYearMonth(lastMonth);
                document.getElementById('start_month').value = value;
                document.getElementById('end_month').value = value;
            });

            document.getElementById('export-center-form').addEventListener('submit', function(e) {
                var errorBox = document.getElementById('export-center-error');
                var checkedModules = document.querySelectorAll('input[name="modules[]"]:checked');
                var startMonth = document.getElementById('start_month').value;
                var endMonth = document.getElementById('end_month').value;

                var message = '';
                if (checkedModules.length === 0) {
                    message = 'Please select at least one module.';
                } else if (!startMonth || !endMonth) {
                    message = 'Please fill in both start month and end month.';
                }

                if (message) {
                    e.preventDefault();
                    errorBox.textContent = message;
                    errorBox.classList.remove('d-none');
                } else {
                    errorBox.classList.add('d-none');
                }
            });
        })();
    </script>
@endsection
