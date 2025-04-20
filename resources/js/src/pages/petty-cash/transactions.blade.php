<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #666;
        }

        .summary {
            margin-bottom: 20px;
            width: 100%;
        }

        .summary-box {
            float: left;
            width: 30%;
            margin-right: 3%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .summary-box.income {
            background-color: #e8f5e9;
        }

        .summary-box.expense {
            background-color: #ffebee;
        }

        .summary-box.balance {
            background-color: #e3f2fd;
        }

        .summary-box h3 {
            font-size: 14px;
            margin: 0 0 5px 0;
        }

        .summary-box p {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .filters {
            margin-bottom: 20px;
            clear: both;
            padding-top: 20px;
        }

        .filters table {
            width: 100%;
            border-collapse: collapse;
        }

        .filters table th,
        .filters table td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .filters table th {
            background-color: #f5f5f5;
            width: 25%;
        }

        .category-summary {
            margin-bottom: 20px;
        }

        .category-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .category-summary table th,
        .category-summary table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .category-summary table th {
            background-color: #f5f5f5;
        }

        .transactions {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions th,
        .transactions td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .transactions th {
            background-color: #f5f5f5;
        }

        .transactions .income {
            color: #2e7d32;
        }

        .transactions .expense {
            color: #c62828;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="summary clearfix">
        <div class="summary-box income">
            <h3>Total Income</h3>
            <p>{{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="summary-box expense">
            <h3>Total Expense</h3>
            <p>{{ number_format($totalExpense, 2) }}</p>
        </div>
        <div class="summary-box balance">
            <h3>Balance</h3>
            <p>{{ number_format($balance, 2) }}</p>
        </div>
    </div>

    <div class="filters">
        <h3>Report Filters</h3>
        <table>
            <tr>
                <th>Category</th>
                <td>{{ $filters['category'] }}</td>
                <th>Type</th>
                <td>{{ $filters['type'] }}</td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td>{{ $filters['start_date'] }}</td>
                <th>End Date</th>
                <td>{{ $filters['end_date'] }}</td>
            </tr>
        </table>
    </div>

    <div class="category-summary">
        <h3>Summary by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Count</th>
                    <th class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byCategory as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ ucfirst($data['type']) }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="text-right {{ $data['type'] }}">
                        @if($data['type'] == 'income')
                        {{ number_format($data['cash_in'], 2) }}
                        @else
                        {{ number_format($data['cash_out'], 2) }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="transactions">
        <h3>Transaction Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Particulars</th>
                    <th>Reference</th>
                    <th class="text-right">Cash In</th>
                    <th class="text-right">Cash Out</th>
                    <th class="text-right">VAT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M d, Y') }}</td>
                    <td>{{ $transaction->category->name }}</td>
                    <td>{{ $transaction->particulars }}</td>
                    <td>{{ $transaction->reference_no }}</td>
                    <td class="text-right income">{{ $transaction->cash_in > 0 ? number_format($transaction->cash_in, 2) : '' }}</td>
                    <td class="text-right expense">{{ $transaction->cash_out > 0 ? number_format($transaction->cash_out, 2) : '' }}</td>
                    <td class="text-right">{{ $transaction->vat_amount > 0 ? number_format($transaction->vat_amount, 2) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is an automatically generated report. For any questions, please contact the administrator.</p>
    </div>
</body>

</html>