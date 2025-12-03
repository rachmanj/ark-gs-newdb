<div class="card shadow-sm border-0 animate__animated animate__fadeIn card-table">
    <div class="card-header bg-gradient-primary text-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-table mr-2"></i>Comprehensive Dashboard <small>(Budget & GRPO in IDR 000, NPI in actual
                    units)</small>
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
                        <th class="text-right border-0 py-2" width="6%">Budget %</th>
                        <th class="text-right border-0 py-2">GRPO</th>
                        <th class="text-right border-0 py-2" width="6%">GRPO %</th>
                        <th class="text-right border-0 py-2">NPI In <small class="d-block text-muted">(units)</small>
                        </th>
                        <th class="text-right border-0 py-2">NPI Out <small class="d-block text-muted">(units)</small>
                        </th>
                        <th class="text-right border-0 py-2" width="6%">Index</th>
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
                                    'npi_in' => 0,
                                    'npi_out' => 0,
                                    'npi_percentage' => 0,
                                    'npi_color' => 'success',
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
                                    'npi_in' => 0,
                                    'npi_out' => 0,
                                    'npi_percentage' => 0,
                                    'npi_color' => 'success',
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

                        // Collect data from NPI
                        foreach ($npi_daily['npi'] as $item) {
                            $projects[$item['project']] = true;

                            // Determine NPI color
                            if ($item['percentage'] < 1) {
                                $color = 'success';
                            } elseif ($item['percentage'] <= 1.1) {
                                $color = 'warning';
                            } else {
                                $color = 'danger';
                            }

                            if (!isset($combinedData[$item['project']])) {
                                $combinedData[$item['project']] = [
                                    'po_sent' => 0,
                                    'budget' => 0,
                                    'budget_percentage' => 0,
                                    'grpo' => 0,
                                    'grpo_percentage' => 0,
                                    'npi_in' => $item['incoming_qty'],
                                    'npi_out' => $item['outgoing_qty'],
                                    'npi_percentage' => $item['percentage'],
                                    'npi_color' => $color,
                                ];
                            } else {
                                $combinedData[$item['project']]['npi_in'] = $item['incoming_qty'];
                                $combinedData[$item['project']]['npi_out'] = $item['outgoing_qty'];
                                $combinedData[$item['project']]['npi_percentage'] = $item['percentage'];
                                $combinedData[$item['project']]['npi_color'] = $color;
                            }
                        }
                    @endphp

                    @foreach ($combinedData as $project => $data)
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex mr-2">
                                        <span
                                            class="badge-dot badge-{{ $data['budget_percentage'] * 100 <= 100 ? 'success' : 'danger' }} mr-1"
                                            title="Budget"></span>
                                        <span
                                            class="badge-dot badge-{{ $data['grpo_percentage'] * 100 >= 80 ? 'success' : 'danger' }} mr-1"
                                            title="GRPO"></span>
                                        <span class="badge-dot badge-{{ $data['npi_color'] }}" title="NPI"></span>
                                    </div>
                                    <span class="font-weight-medium">{{ $project }}</span>
                                </div>
                            </td>
                            <td class="text-right py-2">
                                @if($data['po_sent'] > 0)
                                    <a href="{{ route('dashboard.po.sent.details.page', [
                                        'project' => $project,
                                        'year' => date('Y'),
                                        'month' => date('m'),
                                        'budget_type' => 'REG'
                                    ]) }}" 
                                    class="text-primary font-weight-medium" 
                                    style="text-decoration: none;"
                                    data-toggle="tooltip" 
                                    title="Click to view PO details">
                                        {{ number_format($data['po_sent'] / 1000, 2) }}
                                        <i class="fas fa-external-link-alt ml-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                @else
                                    <span class="text-muted">{{ number_format($data['po_sent'] / 1000, 2) }}</span>
                                @endif
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
                            <td class="text-right py-2">
                                <span class="text-muted">{{ number_format($data['npi_in'], 0) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span class="font-weight-medium">{{ number_format($data['npi_out'], 0) }}</span>
                            </td>
                            <td class="text-right py-2">
                                <span class="badge badge-{{ $data['npi_color'] }} badge-pill">
                                    {{ number_format($data['npi_percentage'], 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    @php
                        if ($npi_daily['total_percentage'] < 1) {
                            $npiTotalColor = 'success';
                        } elseif ($npi_daily['total_percentage'] <= 1.1) {
                            $npiTotalColor = 'warning';
                        } else {
                            $npiTotalColor = 'danger';
                        }
                    @endphp

                    <tr class="font-weight-bold bg-light">
                        <td class="py-2">Total</td>
                        <td class="text-right py-2">
                            @if($reguler_daily['sent_total'] > 0)
                                <a href="{{ route('dashboard.po.sent.details.page', [
                                    'project' => 'ALL',
                                    'year' => date('Y'),
                                    'month' => date('m'),
                                    'budget_type' => 'REG'
                                ]) }}" 
                                class="text-primary" 
                                style="text-decoration: none;"
                                data-toggle="tooltip" 
                                title="Click to view all PO details">
                                    {{ number_format($reguler_daily['sent_total'] / 1000, 2) }}
                                    <i class="fas fa-external-link-alt ml-1" style="font-size: 0.75rem;"></i>
                                </a>
                            @else
                                {{ number_format($reguler_daily['sent_total'] / 1000, 2) }}
                            @endif
                        </td>
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
                        <td class="text-right py-2">{{ number_format($npi_daily['total_incoming_qty'], 0) }}</td>
                        <td class="text-right py-2">{{ number_format($npi_daily['total_outgoing_qty'], 0) }}</td>
                        <td class="text-right py-2">
                            <span class="badge badge-{{ $npiTotalColor }} badge-pill">
                                {{ number_format($npi_daily['total_percentage'], 2) }}
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
        width: 7px;
        height: 7px;
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
