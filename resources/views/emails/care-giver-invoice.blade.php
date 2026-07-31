<p>Hello {{ $invoice->careGiver->name }},</p>

<p>
    Please find attached your Mauricare invoice
    <strong>{{ $invoice->invoice_number }}</strong>
    for the period {{ $invoice->period_start->format('d M Y') }}
    to {{ $invoice->period_end->format('d M Y') }}.
</p>

<p>
    Total amount due:
    <strong>MUR {{ number_format((float) $invoice->amount_due, 2) }}</strong>
</p>

<p>Kind regards,<br>Mauricare</p>
