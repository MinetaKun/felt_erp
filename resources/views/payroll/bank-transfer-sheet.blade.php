<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bank Transfer Sheet</title>
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

        .date {
            text-align: right;
            margin-bottom: 20px;
        }

        .to-section {
            margin-bottom: 20px;
        }

        .subject {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
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

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">MAATA BANSTHALI HANDICRAFTS</div>
        <div class="address">KATHMANDU, BANASTHALI - 16</div>
    </div>

    <div class="date">
        Date: {{ \Carbon\Carbon::now()->format('jS F, Y') }}
    </div>

    <div class="to-section">
        To,<br>
        Global IME Bank Pvt. Ltd.<br>
        Kharibot Branch Banasthali,<br>
        Kathmandu, Nepal
    </div>

    <div class="subject">
        Sub: Request to deposit salary into salary account.
    </div>

    <div>
        This is about the above subject matter. We kindly request you to deposit salary for the month of {{ $month }}, as per the following details, and debit from our Account No. 1001010008136
    </div>

    @php
    $total = 0;
    foreach($payrolls as $payroll) {
    $total += $payroll->net_salary;
    }
    @endphp

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
                <td>{{ $payroll->artisan_name }}</td>
                <td>{{ $payroll->bank_account_number }}</td>
                <td>Rs{{ number_format($payroll->net_salary, 2) }}</td>
            </tr>
            @php
            $total += $payroll->net_salary;
            @endphp
            @endforeach
            <tr>
                <td colspan="3" class="total">TOTAL</td>
                <td>Rs{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        THANKING YOU,<br>
        Yours truly,<br>
        For Mata Banasthali Handicraft<br><br><br>
        Dev Kumari Basnet Gharti Chhetri(Proprietor)
    </div>

    <div class="footer">
        Banasthali - 16, Kharibot, Kathmandu, Nepal.<br>
        Number: 9818781999, 9840633297<br>
        E-mail: maatabanasthalihandicraft09@gmail.com
    </div>
</body>

</html>