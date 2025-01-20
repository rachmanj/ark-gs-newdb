<div class="card card-info">
    <div class="card-header pl-2 py-1">
        <h6>Summary by Unit <small>(IDR 000)</small></h6>
    </div>
    <div class="card-body p-0">
        <h6 class="px-2 pt-2 text-center">{{ date('Y') }}</h6>
        <div class="table-wrapper">
            <table class="table table-sm table-bordered table-striped sticky-table">
                <thead>
                    <tr>
                        <th class="sticky-col" style="min-width: 50px; left: 0; z-index: 2;"><small>Unit No</small></th>
                        @foreach ($unitSummary['months'] as $month)
                            <th class="text-right sticky-header" style="min-width: 60px;">
                                <small>{{ $month['month'] }}</small>
                            </th>
                        @endforeach
                        <th class="text-right bg-light sticky-header" style="min-width: 80px;"><small>Total</small></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Get unique unit numbers across all months
                        $unitNumbers = collect();
                        foreach ($unitSummary['months'] as $month) {
                            foreach ($month['units'] as $unit) {
                                $unitNumbers->push($unit['unit_no']);
                            }
                        }
                        $unitNumbers = $unitNumbers->unique()->sort()->values();
                    @endphp

                    @foreach ($unitNumbers as $index => $unitNo)
                        <tr>
                            <td class="sticky-col" style="left: 0;"><small>{{ $unitNo }}</small></td>
                            @foreach ($unitSummary['months'] as $month)
                                <td class="text-right p-1">
                                    @php
                                        $unitData = collect($month['units'])->firstWhere('unit_no', $unitNo);
                                    @endphp
                                    @if ($unitData)
                                        <small>
                                            {{ $unitData['total_amount'] }}<br>
                                            <span>{{ $unitData['po_count'] }} PO</span>
                                        </small>
                                    @else
                                        <small>-</small>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-right p-1 bg-light">
                                @if (isset($unitSummary['yearly_totals'][$unitNo]))
                                    <small>
                                        <strong>{{ $unitSummary['yearly_totals'][$unitNo]['yearly_total'] }}</strong><br>
                                        <span>{{ $unitSummary['yearly_totals'][$unitNo]['total_po_count'] }} PO</span>
                                    </small>
                                @else
                                    <small>-</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
