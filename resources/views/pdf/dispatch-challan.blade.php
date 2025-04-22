<!DOCTYPE html>
<html>

<head>
    <title>Dispatch Challan #{{ $assignment->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .header h1 {
            margin-bottom: 5px;
            color: #333;
        }

        .header p {
            margin: 2px 0;
            color: #666;
        }

        .challan-info {
            margin-bottom: 20px;
        }

        .challan-info table {
            width: 100%;
        }

        .challan-info td {
            padding: 5px;
        }

        .challan-info .label {
            font-weight: bold;
            width: 150px;
        }

        .product-details {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DISPATCH CHALLAN</h1>
        <p>Challan No: CH-{{ $assignment->id }}-{{ date('Ymd') }}</p>
        <p>Date: {{ date('d/m/Y', strtotime($assignment->dispatched_at)) }}</p>
    </div>

    <div class="challan-info">
        <table>
            <tr>
                <td class="label">Order ID:</td>
                <td>{{ $assignment->order->order_id }}</td>
                <td class="label">Dispatch Date:</td>
                <td>{{ date('d/m/Y', strtotime($assignment->dispatch_date)) }}</td>
            </tr>
            <tr>
                <td class="label">Client Name:</td>
                <td>{{ $assignment->order->client_name }}</td>
                <td class="label">Dispatch Method:</td>
                <td>{{ ucfirst($assignment->dispatch_method) }}</td>
            </tr>
            <!-- <tr>
                <td class="label">Artisan Name:</td>
                <td>{{ $assignment->artisan->name }}</td>
                <td class="label">Artisan ID:</td>
                <td>{{ $assignment->artisan->id }}</td>
            </tr> -->
        </table>
    </div>

    <div class="product-details">
        <h3>Product Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Size</th>
                    <th>Wool Color</th>
                    <th>Assigned Qty</th>
                    <th>Approved Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $assignment->order->product_name }}</td>
                    <td>{{ $assignment->order->size }}</td>
                    <td>{{ $assignment->order->wool_color }}</td>
                    <td>{{ $assignment->assigned_quantity }}</td>
                    <td>{{ $assignment->approved_quantity }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="notes">
        <h3>Notes</h3>
        <p>{{ $assignment->dispatch_notes ?? 'No specific notes for this dispatch.' }}</p>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <p>Dispatched By</p>
            <p>{{ $dispatchedBy->name ?? 'N/A' }}</p>
        </div>
        <div class="signature-box">
            <p>Approved By</p>
            <p>{{ $approvedBy->name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>For any queries, please contact the administration.</p>
    </div>
</body>

</html>