<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .address {
            font-size: 14px;
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
            background-color: #f2f2f2;
        }

        .total {
            text-align: right;
            font-weight: bold;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">MAATA BANSTHALI HANDICRAFTS</div>
        <div class="address">KATHMANDU, BANASTHALI - 16</div>
    </div>

    <p>Dear Sir/Madam,</p>

    <p>This is regarding the salary payment for the month of {{ $month }}. Please find below the details of the salary payments to be made to our employees:</p>

    <table>
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Account Holder Name</th>
                <th>Account No.</th>
                <th>Amount(NPR)</th>
            </tr>
        </thead>
        <tbody>
            @php
            $total = 0;
            @endphp
            @foreach($payrolls as $index => $payroll)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $payroll['name'] }}</td>
                <td>{{ $payroll['bank_account_number'] }}</td>
                <td>Rs{{ number_format($payroll['net_salary'], 2) }}</td>
            </tr>
            @php
            $total += $payroll['net_salary'];
            @endphp
            @endforeach
            <tr>
                <td colspan="3" class="total">TOTAL</td>
                <td>Rs{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p>Please process these payments and debit the total amount of Rs{{ number_format($total, 2) }} from our Account No. 1001010008136.</p>

    <div class="signature">
        THANKING YOU,<br>
        Yours truly,<br>
        For Mata Banasthali Handicraft<br><br><br>
        Dev Kumari Basnet Gharti Chhetri(Proprietor)
    </div>

    <div class="footer">
        <p>Note: Please find attached the detailed PDF and CSV files for your reference.</p>
    </div>
</body>

</html>