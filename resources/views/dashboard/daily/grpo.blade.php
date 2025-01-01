<div class="card card-info">
    <div class="card-header border-transparent py-1">
        <h3 class="card-title">PO Sent vs GRPO <small>(IDR 000)</small></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-striped">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th class="text-right">PO Sent</th>
                        <th class="text-right">GRPO</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grpo_daily['grpo_daily'] as $item)
                        <tr>
                            <td>{{ $item['project'] }}</td>
                            <td class="text-right">
                                {{ number_format($item['po_sent_amount'] / 1000, 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['grpo_amount'] / 1000, 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($item['percentage'] * 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th class="text-right">{{ number_format($grpo_daily['total_po_sent_amount'] / 1000, 2) }}</th>
                        <th class="text-right">{{ number_format($grpo_daily['total_grpo_amount'] / 1000, 2) }}</th>
                        <th class="text-right">{{ number_format($grpo_daily['total_percentage'] * 100, 2) }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
