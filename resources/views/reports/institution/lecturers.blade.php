<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturers Report – {{ $institutionName }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 14px; margin-bottom: 8px; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Lecturers Report</h1>
    <p class="meta">{{ $institutionName }} – Generated: {{ $generatedAt }}</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Employee ID</th>
                <th>Department</th>
                <th>Email</th>
                <th>Total vivas</th>
                <th>Completed submissions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lecturers as $lec)
            <tr>
                <td>{{ $lec['name'] }}</td>
                <td>{{ $lec['employee_id'] }}</td>
                <td>{{ $lec['department'] }}</td>
                <td>{{ $lec['email'] }}</td>
                <td>{{ $lec['total_vivas'] }}</td>
                <td>{{ $lec['completed_submissions'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
