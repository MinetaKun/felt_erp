<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Artisan Advances Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }

        .amount {
            text-align: right;
        }

        .date {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Artisan Advances Report</h1>
        <p>Period: {{ date('d M Y', strtotime($start_date)) }} to {{ date('d M Y', strtotime($end_date)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">S.N.</th>
                <th width="30%">Artisan Name</th>
                <th width="20%" class="amount">Amount</th>
                <th width="20%" class="date">Date</th>
                <th width="25%">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($advances as $index => $advance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $advance->artisan->name }}</td>
                <td class="amount">Rs. {{ number_format($advance->amount, 2) }}</td>
                <td class="date">{{ date('d M Y', strtotime($advance->date)) }}</td>
                <td>{{ $advance->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">No advances found for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($advances->count() > 0)
    <div class="total">
        Total Advances: Rs. {{ number_format($total_amount, 2) }}
    </div>
    @endif

    <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>
</body>

</html>