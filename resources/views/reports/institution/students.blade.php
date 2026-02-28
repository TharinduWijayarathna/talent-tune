<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 14px; margin-bottom: 4px; }
        h2 { font-size: 12px; margin: 12px 0 6px; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f5f5f5; }
        .batch-section { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Students Report (by Batch)</h1>
    <p class="meta">{{ $institutionName }} – Generated: {{ $generatedAt }}</p>
    @foreach($byBatch as $batch => $students)
    <div class="batch-section">
        <h2>Batch: {{ $batch }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Completed vivas</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td>{{ $s['name'] }}</td>
                    <td>{{ $s['student_id'] ?? '—' }}</td>
                    <td>{{ $s['email'] }}</td>
                    <td>{{ $s['department'] }}</td>
                    <td>{{ $s['completed_vivas'] }}</td>
                    <td>{{ $s['created_at'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</body>
</html>
