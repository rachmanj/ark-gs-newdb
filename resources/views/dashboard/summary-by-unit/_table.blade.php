<div class="p-0">
    <h6 class="px-2 pt-2 text-center font-weight-bold">{{ date('Y') }} Summary</h6>
    <div class="table-wrapper">
        <table class="table table-sm table-bordered table-striped table-hover sticky-table">
            <thead>
                <tr>
                    <th class="sticky-col sticky-header" style="min-width: 50px; left: 0; z-index: 2;">
                        <small>Unit No</small>
                        <i class="fas fa-sort-alpha-down fa-xs text-secondary ml-1" title="Sorted by Unit Number"></i>
                    </th>
                    @foreach ($unitSummary['months'] as $month)
                        <th class="text-right sticky-header" style="min-width: 60px;">
                            <small>{{ $month['month'] }}</small>
                        </th>
                    @endforeach
                    <th class="text-right bg-light sticky-header" style="min-width: 80px;">
                        <small>Total</small>
                    </th>
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
                    <tr data-unit-no="{{ $unitNo }}">
                        <td class="sticky-col" style="left: 0;">
                            <small class="font-weight-medium">{{ $unitNo }}</small>
                        </td>
                        @foreach ($unitSummary['months'] as $month)
                            <td class="text-right p-1">
                                @php
                                    $unitData = collect($month['units'])->firstWhere('unit_no', $unitNo);
                                @endphp
                                @if ($unitData)
                                    <small>
                                        <span class="d-block font-weight-bold">{{ $unitData['total_amount'] }}</span>
                                        <span class="text-muted">{{ $unitData['po_count'] }} PO</span>
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-right p-1 bg-light">
                            @if (isset($unitSummary['yearly_totals'][$unitNo]))
                                <small>
                                    <span
                                        class="d-block font-weight-bold">{{ $unitSummary['yearly_totals'][$unitNo]['yearly_total'] }}</span>
                                    <span
                                        class="text-muted">{{ $unitSummary['yearly_totals'][$unitNo]['total_po_count'] }}
                                        PO</span>
                                </small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
