<div class="card shadow-sm border-0 animate__animated animate__fadeIn card-table">
    <div class="card-header bg-gradient-primary text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-chart-line mr-2"></i>Reguler & GRPO Comparison <small>(IDR 000)</small>
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
                        <th class="text-right border-0 py-2" width="8%">Budget %</th>
                        <th class="text-right border-0 py-2">GRPO</th>
                        <th class="text-right border-0 py-2" width="8%">GRPO %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Create an array to track all unique projects
                        $projects = [];
                        $combinedData = [];

                        // Collect data from reguler
                        foreach ($reguler_daily['reguler'] as $item) {
                            $projects[$item['project']] = true;
                            if (!isset($combinedData[$item['project']])) {
                                $combinedData[$item['project']] = [
                                    'po_sent' => $item['sent_amount'],
                                    'budget' => $item['budget'],
                                    'budget_percentage' => $item['percentage'],
                                    'grpo' => 0,
                                    'grpo_percentage' => 0,
                                ];
                            }
                        }

                        // Collect data from GRPO
                        foreach ($grpo_daily['grpo_daily'] as $item) {
                            $projects[$item['project']] = true;
                            if (!isset($combinedData[$item['project']])) {
                                $combinedData[$item['project']] = [
                                    'po_sent' => $item['po_sent_amount'],
                                    'budget' => 0,
                                    'budget_percentage' => 0,
                                    'grpo' => $item['grpo_amount'],
                                    'grpo_percentage' => $item['percentage'],
                                ];
                            } else {
                                $combinedData[$item['project']]['grpo'] = $item['grpo_amount'];
                                $combinedData[$item['project']]['grpo_percentage'] = $item['percentage'];

                                // Make sure PO Sent values are consistent
                                if ($combinedData[$item['project']]['po_sent'] != $item['po_sent_amount']) {
                                    $combinedData[$item['project']]['po_sent'] = $item['po_sent_amount'];
                                }
                            }
                        }
                    @endphp

                    @foreach ($combinedData as $project => $data)
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex mr-2">
                                        <span
                                            class="badge-dot badge-{{ $data['budget_percentage'] * 100 <= 100 ? 'success' : 'danger' }} mr-1"></span>
                                        <span
                                            class="badge-dot badge-{{ $data['grpo_percentage'] * 100 >= 80 ? 'success' : 'danger' }}"></span>
                                    </div>
                                    <span class="font-weight-medium">{{ $project }}</span>
                                </div>
                            </td>
                            <td class="text-right py-2">
                                <span class="text-muted">{{ number_format($data['po_sent'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span class="font-weight-medium">{{ number_format($data['budget'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span
                                    class="badge badge-{{ $data['budget_percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($data['budget_percentage'] * 100, 2) }}%
                                </span>
                            </td>
                            <td class="text-right py-2">
                                <span class="font-weight-medium">{{ number_format($data['grpo'] / 1000, 2) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span
                                    class="badge badge-{{ $data['grpo_percentage'] * 100 >= 80 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($data['grpo_percentage'] * 100, 2) }}%
                                </span>
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
                        <td class="text-right py-2">{{ number_format($grpo_daily['total_grpo_amount'] / 1000, 2) }}
                        </td>
                        <td class="text-right py-2">
                            <span
                                class="badge badge-{{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'success' : 'danger' }} badge-pill">
                                {{ number_format($grpo_daily['total_percentage'] * 100, 2) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .badge-dot {
        width: 8px;
        height: 8px;
        display: inline-block;
        border-radius: 50%;
    }

    .badge-dot.badge-success {
        background-color: #28a745;
    }

    .badge-dot.badge-danger {
        background-color: #dc3545;
    }

    .badge-dot.badge-warning {
        background-color: #ffc107;
    }
</style>
