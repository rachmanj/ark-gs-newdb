<div class="card card-info">
    <div class="card-header border-transparent py-1">
        <h3 class="card-title">NPI</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th class="text-right">In</th>
                        <th class="text-right">Out</th>
                        <th class="text-right">index</th>
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
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="mr-2 text-{{ $color }}">
                                        <i class="fas fa-circle fa-xs"></i>
                                    </span>
                                    <span>{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                {{ number_format($item['incoming_qty'], 0) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['outgoing_qty'], 0) }}
                            </td>
                            <td class="text-right">
                                <span class="badge badge-{{ $color }} badge-pill">
                                    {{ number_format($item['percentage'], 2) }}
                                </span>
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
                        <th>Total</th>
                        <th class="text-right">{{ number_format($npi_daily['total_incoming_qty'], 0) }}</th>
                        <th class="text-right">{{ number_format($npi_daily['total_outgoing_qty'], 0) }}</th>
                        <th class="text-right">
                            <span class="badge badge-{{ $totalColor }} badge-pill">
                                {{ number_format($npi_daily['total_percentage'], 2) }}
                            </span>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
