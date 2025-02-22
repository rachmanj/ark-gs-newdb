<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #2B5797;
            color: white;
        }

        .amount {
            text-align: right;
        }

        .unit-no {
            text-align: left;
        }

        .yearly-total {
            font-weight: bold;
            background-color: #E8F0FE;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
            color: #2B5797;
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h1>Summary Report ({{ date('d/m/Y') }})</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Unit No</th>
                @foreach ($months as $month)
                    <th>{{ $month['month'] }}</th>
                @endforeach
                <th class="yearly-total">Yearly Total</th>
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
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    <td class="amount yearly-total">
                        {{ $yearly_totals[$unitNo]['yearly_total'] ?? '0.00' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
