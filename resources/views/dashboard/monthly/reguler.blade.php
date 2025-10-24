<div class="card card-info">
    <div class="card-header border-transparent">
        <h3 class="card-title"><b>REGULER</b> <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead class="bg-gradient-info">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">PO Sent</th>
                        <th class="text-right border-0">Budget</th>
                        <th class="text-center border-0" style="min-width: 200px;">Performance</th>
                        <th class="text-center border-0">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['reguler']['reguler_monthly'] as $item)
                        @php
                            $percentage = $item['percentage'] * 100;
                            $status =
                                $percentage > 95 && $percentage <= 105
                                    ? ['success', 'check-circle', 'Excellent']
                                    : ($percentage > 80 && $percentage <= 95
                                        ? ['info', 'info-circle', 'Good']
                                        : ($percentage > 105 && $percentage <= 150
                                            ? ['warning', 'exclamation-triangle', 'Over Budget']
                                            : ($percentage > 150
                                                ? ['danger', 'exclamation-circle', 'Critical']
                                                : ['secondary', 'minus-circle', 'Under Budget'])));
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
                                <strong>{{ number_format($item['sent_amount'] / 1000, 2) }}</strong>
                            </td>
                            <td class="text-right">
                                {{ number_format($item['budget'] / 1000, 2) }}
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;" data-toggle="tooltip"
                                    title="PO Sent: {{ number_format($percentage, 1) }}% of Budget">
                                    <div class="progress-bar bg-{{ $status[0] }}" role="progressbar"
                                        style="width: {{ $progressWidth }}%" aria-valuenow="{{ $percentage }}"
                                        aria-valuemin="0" aria-valuemax="100">
                                        <strong>{{ number_format($percentage, 1) }}%</strong>
                                    </div>
                                    @if ($percentage > 100)
                                        <div class="progress-bar bg-{{ $status[0] }} progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: {{ min($percentage - 100, 100) }}%">
                                        </div>
                                    @endif
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
                        <td class="text-right text-primary">
                            <strong>{{ number_format($data['reguler']['sent_total'] / 1000, 2) }}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($data['reguler']['budget_total'] / 1000, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $totalPercentage = $data['reguler']['percentage'] * 100;
                                $totalStatus =
                                    $totalPercentage > 95 && $totalPercentage <= 105
                                        ? 'success'
                                        : ($totalPercentage > 105
                                            ? 'warning'
                                            : 'info');
                                $totalProgressWidth = min($totalPercentage, 100);
                            @endphp
                            <div class="progress" style="height: 24px;">
                                <div class="progress-bar bg-{{ $totalStatus }}" role="progressbar"
                                    style="width: {{ $totalProgressWidth }}%">
                                    <strong>{{ number_format($totalPercentage, 1) }}%</strong>
                                </div>
                                @if ($totalPercentage > 100)
                                    <div class="progress-bar bg-{{ $totalStatus }} progress-bar-striped"
                                        role="progressbar" style="width: {{ min($totalPercentage - 100, 100) }}%">
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $overallStatus =
                                    $totalPercentage > 150
                                        ? ['danger', 'Critical']
                                        : ($totalPercentage > 105
                                            ? ['warning', 'Over Budget']
                                            : ($totalPercentage > 95
                                                ? ['success', 'On Track']
                                                : ['info', 'Under Budget']));
                            @endphp
                            <span class="badge badge-{{ $overallStatus[0] }} badge-lg">
                                <i class="fas fa-flag"></i>
                                {{ $overallStatus[1] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
