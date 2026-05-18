<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Violation History - {{ $student->full_name }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.4; color: #000; }
        .header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .section { margin-bottom: 20px; }
        .label { font-weight: bold; text-transform: uppercase; font-size: 0.8em; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; vertical-align: top; }
        .no-print { margin-bottom: 20px; }
        @media print {
            @page { size: portrait; margin: 1cm; }
            .no-print { display: none; }
        }
        body { background: #fff; color: #000; padding: 20px; }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()">Print Report</button>
        <a href="{{ route('students.show', $student) }}">Back to Profile</a>
    </div>

    <div class="header">
        <h1>STUDENT VIOLATION RECORD</h1>
        <p>I-Link CST Violation System | Official Record</p>
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="section">
        <div class="label">Student Details</div>
        <p>
            <strong>Name:</strong> {{ $student->full_name }}<br>
            <strong>ID/Email:</strong> {{ $student->email }}<br>
            <strong>Year & Section:</strong> {{ $student->year_level ?? 'N/A' }} - {{ $student->section ?? 'N/A' }}<br>
            <strong>Department:</strong> {{ $student->department }}<br>
            <strong>Guardian:</strong> {{ $student->guardian_name ?? 'N/A' }} ({{ $student->guardian_phone ?? 'No Phone' }})
        </p>
    </div>

    <div class="section">
        <div class="label">Violation History</div>
        @if($student->cases->isEmpty())
            <p>No violation records found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Violation</th>
                        <th>Description</th>
                        <th>Witness</th>
                        <th>Action/Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->cases as $case)
                        <tr>
                            <td>{{ $case->occurred_at->format('M d, Y') }}</td>
                            <td>{{ $case->violation->code }}</td>
                            <td>{{ $case->violation->title }} ({{ $case->violation->severity }})</td>
                            <td>{{ $case->description }}</td>
                            <td>{{ $case->witness ?? '-' }}</td>
                            <td>
                                <strong>Status:</strong> {{ $case->status }}<br>
                                <strong>Action:</strong> {{ $case->actions->isNotEmpty() ? $case->actions->last()->action_taken : 'None' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section" style="margin-top: 50px;">
        <table style="border: none;">
            <tr>
                <td style="border: none; border-top: 1px solid #000; width: 45%; text-align: center;">
                    Student Signature
                </td>
                <td style="border: none; width: 10%;"></td>
                <td style="border: none; border-top: 1px solid #000; width: 45%; text-align: center;">
                    {{ auth()->user()->name }}<br>
                    Reporting Official
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
