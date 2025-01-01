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
                        <tr>
                            <td>{{ $item['project'] }}</td>
                            <td class="text-right">
                                {{ number_format($item['incoming_qty'], 0) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['outgoing_qty'], 0) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['percentage'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th class="text-right">{{ number_format($npi_daily['total_incoming_qty'], 0) }}</th>
                        <th class="text-right">{{ number_format($npi_daily['total_outgoing_qty'], 0) }}</th>
                        <th class="text-right">{{ number_format($npi_daily['total_percentage'], 2) }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
