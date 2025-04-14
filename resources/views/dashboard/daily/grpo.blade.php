<div class="card shadow-sm border-0 animate__animated animate__fadeIn animate__delay-1s card-table">
    <div class="card-header bg-gradient-success text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-exchange-alt mr-2"></i>PO Sent vs GRPO <small>(IDR 000)</small>
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
                        <th class="text-right border-0 py-2">GRPO</th>
                        <th class="text-right border-0 py-2" width="8%">%</th>
                        <th class="border-0 py-2" width="15%">Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grpo_daily['grpo_daily'] as $item)
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <span
                                            class="badge-dot badge-{{ $item['percentage'] * 100 >= 80 ? 'success' : 'danger' }}"></span>
                                    </div>
                                    <span class="font-weight-medium">{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right py-2">
                                <span class="text-muted">{{ number_format($item['po_sent_amount'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span
                                    class="font-weight-medium">{{ number_format($item['grpo_amount'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span
                                    class="badge badge-{{ $item['percentage'] * 100 >= 80 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($item['percentage'] * 100, 2) }}%
                                </span>
                            </td>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $item['percentage'] * 100 >= 80 ? 'success' : 'danger' }}"
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
                        <td class="text-right py-2">{{ number_format($grpo_daily['total_po_sent_amount'] / 1000, 2) }}
                        </td>
                        <td class="text-right py-2">{{ number_format($grpo_daily['total_grpo_amount'] / 1000, 2) }}
                        </td>
                        <td class="text-right py-2">
                            <span
                                class="badge badge-{{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'success' : 'danger' }} badge-pill">
                                {{ number_format($grpo_daily['total_percentage'] * 100, 2) }}%
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'success' : 'danger' }}"
                                        role="progressbar"
                                        style="width: {{ min($grpo_daily['total_percentage'] * 100, 100) }}%"
                                        aria-valuenow="{{ $grpo_daily['total_percentage'] * 100 }}" aria-valuemin="0"
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

<style>
    .badge-dot {
        width: 10px;
        height: 10px;
        display: inline-block;
        border-radius: 50%;
    }

    .badge-dot.badge-success {
        background-color: #28a745;
    }

    .badge-dot.badge-danger {
        background-color: #dc3545;
    }
</style>
