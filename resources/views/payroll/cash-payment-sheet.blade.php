<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cash Payment Sheet</title>
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

        .payment-details {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
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

    <div class="subject">
        <h2>Cash Payment Sheet for {{ $month }}</h2>
    </div>

    @php
    $total = 0;
    foreach($payrolls as $payroll) {
    $total += $payroll->net_salary;
    }
    @endphp

    <div class="payment-details">
        <p><strong>Payment Instructions:</strong></p>
        <ul>
            <li>Please ensure all payments are made in person</li>
            <li>Each artisan must sign the payment receipt</li>
            <li>Keep all payment receipts for record keeping</li>
            <li>Total amount to be disbursed: Rs{{ number_format($total, 2) }}</li>
        </ul>
    </div>

    <table>
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Artisan Name</th>
                <th>Phone Number</th>
                <th>Amount(NPR)</th>
                <th>Signature</th>
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
                <td>{{ $payroll->phone_number }}</td>
                <td>Rs{{ number_format($payroll->net_salary, 2) }}</td>
                <td style="height: 50px;"></td>
            </tr>
            @php
            $total += $payroll->net_salary;
            @endphp
            @endforeach
            <tr>
                <td colspan="3" class="total">TOTAL</td>
                <td>Rs{{ number_format($total, 2) }}</td>
                <td></td>
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