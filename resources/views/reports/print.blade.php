<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Violation Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 13px; 
            line-height: 1.5; 
            color: #1a1a1a; 
            padding: 40px; 
            background: #fff;
        }
        .header { 
            text-align: center; 
            margin-bottom: 50px; 
            border-bottom: 3px double #000; 
            padding-bottom: 20px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 26px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .header p { 
            margin: 5px 0; 
            font-size: 14px; 
            font-style: italic;
        }
        .report-meta {
            margin-bottom: 25px;
            font-weight: bold;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            padding: 12px 8px; 
            text-align: left; 
            border: 1px solid #000;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 12px;
        }
        .badge { 
            padding: 3px 6px; 
            font-weight: bold; 
            border: 1px solid #000;
            font-size: 10px;
            text-transform: uppercase;
            display: inline-block;
        }
        /* Status Colors - Print Friendly */
        .status-pending { background-color: #fef9c3; } /* Yellow */
        .status-hearing-scheduled { background-color: #e0f2fe; } /* Sky Blue */
        .status-resolved, .status-closed { background-color: #dcfce7; } /* Green */
        .status-endorsed-to-grievance { background-color: #fee2e2; } /* Red */
        
        .footer {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
        }
        .sig-box {
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 1cm; }
            @page { margin: 2cm; }
        }
        
        .print-btn {
            position: fixed; top: 20px; right: 20px;
            background: #1a1a1a; color: #fff; border: none; padding: 10px 20px;
            border-radius: 4px; cursor: pointer; font-weight: bold; z-index: 100;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn no-print">Generate Print Document</button>

    <div class="header">
        <h1>Student Violation Summary Report</h1>
        <p>Office of Student Affairs - General Disciplinary Record</p>
    </div>

    <div class="report-meta">
        <div>DATE GENERATED: {{ now()->format('F d, Y h:i A') }}</div>
        <div>PERIOD: {{ request('start_date') ?? 'START' }} TO {{ request('end_date') ?? 'CURRENT' }}</div>
        <div>PARAMETERS: {{ request('department') ?? 'All Departments' }} | {{ request('status') ?? 'All Statuses' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">DATE</th>
                <th width="20%">STUDENT NAME</th>
                <th width="15%">DEPT / SECTION</th>
                <th width="30%">VIOLATION DETAILS</th>
                <th width="20%">CURRENT STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
                @php
                    $statusClass = match($case->status) {
                        'Pending' => 'status-pending',
                        'Hearing Scheduled' => 'status-hearing-scheduled',
                        'Resolved', 'Closed' => 'status-resolved',
                        'Endorsed to Grievance' => 'status-endorsed-to-grievance',
                        default => ''
                    };
                @endphp
                <tr>
                    <td>{{ $case->occurred_at ? $case->occurred_at->format('M d, Y') : '-' }}</td>
                    <td style="font-weight: bold;">{{ $case->student ? $case->student->full_name : 'N/A' }}</td>
                    <td>{{ $case->student->department ?? '-' }}<br><small>{{ $case->student->section ?? '' }}</small></td>
                    <td>
                        <strong>{{ $case->violation->code ?? '' }}</strong> - {{ $case->violation->title ?? '' }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $statusClass }}">
                            {{ strtoupper($case->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-box">
            <div class="sig-line">PREPARED BY</div>
            <small>Disciplinary Officer</small>
        </div>
        <div class="sig-box">
            <div class="sig-line">NOTED BY</div>
            <small>Dean of Student Affairs</small>
        </div>
    </div>
</body>
</html>

