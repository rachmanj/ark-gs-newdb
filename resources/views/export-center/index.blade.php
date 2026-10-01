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
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="export-tab-ark-link" data-toggle="tab" href="#tab-ark"
                                        role="tab" aria-controls="tab-ark" aria-selected="true">
                                        Data ARK-GS
                                        <span class="text-muted" id="export-tab-ark-count">(0)</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="export-tab-sap-link" data-toggle="tab" href="#tab-sap"
                                        role="tab" aria-controls="tab-sap" aria-selected="false">
                                        Laporan SAP (PRC and Logistik)
                                        <span class="text-muted" id="export-tab-sap-count">(0)</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tab-ark" role="tabpanel"
                                    aria-labelledby="export-tab-ark-link">
                                    @foreach ($arkModules as $module)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="modules[]"
                                                id="module-{{ $module['code'] }}" value="{{ $module['code'] }}">
                                            <label class="form-check-label" for="module-{{ $module['code'] }}">
                                                {{ $module['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="tab-pane fade" id="tab-sap" role="tabpanel"
                                    aria-labelledby="export-tab-sap-link">
                                    <p class="text-muted small mb-2">
                                        Laporan SAP ditarik langsung dari SAP saat tombol unduh ditekan.
                                    </p>
                                    <ul class="list-group">
                                        @foreach ($sapModules as $module)
                                            <li class="list-group-item">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="modules[]"
                                                        id="module-{{ $module['code'] }}" value="{{ $module['code'] }}">
                                                    <label class="form-check-label" for="module-{{ $module['code'] }}">
                                                        {{ $module['label'] }}
                                                        @if ($module['code'] === 'sap10')
                                                            <small class="text-muted d-block">Menampilkan posisi stok terkini; tidak terpengaruh bulan yang dipilih.</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
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
                                <button type="button" class="btn btn-outline-secondary" id="btn-last-month">Last
                                    Month</button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-this-month">This
                                    Month</button>
                                <small class="form-text text-muted">Tip: exporting one to three months at a time is fastest.</small>
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

            function updateTabModuleCounts() {
                var arkPane = document.getElementById('tab-ark');
                var sapPane = document.getElementById('tab-sap');
                var arkCount = arkPane.querySelectorAll('input[name="modules[]"]:checked').length;
                var sapCount = sapPane.querySelectorAll('input[name="modules[]"]:checked').length;
                document.getElementById('export-tab-ark-count').textContent = '(' + arkCount + ')';
                document.getElementById('export-tab-sap-count').textContent = '(' + sapCount + ')';
            }

            document.querySelectorAll('input[name="modules[]"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', updateTabModuleCounts);
            });
            updateTabModuleCounts();

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
