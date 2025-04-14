<div class="card shadow-sm border-0 animate__animated animate__fadeIn animate__delay-2s card-table">
    <div class="card-header bg-gradient-warning text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-lightbulb mr-2"></i>NPI
            </h3>
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive table-shadow">
            <table class="table table-hover table-striped table-compact mb-0">
                <thead>
                    <tr class="bg-light">
                        <th class="border-0 py-2">Project</th>
                        <th class="text-right border-0 py-2">In</th>
                        <th class="text-right border-0 py-2">Out</th>
                        <th class="text-right border-0 py-2" width="8%">Index</th>
                        <th class="border-0 py-2" width="15%">Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($npi_daily['npi'] as $item)
                        @php
                            if ($item['percentage'] < 1) {
                                $color = 'success';
                            } elseif ($item['percentage'] <= 1.1) {
                                $color = 'warning';
                            } else {
                                $color = 'danger';
                            }
                        @endphp
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <span class="badge-dot badge-{{ $color }}"></span>
                                    </div>
                                    <span class="font-weight-medium">{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right py-2">
                                <span class="text-muted">{{ number_format($item['incoming_qty'], 0) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span class="font-weight-medium">{{ number_format($item['outgoing_qty'], 0) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span class="badge badge-{{ $color }} badge-pill">
                                    {{ number_format($item['percentage'], 2) }}
                                </span>
                            </td>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                            style="width: {{ min($item['percentage'] * 100, 100) }}%"
                                            aria-valuenow="{{ $item['percentage'] * 100 }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @php
                        if ($npi_daily['total_percentage'] < 1) {
                            $totalColor = 'success';
                        } elseif ($npi_daily['total_percentage'] <= 1.1) {
                            $totalColor = 'warning';
                        } else {
                            $totalColor = 'danger';
                        }
                    @endphp
                    <tr class="font-weight-bold bg-light">
                        <td class="py-2">Total</td>
                        <td class="text-right py-2">{{ number_format($npi_daily['total_incoming_qty'], 0) }}</td>
                        <td class="text-right py-2">{{ number_format($npi_daily['total_outgoing_qty'], 0) }}</td>
                        <td class="text-right py-2">
                            <span class="badge badge-{{ $totalColor }} badge-pill">
                                {{ number_format($npi_daily['total_percentage'], 2) }}
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $totalColor }}" role="progressbar"
                                        style="width: {{ min($npi_daily['total_percentage'] * 100, 100) }}%"
                                        aria-valuenow="{{ $npi_daily['total_percentage'] * 100 }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
