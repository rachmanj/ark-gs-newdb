<div class="card card-warning">
    <div class="card-header border-transparent">
        <h3 class="card-title">
            <b>NPI Index</b>
            <button class="btn btn-xs btn-link p-0 ml-1" data-toggle="popover" data-trigger="hover" data-placement="top"
                data-content="NPI (Net Production Index) = Incoming Qty / Outgoing Qty. Lower index values indicate better production efficiency - less material input needed for same output.">
                <i class="fas fa-info-circle text-info"></i>
            </button>
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead class="bg-gradient-warning">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">In <small>(units)</small></th>
                        <th class="text-right border-0">Out <small>(units)</small></th>
                        <th class="text-center border-0" style="min-width: 150px;">Index</th>
                        <th class="text-center border-0">Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['npi']['npi'] as $item)
                        @php
                            $index = $item['percentage'];
                            // Lower index = better efficiency (less material waste, more efficient production)
                            $status =
                                $index <= 0.85
                                    ? ['success', 'star', 'Excellent']
                                    : ($index <= 1.0
                                        ? ['info', 'thumbs-up', 'Good']
                                        : ($index <= 1.2
                                            ? ['warning', 'exclamation-triangle', 'Average']
                                            : ($index <= 1.5
                                                ? ['danger', 'arrow-up', 'Below Target']
                                                : ['danger', 'times-circle', 'Critical'])));

                            $normalizedIndex = min(max($index * 100, 0), 200);
                            $barWidth = min($normalizedIndex, 100);
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
                                <span class="badge badge-success badge-pill">
                                    {{ number_format($item['incoming_qty'], 0) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="badge badge-danger badge-pill">
                                    {{ number_format($item['outgoing_qty'], 0) }}
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;" data-toggle="tooltip"
                                    title="Index: {{ number_format($index, 2) }} (Lower is better - Excellent: ≤0.85)">
                                    @if ($index <= 1.0)
                                        <div class="progress-bar bg-{{ $status[0] }}" role="progressbar"
                                            style="width: {{ $barWidth }}%">
                                            <strong>{{ number_format($index, 2) }}</strong>
                                        </div>
                                    @else
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 50%">
                                        </div>
                                        <div class="progress-bar bg-{{ $status[0] }} progress-bar-striped"
                                            role="progressbar" style="width: {{ min(($index - 1) * 50, 50) }}%">
                                            <strong>{{ number_format($index, 2) }}</strong>
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
                    @php
                        $totalIndex = $data['npi']['total_percentage'];
                        // Lower total index = better overall efficiency
                        $totalStatus =
                            $totalIndex <= 0.85
                                ? ['success', 'Excellent']
                                : ($totalIndex <= 1.0
                                    ? ['info', 'Good']
                                    : ($totalIndex <= 1.2
                                        ? ['warning', 'Average']
                                        : ['danger', 'Below Target']));
                    @endphp
                    <tr class="font-weight-bold bg-gradient-light">
                        <td>
                            <i class="fas fa-calculator mr-2"></i>
                            <strong>Total</strong>
                        </td>
                        <td class="text-right">
                            <strong class="text-success">
                                {{ number_format($data['npi']['total_incoming_qty'], 0) }}
                            </strong>
                        </td>
                        <td class="text-right">
                            <strong class="text-danger">
                                {{ number_format($data['npi']['total_outgoing_qty'], 0) }}
                            </strong>
                        </td>
                        <td>
                            <div class="progress" style="height: 24px;">
                                @if ($totalIndex <= 1.0)
                                    <div class="progress-bar bg-{{ $totalStatus[0] }}" role="progressbar"
                                        style="width: {{ min($totalIndex * 100, 100) }}%">
                                        <strong>{{ number_format($totalIndex, 2) }}</strong>
                                    </div>
                                @else
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 50%">
                                    </div>
                                    <div class="progress-bar bg-{{ $totalStatus[0] }} progress-bar-striped"
                                        role="progressbar" style="width: {{ min(($totalIndex - 1) * 50, 50) }}%">
                                        <strong>{{ number_format($totalIndex, 2) }}</strong>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $totalStatus[0] }} badge-lg">
                                <i class="fas fa-flag"></i>
                                {{ $totalStatus[1] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
