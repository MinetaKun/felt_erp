<!DOCTYPE html>
<html>

<head>
    <title>Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
    </style>
</head>

<body>
    <h1>Order Details</h1>
    <table>
        <tr>
            <th>Order ID</th>
            <td>{{ $order->id }}</td>
        </tr>
        <tr>
            <th>Product Name</th>
            <td>{{ $order->product_name }}</td>
        </tr>
        <tr>
            <th>Total Quantity</th>
            <td>{{ $order->quantity }}</td>
        </tr>
        <tr>
            <th>Assigned Quantity</th>
            <td>{{ $order->assigned_quantity }}</td>
        </tr>
        <tr>
            <th>Remaining Quantity</th>
            <td>{{ $order->remaining_quantity }}</td>
        </tr>
        <tr>
            <th>Produced Quantity</th>
            <td>{{ $order->produced_quantity }}</td>
        </tr>
        <tr>
            <th>Accepted Quantity</th>
            <td>{{ $order->accepted_quantity }}</td>
        </tr>
        <tr>
            <th>Rejected Quantity</th>
            <td>{{ $order->rejected_quantity }}</td>
        </tr>
        <tr>
            <th>Wool Color</th>
            <td>{{ $order->wool_color }}</td>
        </tr>
        <tr>
            <th>Size (cm)</th>
            <td>{{ $order->size_cm }}</td>
        </tr>
        <tr>
            <th>Wage per Piece</th>
            <td>{{ $order->wage_per_piece }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $order->status }}</td>
        </tr>
        <tr>
            <th>Assignments</th>
            <td>
                @if($order->assignments->isEmpty())
                Not Assigned
                @else
                <ul>
                    @foreach($order->assignments as $assignment)
                    <li>
                        {{ $assignment->artisan->name }}:
                        Assigned: {{ $assignment->assigned_quantity }} pieces,
                        Produced: {{ $assignment->produced_quantity }} pieces,
                        Accepted: {{ $assignment->accepted_quantity }} pieces,
                        Rejected: {{ $assignment->rejected_quantity }} pieces,
                        Dispatched: {{ $assignment->is_dispatched ? 'Yes' : 'No' }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </td>
        </tr>
        <tr>
            <th>Created At</th>
            <td>{{ $order->created_at }}</td>
        </tr>
        <tr>
            <th>Updated At</th>
            <td>{{ $order->updated_at }}</td>
        </tr>
    </table>
</body>

</html>