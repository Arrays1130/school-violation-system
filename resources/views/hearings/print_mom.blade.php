<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minutes of Meeting - Case #{{ $hearing->case->id }}</title>
    <style>
        @page { size: letter; margin: 1in; } /* Standard US Letter margins */
        body { 
            font-family: 'Times New Roman', Times, serif; 
            line-height: 1.6; 
            color: #000; 
            margin: 0; 
            padding: 40px; 
            background: #f4f4f4; /* Gray background for screen */
        }
        .document-container { 
            max-width: 8in; 
            margin: 0 auto; 
            padding: 1in; 
            background: #fff; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: bold; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 16px; font-weight: normal; }
        .header p { margin: 5px 0; font-size: 12px; }
        .line-separator { border-top: 2px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 20px 0; }
        
        .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin-bottom: 30px; letter-spacing: 1px; }

        table.meta-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.meta-table td { padding: 6px 0; vertical-align: top; font-size: 14px; }
        table.meta-table td.label { width: 160px; font-weight: bold; }
        
        .section { margin-bottom: 30px; }
        .section-title { font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; text-decoration: underline; }
        .section-content { font-size: 14px; text-align: justify; white-space: pre-wrap; padding-left: 20px; }
        
        .participants-list { padding-left: 40px; margin: 0; font-size: 14px; }
        
        .signatures { margin-top: 80px; width: 100%; display: table; }
        .signature-block { display: table-cell; width: 50%; vertical-align: bottom; }
        .signature-line { width: 80%; border-top: 1px solid #000; margin-top: 50px; text-align: center; font-size: 14px; font-weight: bold; padding-top: 5px; }
        .signature-sub { text-align: center; font-size: 12px; width: 80%; }

        .print-btn { 
            position: fixed; top: 20px; right: 20px; 
            background: #2563eb; color: #fff; border: none; padding: 12px 24px; 
            border-radius: 8px; cursor: pointer; font-weight: bold; font-family: sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: background 0.2s;
        }
        .print-btn:hover { background: #1d4ed8; }

        @media print {
            body { background: #fff; padding: 0; }
            .document-container { box-shadow: none; padding: 0; max-width: 100%; margin: 0; border: none; }
            .print-btn, .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn no-print">Print Document</button>

    <div class="document-container">
        <div class="header">
            <h1>I-LINK COLLEGE OF SCIENCE AND TECHNOLOGY</h1>
            <h2>Office of Student Affairs</h2>
            <p><strong>HEARING RECORD</strong></p>
        </div>
        
        <div class="line-separator"></div>

        <div class="title">MINUTES OF MEETING</div>

        <table class="meta-table">
            <tr>
                <td class="label">Case Number:</td>
                <td>#{{ str_pad($hearing->case->id, 4, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td>{{ $hearing->scheduled_at->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Time:</td>
                <td>{{ $hearing->scheduled_at->format('h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Venue:</td>
                <td>{{ $hearing->venue }}</td>
            </tr>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong>{{ $hearing->case->student->full_name }}</strong></td>
            </tr>
            <tr>
                <td class="label">Program/Course:</td>
                <td>{{ $hearing->case->student->department }}</td>
            </tr>
            <tr>
                <td class="label">Violation/Offense:</td>
                <td>{{ $hearing->case->violation->code ?? 'N/A' }} - {{ $hearing->case->violation->title ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">I. Present in the Meeting:</div>
            <ul class="participants-list">
                @foreach($hearing->participants as $participant)
                    <li>{{ $participant }}</li>
                @endforeach
            </ul>
        </div>

        <div class="section">
            <div class="section-title">II. Minutes / Proceedings:</div>
            <div class="section-content">{{ $hearing->meeting_minutes ?? 'No details recorded.' }}</div>
        </div>

        <div class="section">
            <div class="section-title">III. Notes / Remarks:</div>
            <div class="section-content">{{ $hearing->notes ?? 'None' }}</div>
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">{{ $hearing->case->student->full_name }}</div>
                <div class="signature-sub">Signature over Printed Name<br>(Student/Guardian)</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">OSA Representative</div>
                <div class="signature-sub">Signature over Printed Name<br>(Discipline Officer)</div>
            </div>
        </div>
    </div>
</body>
</html>
