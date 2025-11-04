<div class="card card-secondary">
    <div class="card-header border-transparent">
        <h3 class="card-title"><b>CAPEX</b> <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead class="bg-gradient-secondary">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">PO Sent</th>
                        <th class="text-right border-0">Budget</th>
                        <th class="text-right border-0">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['capex']['capex'] as $item)
                        <tr>
                            <td><strong>{{ $item['project'] }}</strong></td>
                            <td class="text-right">{{ number_format($item['sent_amount'] / 1000, 2) }}</td>
                            <td class="text-right">{{ number_format($item['budget'] / 1000, 2) }}</td>
                            <td class="text-right">
                                <span class="badge badge-secondary">
                                    {{ number_format($item['percentage'] * 100, 2) }}%
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
                            <strong>{{ number_format($data['capex']['sent_total'] / 1000, 2) }}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($data['capex']['budget_total'] / 1000, 2) }}</strong>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-secondary badge-lg">
                                {{ number_format($data['capex']['percentage'] * 100, 2) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

