@extends('templates.main')

@section('title_page')
    POWITHETA scheduled sync
@endsection

@section('breadcrumb_title')
    admin / powitheta schedule
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Automated SAP sync</h3>
                </div>
                <form action="{{ route('admin.powitheta-schedule.update') }}" method="post" id="scheduleForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if (Session::has('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="text-muted">
                            The server runs <code>php artisan schedule:run</code> every minute (cron or Windows Task
                            Scheduler). Scheduled runs execute <code>powitheta:refresh-from-sap --scheduled</code> (clear
                            <code>powithetas</code>, then SAP import + convert). <strong>Manual “Sync from SAP” on PO With
                                ETA</strong> uses the modal only and does <strong>not</strong> use these date settings.
                        </p>

                        <div class="form-group">
                            <input type="hidden" name="enabled" value="0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="enabled" name="enabled"
                                    value="1"
                                    {{ old('enabled', $enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="enabled">Enable scheduled sync</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="staging_modules_enabled" value="0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="staging_modules_enabled"
                                    name="staging_modules_enabled" value="1"
                                    {{ old('staging_modules_enabled', $staging_modules_enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="staging_modules_enabled">Also run GRPO, MIGI,
                                    Incoming SAP sync at the same times</label>
                            </div>
                            <p class="text-muted small mb-0">Uses <code>staging-modules:sync-from-sap --scheduled</code>:
                                Jan 1 → today, <strong>insert only</strong> with duplicate lines skipped (same PO/GRPO/item
                                or same posting/doc/item keys). Requires master “Enable scheduled sync” above.</p>
                        </div>

                        <div class="form-group">
                            <label for="sync_time_1">First run (server local time)</label>
                            <input type="time" class="form-control @error('sync_time_1') is-invalid @enderror"
                                id="sync_time_1" name="sync_time_1" value="{{ old('sync_time_1', $sync_time_1) }}"
                                required>
                            @error('sync_time_1')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sync_time_2">Second run (server local time)</label>
                            <input type="time" class="form-control @error('sync_time_2') is-invalid @enderror"
                                id="sync_time_2" name="sync_time_2" value="{{ old('sync_time_2', $sync_time_2) }}"
                                required>
                            @error('sync_time_2')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3">Scheduled SAP date range</h5>
                        <p class="text-muted small">Applied to <strong>automatic</strong> runs only. Dates are still
                            clamped to the current calendar year (Jan 1 … today) by the SAP service.</p>

                        <div class="form-group">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="sap_date_mode_cy" name="sap_date_mode"
                                    value="current_year"
                                    {{ old('sap_date_mode', $sap_date_mode) === 'current_year' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sap_date_mode_cy">Current year (default: Jan 1
                                    → today)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="sap_date_mode_custom"
                                    name="sap_date_mode" value="custom"
                                    {{ old('sap_date_mode', $sap_date_mode) === 'custom' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sap_date_mode_custom">Custom range (within
                                    current year)</label>
                            </div>
                            @error('sap_date_mode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="sap_custom_dates" class="border rounded p-3 mb-2 bg-light"
                            style="{{ old('sap_date_mode', $sap_date_mode) === 'custom' ? '' : 'display:none;' }}">
                            <div class="form-group mb-2">
                                <label for="sap_custom_start">Start date</label>
                                <input type="date" class="form-control @error('sap_custom_start') is-invalid @enderror"
                                    id="sap_custom_start" name="sap_custom_start"
                                    value="{{ old('sap_custom_start', $sap_custom_start) }}">
                                @error('sap_custom_start')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label for="sap_custom_end">End date</label>
                                <input type="date" class="form-control @error('sap_custom_end') is-invalid @enderror"
                                    id="sap_custom_end" name="sap_custom_end"
                                    value="{{ old('sap_custom_end', $sap_custom_end) }}">
                                @error('sap_custom_end')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-info mt-3">
                <div class="card-header">
                    <h3 class="card-title">GRPO / MIGI / Incoming — recent scheduled runs</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Run</th>
                                <th>Module</th>
                                <th>Status</th>
                                <th>SAP range</th>
                                <th>Imported</th>
                                <th>Skipped dupes</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stagingHistories as $runId => $rows)
                                @foreach ($rows as $h)
                                    <tr>
                                        @if ($loop->first)
                                            <td rowspan="{{ $rows->count() }}" class="text-muted small">
                                                {{ \Illuminate\Support\Str::limit($runId, 13) }}</td>
                                        @endif
                                        <td>{{ $h->module }}</td>
                                        <td>
                                            @if ($h->status === 'success')
                                                <span class="badge badge-success">success</span>
                                            @elseif($h->status === 'failed')
                                                <span class="badge badge-danger">failed</span>
                                            @elseif($h->status === 'running')
                                                <span class="badge badge-warning">running</span>
                                            @else
                                                {{ $h->status }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($h->sap_date_start && $h->sap_date_end)
                                                {{ $h->sap_date_start->format('Y-m-d') }}
                                                → {{ $h->sap_date_end->format('Y-m-d') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $h->imported_count ?? '—' }}</td>
                                        <td>{{ $h->skipped_duplicate_count ?? '—' }}</td>
                                        <td class="text-truncate" style="max-width:220px;" title="{{ $h->message }}">
                                            {{ \Illuminate\Support\Str::limit($h->message ?? '', 80) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No GRPO/MIGI/Incoming scheduled history
                                        yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-secondary mt-3">
                <div class="card-header">
                    <h3 class="card-title">PO With ETA — recent sync history</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Started</th>
                                <th>Trigger</th>
                                <th>Status</th>
                                <th>SAP range</th>
                                <th>Imported</th>
                                <th>User</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($histories as $h)
                                <tr>
                                    <td>{{ $h->started_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $h->trigger }}</td>
                                    <td>
                                        @if ($h->status === 'success')
                                            <span class="badge badge-success">success</span>
                                        @elseif($h->status === 'failed')
                                            <span class="badge badge-danger">failed</span>
                                        @elseif($h->status === 'running')
                                            <span class="badge badge-warning">running</span>
                                        @else
                                            {{ $h->status }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($h->sap_date_start && $h->sap_date_end)
                                            {{ $h->sap_date_start->format('Y-m-d') }}
                                            → {{ $h->sap_date_end->format('Y-m-d') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $h->imported_count ?? '—' }}</td>
                                    <td>{{ $h->user->name ?? '—' }}</td>
                                    <td class="text-truncate" style="max-width:220px;" title="{{ $h->message }}">
                                        {{ \Illuminate\Support\Str::limit($h->message ?? '', 80) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            function toggleCustom() {
                const custom = $('#sap_date_mode_custom').is(':checked');
                $('#sap_custom_dates').toggle(custom);
                if (custom) {
                    $('#sap_custom_start, #sap_custom_end').prop('required', true);
                } else {
                    $('#sap_custom_start, #sap_custom_end').prop('required', false);
                }
            }
            $('input[name="sap_date_mode"]').on('change', toggleCustom);
            toggleCustom();
        });
    </script>
@endsection
