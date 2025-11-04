<div class="card card-primary">
    <div class="card-header border-transparent">
        <h3 class="card-title"><b>PO Sent vs GRPO</b> <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead class="bg-gradient-primary">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">PO Sent</th>
                        <th class="text-right border-0">GRPO</th>
                        <th class="text-center border-0" style="min-width: 200px;">Completion Rate</th>
                        <th class="text-center border-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['grpo']['grpo_yearly'] as $item)
                        @php
                            $percentage = $item['percentage'] * 100;
                            $status =
                                $percentage >= 95
                                    ? ['success', 'check-circle', 'Excellent']
                                    : ($percentage >= 80
                                        ? ['info', 'thumbs-up', 'Good']
                                        : ($percentage >= 60
                                            ? ['warning', 'exclamation-triangle', 'Attention']
                                            : ['danger', 'times-circle', 'Critical']));
                            $progressWidth = min($percentage, 100);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2 text-{{ $status[0] }}">
                                        <i class="fas fa-{{ $status[1] }}"></i>
                                    </span>
                                    <strong>{{ $item['project'] }}</strong>
                                </div>
                            </td>
                            <td class="text-right">
                                {{ number_format($item['po_sent_amount'] / 1000, 2) }}
                            </td>
                            <td class="text-right">
                                <strong class="text-{{ $status[0] }}">
                                    {{ number_format($item['grpo_amount'] / 1000, 2) }}
                                </strong>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;" data-toggle="tooltip"
                                    title="GRPO: {{ number_format($percentage, 1) }}% of PO Sent">
                                    <div class="progress-bar bg-{{ $status[0] }}" role="progressbar"
                                        style="width: {{ $progressWidth }}%">
                                        <strong>{{ number_format($percentage, 1) }}%</strong>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $status[0] }}">
                                    <i class="fas fa-{{ $status[1] }}"></i>
                                    {{ $status[2] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold bg-gradient-light">
                        <td>
                            <i class="fas fa-calculator mr-2"></i>
                            <strong>Total</strong>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($data['grpo']['total_po_sent_amount'] / 1000, 2) }}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($data['grpo']['total_grpo_amount'] / 1000, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $totalPercentage = $data['grpo']['total_percentage'] * 100;
                                $totalGrpoStatus =
                                    $totalPercentage >= 95
                                        ? 'success'
                                        : ($totalPercentage >= 80
                                            ? 'info'
                                            : ($totalPercentage >= 60
                                                ? 'warning'
                                                : 'danger'));
                            @endphp
                            <div class="progress" style="height: 24px;">
                                <div class="progress-bar bg-{{ $totalGrpoStatus }}" role="progressbar"
                                    style="width: {{ min($totalPercentage, 100) }}%">
                                    <strong>{{ number_format($totalPercentage, 1) }}%</strong>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $overallGrpoStatus =
                                    $totalPercentage >= 95
                                        ? ['success', 'Excellent']
                                        : ($totalPercentage >= 80
                                            ? ['info', 'Good']
                                            : ($totalPercentage >= 60
                                                ? ['warning', 'Needs Attention']
                                                : ['danger', 'Critical']));
                            @endphp
                            <span class="badge badge-{{ $overallGrpoStatus[0] }} badge-lg">
                                <i class="fas fa-flag"></i>
                                {{ $overallGrpoStatus[1] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

