<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Violation Record - {{ $case->student->full_name }}</title>
    <style>
        /* Base reset and fonts */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            background: #e5e5e5; 
            color: #000; 
            padding: 20px; 
            font-size: 11pt;
            line-height: 1.5;
        }
        
        /* A4 Paper simulation */
        .paper {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Buttons (No Print) */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #fff;
            color: #000;
            text-decoration: none;
            border: 1px solid #000;
            border-radius: 4px;
            font-family: sans-serif;
            cursor: pointer;
            margin: 0 5px;
            font-size: 14px;
        }
        .btn:hover {
            background: #eee;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18pt;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header h2 {
            font-size: 13pt;
            font-weight: normal;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header p {
            font-size: 11pt;
            font-style: italic;
        }
        
        .report-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11pt;
        }

        /* Formal Tables/Grids for Data */
        .table-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table-section th, .table-section td {
            border: 1px solid #000;
            padding: 8px 12px;
            vertical-align: top;
        }
        .table-section th {
            text-align: left;
            width: 20%;
            font-weight: bold;
            background: transparent;
        }
        .table-section td {
            width: 30%;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12pt;
            margin-bottom: 8px;
            color: #000;
            padding: 0;
            border-bottom: 1px solid #000;
            display: inline-block;
            margin-bottom: 10px;
        }

        .actions-list {
            margin-left: 20px;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        /* Signatures */
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            min-height: 25px; 
            display: flex;
            align-items: flex-end;
            justify-content: center;
            font-weight: bold;
            font-size: 12pt;
            padding-bottom: 2px;
        }
        .signature-label {
            font-size: 10pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        .footer-notice {
            margin-top: 40px; 
            text-align: center; 
            font-size: 8pt; 
            color: #000; 
            border-top: 1px solid #000; 
            padding-top: 8px;
            font-family: sans-serif;
        }

        /* Print Adjustments */
        @media print {
            body { 
                background: none; 
                padding: 0; 
            }
            .paper { 
                box-shadow: none; 
                margin: 0; 
                padding: 0;
                width: 100%;
                min-height: auto;
            }
            .no-print { 
                display: none; 
            }
            @page {
                size: portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">
            Print Report
        </button>
        <a href="{{ route('reports.index') }}" class="btn" style="vertical-align: middle;">Back to Reports</a>
    </div>

    <div class="paper">
        <div class="header">
            <h1>I-Link College of Science and Technology</h1>
            <h2>Office of Student Affairs</h2>
            <p>Official Student Disciplinary Record</p>
        </div>

        <div class="report-title">
            Violation Incident Report
        </div>

        <div class="meta-info">
            <div><strong>Case Ref No:</strong> #{{ str_pad($case->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div><strong>Date Generated:</strong> {{ now()->format('F d, Y') }}</div>
        </div>

        <div class="section-title">I. Student Information</div>
        <table class="table-section">
            <tr>
                <th>Student Name:</th>
                <td colspan="3"><strong>{{ $case->student->full_name }}</strong></td>
            </tr>
            <tr>
                <th>Department:</th>
                <td>{{ $case->student->department }}</td>
                <th>Year & Section:</th>
                <td>{{ $case->student->year_level ?? 'N/A' }} - {{ $case->student->section ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Email Address:</th>
                <td colspan="3">{{ $case->student->email }}</td>
            </tr>
        </table>

        <div class="section-title">II. Incident Details</div>
        <table class="table-section">
            <tr>
                <th>Date of Incident:</th>
                <td>{{ $case->occurred_at->format('F d, Y') }}</td>
                <th>Time of Incident:</th>
                <td>{{ $case->occurred_at->format('h:i A') }}</td>
            </tr>
            <tr>
                <th>Violation Code:</th>
                <td><strong>{{ $case->violation->code }}</strong></td>
                <th>Severity Level:</th>
                <td>{{ $case->violation->severity }}</td>
            </tr>
            <tr>
                <th>Violation Title:</th>
                <td colspan="3"><strong>{{ $case->violation->title }}</strong></td>
            </tr>
            <tr>
                <th>Offense Level:</th>
                <td colspan="3">{{ $case->offense_level }}</td>
            </tr>
            @if($case->witness)
            <tr>
                <th>Witness(es):</th>
                <td colspan="3">{{ $case->witness }}</td>
            </tr>
            @endif
            <tr>
                <th colspan="4" style="text-align: center; border-bottom: 1px solid #000;">Narrative / Description of the Incident</th>
            </tr>
            <tr>
                <td colspan="4">
                    <div style="min-height: 80px; padding: 5px; white-space: pre-wrap;">{{ $case->description }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">III. Sanctions & Interventions</div>
        <table class="table-section">
            <tr>
                <th>Prescribed Sanction:</th>
                <td style="width: 80%;"><strong>{{ $case->sanction ?: 'Determination pending.' }}</strong></td>
            </tr>
            <tr>
                <th>Current Status:</th>
                <td><strong>{{ strtoupper($case->status) }}</strong></td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: center; border-bottom: 1px solid #000;">Actions Taken / Interventions</th>
            </tr>
            <tr>
                <td colspan="2">
                    @if($case->actions->isEmpty())
                        <div style="padding: 5px 10px; font-style: italic; color: #000;">No actions have been recorded for this case yet.</div>
                    @else
                        <ul class="actions-list">
                            @foreach($case->actions as $action)
                                <li><strong>{{ $action->action_taken }}</strong> — Recorded by {{ $action->user->name }} on {{ $action->created_at->format('F d, Y') }}</li>
                            @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
        </table>

        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line">
                    <!-- Space for signature -->
                </div>
                <div class="signature-label">Student Signature over Printed Name</div>
                <div style="margin-top:5px; font-size:10pt;">Date: ________________________</div>
            </div>

            <div class="signature-box">
                <div class="signature-line">
                    {{ auth()->user()->name }}
                </div>
                <div class="signature-label">Reporting Official / OSA Representative</div>
                <div style="margin-top:5px; font-size:10pt;">Date: ________________________</div>
            </div>
        </div>
        
        <div class="footer-notice">
            CONFIDENTIALITY NOTICE: This document contains confidential information intended only for the use of the individual or entity named above. If you are not the intended recipient, you are hereby notified that any disclosure, copying, distribution, or taking of any action in reliance on the contents of this document is strictly prohibited.
        </div>
    </div>

</body>
</html>
