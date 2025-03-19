<div class="card card-info">
    <div class="card-header border-transparent">
        <h3 class="card-title"><b>REGULER</b> <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">PO Sent</th>
                        <th class="text-right border-0">Budget</th>
                        <th class="text-right border-0">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['reguler']['reguler_monthly'] as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="mr-2 text-{{ $item['percentage'] * 100 <= 100 ? 'success' : 'danger' }}">
                                        <i class="fas fa-circle fa-xs"></i>
                                    </span>
                                    <span>{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right">{{ number_format($item['sent_amount'] / 1000, 2) }}</td>
                            <td class="text-right">{{ number_format($item['budget'] / 1000, 2) }}</td>
                            <td class="text-right">
                                <span
                                    class="badge badge-{{ $item['percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($item['percentage'] * 100, 2) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold bg-light">
                        <td>Total</td>
                        <td class="text-right">{{ number_format($data['reguler']['sent_total'] / 1000, 2) }}</td>
                        <td class="text-right">{{ number_format($data['reguler']['budget_total'] / 1000, 2) }}</td>
                        <td class="text-right">
                            <span
                                class="badge badge-{{ $data['reguler']['percentage'] * 100 <= 100 ? 'success' : 'danger' }} badge-pill">
                                {{ number_format($data['reguler']['percentage'] * 100, 2) }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
