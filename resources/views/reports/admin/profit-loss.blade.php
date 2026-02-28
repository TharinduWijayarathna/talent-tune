<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit &amp; Loss Report – TalentTune</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; max-width: 400px; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; width: 50%; }
        .text-right { text-align: right; }
        .profit { color: green; }
        .loss { color: red; }
        p { margin: 6px 0; }
    </style>
</head>
<body>
    <h1>Profit &amp; Loss Report</h1>
    <p class="meta">Generated: {{ $generatedAt }}</p>
    <p>Based on subscription revenue (${{ number_format($subscriptionPrice, 0) }} per monthly payment) and costs (API calls + Google TTS: ${{ number_format($costPerSubscriber, 0) }} per active subscriber).</p>
    <table>
        <tr>
            <th>Completed payments (monthly)</th>
            <td class="text-right">{{ $completedPaymentsCount }}</td>
        </tr>
        <tr>
            <th>Active subscribers</th>
            <td class="text-right">{{ $activeSubscribersCount }}</td>
        </tr>
        <tr>
            <th>Revenue</th>
            <td class="text-right">${{ number_format($revenue, 2) }}</td>
        </tr>
        <tr>
            <th>Costs (API + Google TTS)</th>
            <td class="text-right">${{ number_format($costs, 2) }}</td>
        </tr>
        <tr>
            <th>Profit / Loss</th>
            <td class="text-right {{ $profitLoss >= 0 ? 'profit' : 'loss' }}">${{ number_format($profitLoss, 2) }}</td>
        </tr>
    </table>
</body>
</html>
