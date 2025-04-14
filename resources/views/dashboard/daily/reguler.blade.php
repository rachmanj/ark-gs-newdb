<div class="card shadow-sm border-0 animate__animated animate__fadeIn card-table">
    <div class="card-header bg-gradient-info text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-file-invoice-dollar mr-2"></i>REGULER <small>(IDR 000)</small>
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
                    @foreach ($reguler_daily['reguler'] as $item)
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
                                <span class="text-muted">{{ number_format($item['sent_amount'] / 1000, 2) }}</span>
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
                        <td class="text-right py-2">{{ number_format($reguler_daily['sent_total'] / 1000, 2) }}</td>
                        <td class="text-right py-2">{{ number_format($reguler_daily['budget_total'] / 1000, 2) }}</td>
                        <td class="text-right py-2">
                            <span
                                class="badge badge-{{ $reguler_daily['percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                {{ number_format($reguler_daily['percentage'] * 100, 2) }}%
                            </span>
                        </td>
                        <td class="py-2">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $reguler_daily['percentage'] * 100 <= 100 ? 'success' : 'danger' }}"
                                        role="progressbar"
                                        style="width: {{ min($reguler_daily['percentage'] * 100, 100) }}%"
                                        aria-valuenow="{{ $reguler_daily['percentage'] * 100 }}" aria-valuemin="0"
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

    .animate__animated {
        animation-duration: 0.8s;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
