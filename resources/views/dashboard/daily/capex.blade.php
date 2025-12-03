<div class="card shadow-sm border-0 animate__animated animate__fadeIn animate__delay-3s card-table">
    <div class="card-header bg-gradient-danger text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-building mr-2"></i>CAPEX <small>(IDR 000)</small>
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
                        <th class="text-right border-0 py-2">PO Sent</th>
                        <th class="text-right border-0 py-2">Budget</th>
                        <th class="text-right border-0 py-2" width="8%">%</th>
                        <th class="border-0 py-2" width="15%">Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($capex_daily['capex'] as $item)
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <span
                                            class="badge-dot badge-{{ $item['percentage'] * 100 <= 100 ? 'success' : 'danger' }}"></span>
                                    </div>
                                    <span class="font-weight-medium">{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right py-2">
                                @if($item['sent_amount'] > 0)
                                    <a href="{{ route('dashboard.po.sent.details.page', [
                                        'project' => $item['project'],
                                        'year' => date('Y'),
                                        'month' => date('m'),
                                        'budget_type' => 'CPX'
                                    ]) }}" 
                                    class="text-primary font-weight-medium" 
                                    style="text-decoration: none;"
                                    data-toggle="tooltip" 
                                    title="Click to view details">
                                        {{ number_format($item['sent_amount'] / 1000, 2) }}
                                        <i class="fas fa-external-link-alt ml-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                @else
                                    <span class="text-muted">{{ number_format($item['sent_amount'] / 1000, 2) }}</span>
                                @endif
                            </td>
                            <td class="text-right py-2">
                                <span class="font-weight-medium">{{ number_format($item['budget'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span
                                    class="badge badge-{{ $item['percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($item['percentage'] * 100, 2) }}%
                                </span>
                            </td>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $item['percentage'] * 100 <= 100 ? 'success' : 'danger' }}"
                                            role="progressbar" style="width: {{ min($item['percentage'] * 100, 100) }}%"
                                            aria-valuenow="{{ $item['percentage'] * 100 }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold bg-light">
                        <td class="py-2">Total</td>
                        <td class="text-right py-2">
                            @if($capex_daily['sent_total'] > 0)
                                <a href="{{ route('dashboard.po.sent.details.page', [
                                    'project' => 'ALL',
                                    'year' => date('Y'),
                                    'month' => date('m'),
                                    'budget_type' => 'CPX'
                                ]) }}" 
                                class="text-primary" 
                                style="text-decoration: none;"
                                data-toggle="tooltip" 
                                title="Click to view all PO details">
                                    {{ number_format($capex_daily['sent_total'] / 1000, 2) }}
                                    <i class="fas fa-external-link-alt ml-1" style="font-size: 0.75rem;"></i>
                                </a>
                            @else
                                {{ number_format($capex_daily['sent_total'] / 1000, 2) }}
                            @endif
                        </td>
                        <td class="text-right py-2">{{ number_format($capex_daily['budget_total'] / 1000, 2) }}</td>
                        <td class="text-right py-2">
                            <span
                                class="badge badge-{{ $capex_daily['percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                {{ number_format($capex_daily['percentage'] * 100, 2) }}%
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $capex_daily['percentage'] * 100 <= 100 ? 'success' : 'danger' }}"
                                        role="progressbar"
                                        style="width: {{ min($capex_daily['percentage'] * 100, 100) }}%"
                                        aria-valuenow="{{ $capex_daily['percentage'] * 100 }}" aria-valuemin="0"
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
