<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments Report – TalentTune</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .total { font-weight: bold; margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Payments Report</h1>
    <p class="meta">Generated: {{ $generatedAt }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Institution</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Gateway</th>
                <th>Paid at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
            <tr>
                <td>{{ $p['id'] }}</td>
                <td>{{ $p['institution_name'] }}</td>
                <td class="text-right">${{ number_format($p['amount_dollars'], 2) }}</td>
                <td>{{ $p['status'] }}</td>
                <td>{{ $p['gateway'] ?? '—' }}</td>
                <td>{{ $p['paid_at'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">Total amount: ${{ number_format($totalAmountDollars, 2) }}</p>
</body>
</html>
