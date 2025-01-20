<!DOCTYPE html>
<html>

<head>
    <title>Unit Summary Table</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .amount {
            text-align: right;
        }

        .unit-no {
            text-align: left;
        }

        .no-data {
            color: #999;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Unit No</th>
                @foreach ($months as $month)
                    <th>{{ $month['month'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($unitNumbers as $index => $unitNo)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="unit-no">{{ $unitNo }}</td>
                    @foreach ($months as $month)
                        <td class="amount">
                            @php
                                $unitData = collect($month['units'])->firstWhere('unit_no', $unitNo);
                            @endphp
                            @if ($unitData)
                                {{ $unitData['total_amount'] }}
                                <small>({{ $unitData['po_count'] }})</small>
                            @else
                                <span class="no-data">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
