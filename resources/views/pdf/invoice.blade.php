<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 34px; }
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { border-bottom: 3px solid #117d73; padding-bottom: 20px; }
        .brand { color: #117d73; font-size: 25px; font-weight: bold; }
        .meta { float: right; line-height: 1.65; text-align: right; }
        .recipient { margin: 25px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #dbe3ec; text-align: left; }
        th { background: #f1f5f9; color: #475569; font-size: 10px; text-transform: uppercase; }
        .number { text-align: right; }
        .summary { width: 330px; margin: 26px 0 0 auto; }
        .summary td { border: 0; padding: 7px; }
        .total td { border-top: 2px solid #117d73; color: #117d73; font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="meta">
            <strong>{{ $invoice->invoice_number }}</strong><br>
            Generated {{ $invoice->created_at->format('d M Y') }}<br>
            {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}
        </div>
        <div class="brand">MAURICARE</div>
        <div>Care giver commission invoice</div>
    </div>

    <div class="recipient">
        <strong>{{ $invoice->careGiver->name }}</strong><br>
        {{ $invoice->careGiver->email }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking</th>
                <th>Date</th>
                <th>Care seeker</th>
                <th>Care type</th>
                <th class="number">Paid amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->bookings as $booking)
                <tr>
                    <td>#{{ $booking->id }}</td>
                    <td>{{ $booking->scheduled_date->format('d M Y') }}</td>
                    <td>{{ $booking->user?->name ?? '—' }}</td>
                    <td>{{ str($booking->care_type)->replace('_', ' ')->title() }}</td>
                    <td class="number">MUR {{ number_format((float) ($booking->amount_paid ?? $booking->amount_due), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Closed booking total</td>
            <td class="number"><strong>MUR {{ number_format((float) $invoice->booking_total, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Rate</td>
            <td class="number"><strong>{{ number_format((float) $invoice->rate, 2) }}%</strong></td>
        </tr>
        <tr class="total">
            <td>Total amount due</td>
            <td class="number">MUR {{ number_format((float) $invoice->amount_due, 2) }}</td>
        </tr>
    </table>
</body>
</html>
