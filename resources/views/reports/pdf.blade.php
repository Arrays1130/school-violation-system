<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Violation Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; color: #fff; }
        .Open { background-color: #f59e0b; }
        .Resolved { background-color: #10b981; }
        .Scheduled { background-color: #3b82f6; }
        .Dismissed { background-color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>I-Link CST - Student Violation Report</h1>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        <p>Filters: Department: {{ request('department') ?? 'All' }} | Status: {{ request('status') ?? 'All' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Department</th>
                <th>Violation</th>
                <th>Status</th>
                <th>Hearing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
                <tr>
                    <td>{{ $case->occurred_at->format('M d, Y') }}</td>
                    <td>{{ $case->student->full_name }}</td>
                    <td>{{ $case->student->department }}</td>
                    <td>{{ $case->violation->code }} - {{ $case->violation->title }}</td>
                    <td><span class="badge {{ $case->status }}">{{ $case->status }}</span></td>
                    <td>{{ $case->hearing ? $case->hearing->scheduled_at->format('M d') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
