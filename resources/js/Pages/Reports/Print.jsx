import React from 'react';
import PrintLayout from '@/Layouts/PrintLayout';
import { Printer } from 'lucide-react';
import dayjs from 'dayjs';

function getStatusClass(status) {
    switch (status) {
        case 'Pending':
            return 'status-pending';
        case 'Hearing Scheduled':
            return 'status-hearing-scheduled';
        case 'Resolved':
        case 'Closed':
            return 'status-resolved';
        case 'Endorsed to Grievance':
            return 'status-endorsed-to-grievance';
        default:
            return '';
    }
}

export default function Print({ cases }) {
    const params = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
    const startDate = params.get('start_date') ?? 'START';
    const endDate = params.get('end_date') ?? 'CURRENT';
    const department = params.get('department') ?? 'All Departments';
    const status = params.get('status') ?? 'All Statuses';

    return (
        <PrintLayout title={`Violation Report - ${dayjs().format('YYYY-MM-DD')}`}>
            <style>{`
                .report-print-body {
                    font-family: 'Times New Roman', serif;
                    font-size: 13px;
                    line-height: 1.5;
                    color: #1a1a1a;
                    padding: 40px;
                    background: #fff;
                }
                .report-print-header {
                    text-align: center;
                    margin-bottom: 50px;
                    border-bottom: 3px double #000;
                    padding-bottom: 20px;
                }
                .report-print-header h1 {
                    margin: 0;
                    font-size: 26px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .report-print-header p {
                    margin: 5px 0;
                    font-size: 14px;
                    font-style: italic;
                }
                .report-print-meta { margin-bottom: 25px; font-weight: bold; }
                .report-print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .report-print-table th, .report-print-table td {
                    padding: 12px 8px;
                    text-align: left;
                    border: 1px solid #000;
                }
                .report-print-table th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 12px;
                }
                .report-badge {
                    padding: 3px 6px;
                    font-weight: bold;
                    border: 1px solid #000;
                    font-size: 10px;
                    text-transform: uppercase;
                    display: inline-block;
                }
                .status-pending { background-color: #fef9c3; }
                .status-hearing-scheduled { background-color: #e0f2fe; }
                .status-resolved { background-color: #dcfce7; }
                .status-endorsed-to-grievance { background-color: #fee2e2; }
                .report-print-footer {
                    margin-top: 60px;
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 100px;
                }
                .report-sig-box { text-align: center; }
                .report-sig-line {
                    border-top: 1px solid #000;
                    margin-top: 40px;
                    padding-top: 5px;
                    font-weight: bold;
                }
                @media print {
                    .report-print-body { padding: 0; margin: 1cm; }
                }
            `}</style>

            <button
                type="button"
                onClick={() => window.print()}
                className="no-print fixed top-5 right-5 bg-neutral-900 text-white border-none py-2.5 px-5 rounded font-bold z-[100] inline-flex items-center gap-2"
            >
                <Printer className="w-4 h-4" />
                Generate Print Document
            </button>

            <div className="report-print-body">
                <div className="report-print-header">
                    <h1>Student Violation Summary Report</h1>
                    <p>Office of Student Affairs - General Violation Record</p>
                </div>

                <div className="report-print-meta">
                    <div>DATE GENERATED: {dayjs().format('MMMM D, YYYY h:mm A')}</div>
                    <div>PERIOD: {startDate} TO {endDate}</div>
                    <div>PARAMETERS: {department} | {status}</div>
                </div>

                <table className="report-print-table">
                    <thead>
                        <tr>
                            <th style={{ width: '15%' }}>DATE</th>
                            <th style={{ width: '20%' }}>STUDENT NAME</th>
                            <th style={{ width: '15%' }}>DEPT / SECTION</th>
                            <th style={{ width: '30%' }}>VIOLATION DETAILS</th>
                            <th style={{ width: '20%' }}>CURRENT STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cases.map((caseItem) => (
                            <tr key={caseItem.id}>
                                <td>
                                    {caseItem.occurred_at
                                        ? dayjs(caseItem.occurred_at).format('MMM D, YYYY')
                                        : '-'}
                                </td>
                                <td style={{ fontWeight: 'bold' }}>
                                    {caseItem.student ? caseItem.student.full_name : 'N/A'}
                                </td>
                                <td>
                                    {caseItem.student?.department ?? '-'}
                                    <br />
                                    <small>{caseItem.student?.section ?? ''}</small>
                                </td>
                                <td>
                                    <strong>{caseItem.violation?.code ?? ''}</strong> - {caseItem.violation?.title ?? ''}
                                </td>
                                <td style={{ textAlign: 'center' }}>
                                    {(() => {
                                        const displayStatus = caseItem.endorsed_at ? 'Endorsed' : (caseItem.status || '');
                                        const statusClass = getStatusClass(caseItem.endorsed_at ? 'Endorsed to Grievance' : caseItem.status);
                                        return (
                                            <span className={`report-badge ${statusClass}`}>
                                                {displayStatus.toUpperCase()}
                                            </span>
                                        );
                                    })()}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                <div className="report-print-footer">
                    <div className="report-sig-box">
                        <div className="report-sig-line">PREPARED BY</div>
                        <small>Violation Officer</small>
                    </div>
                    <div className="report-sig-box">
                        <div className="report-sig-line">NOTED BY</div>
                        <small>Dean of Student Affairs</small>
                    </div>
                </div>
            </div>
        </PrintLayout>
    );
}
