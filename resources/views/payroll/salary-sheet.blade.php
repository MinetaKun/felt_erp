<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Salary Sheet - {{ $month }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Salary Sheet</h1>
        <h2>{{ $month }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Artisan Name</th>
                <th>Basic Salary</th>
                <th>OT Pay</th>
                <th>Order-based Wages</th>
                <th>Allowances</th>
                <th>Advance Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $payroll)
            <tr>
                <td>{{ $payroll->artisan->name }}</td>
                <td>{{ number_format($payroll->basic_salary, 2) }}</td>
                <td>{{ number_format($payroll->ot_pay, 2) }}</td>
                <td>{{ number_format($payroll->order_based_wages, 2) }}</td>
                <td>{{ number_format($payroll->allowances, 2) }}</td>
                <td>{{ number_format($payroll->advance_deductions, 2) }}</td>
                <td>{{ number_format($payroll->net_salary, 2) }}</td>
                <td>{{ ucfirst($payroll->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Total</td>
                <td>{{ number_format($payrolls->sum('basic_salary'), 2) }}</td>
                <td>{{ number_format($payrolls->sum('ot_pay'), 2) }}</td>
                <td>{{ number_format($payrolls->sum('order_based_wages'), 2) }}</td>
                <td>{{ number_format($payrolls->sum('allowances'), 2) }}</td>
                <td>{{ number_format($payrolls->sum('advance_deductions'), 2) }}</td>
                <td>{{ number_format($payrolls->sum('net_salary'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>