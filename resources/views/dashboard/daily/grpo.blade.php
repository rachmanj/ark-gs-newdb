<div class="card card-info">
    <div class="card-header border-transparent py-1">
        <h3 class="card-title">PO Sent vs GRPO <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Project</th>
                        <th class="text-right border-0">PO Sent</th>
                        <th class="text-right border-0">GRPO</th>
                        <th class="text-right border-0">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grpo_daily['grpo_daily'] as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="mr-2 text-{{ $item['percentage'] * 100 >= 80 ? 'success' : 'danger' }}">
                                        <i class="fas fa-circle fa-xs"></i>
                                    </span>
                                    <span>{{ $item['project'] }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                {{ number_format($item['po_sent_amount'] / 1000, 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['grpo_amount'] / 1000, 2) }}
                            </td>
                            <td class="text-right">
                                <span
                                    class="badge badge-{{ $item['percentage'] * 100 >= 80 ? 'success' : 'danger' }} badge-pill">
                                    {{ number_format($item['percentage'] * 100, 2) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-weight-bold bg-light">
                        <td>Total</td>
                        <td class="text-right">{{ number_format($grpo_daily['total_po_sent_amount'] / 1000, 2) }}</td>
                        <td class="text-right">{{ number_format($grpo_daily['total_grpo_amount'] / 1000, 2) }}</td>
                        <td class="text-right">
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
