import React from 'react';
import PrintLayout from '@/Layouts/PrintLayout';
import { Link } from '@inertiajs/react';
import { Printer } from 'lucide-react';
import dayjs from 'dayjs';
import BrandText from '@/Components/BrandText';

export default function Print({ auth, case: caseRecord }) {
    const padId = (id) => String(id).padStart(5, '0');

    return (
        <PrintLayout title={`Violation Record - ${caseRecord.student?.full_name}`}>
            <style>{`
                .case-print-body {
                    font-family: 'Times New Roman', Times, serif;
                    background: #e5e5e5;
                    color: #000;
                    padding: 20px;
                    font-size: 11pt;
                    line-height: 1.5;
                }
                .case-print-paper {
                    background: #fff;
                    width: 210mm;
                    min-height: 297mm;
                    margin: 0 auto;
                    padding: 20mm;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .case-print-header {
                    text-align: center;
                    margin-bottom: 25px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                }
                .case-print-header h1 { font-size: 18pt; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
                .case-print-header h2 { font-size: 13pt; font-weight: normal; margin-bottom: 5px; text-transform: uppercase; }
                .case-print-header p { font-size: 11pt; font-style: italic; }
                .case-print-title {
                    text-align: center;
                    font-size: 15pt;
                    font-weight: bold;
                    margin-bottom: 20px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .case-print-meta {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 15px;
                    font-size: 11pt;
                }
                .case-print-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 25px;
                }
                .case-print-table th, .case-print-table td {
                    border: 1px solid #000;
                    padding: 8px 12px;
                    vertical-align: top;
                }
                .case-print-table th {
                    text-align: left;
                    width: 20%;
                    font-weight: bold;
                }
                .case-print-table td { width: 30%; }
                .case-print-section-title {
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 12pt;
                    border-bottom: 1px solid #000;
                    display: inline-block;
                    margin-bottom: 10px;
                }
                .case-print-actions { margin-left: 20px; margin-top: 5px; margin-bottom: 5px; }
                .case-print-signatures {
                    margin-top: 50px;
                    display: flex;
                    justify-content: space-between;
                }
                .case-print-sig-box { width: 45%; text-align: center; }
                .case-print-sig-line {
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
                .case-print-sig-label {
                    font-size: 10pt;
                    text-transform: uppercase;
                    font-weight: bold;
                }
                .case-print-footer {
                    margin-top: 40px;
                    text-align: center;
                    font-size: 8pt;
                    border-top: 1px solid #000;
                    padding-top: 8px;
                    font-family: sans-serif;
                }
                @media print {
                    .case-print-body { background: none; padding: 0; }
                    .case-print-paper { box-shadow: none; margin: 0; padding: 0; width: 100%; min-height: auto; }
                }
            `}</style>

            <div className="case-print-body">
                <div className="no-print text-center mb-5 flex items-center justify-center gap-3">
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 text-white border border-slate-700 rounded-lg font-sans text-sm font-semibold hover:bg-slate-900 transition-colors"
                    >
                        <Printer className="w-4 h-4" />
                        Print Report
                    </button>
                    <Link
                        href={route('reports.index')}
                        className="inline-block px-5 py-2.5 bg-slate-800 text-white border border-slate-700 rounded-lg font-sans text-sm font-semibold hover:bg-slate-900 transition-colors"
                    >
                        Back to Reports
                    </Link>
                </div>

                <div className="case-print-paper">
                    <div className="case-print-header">
                        <h1><BrandText>I-Link College of Science and Technology</BrandText></h1>
                        <h2>Office of Student Affairs</h2>
                        <p>Official Student Violation Record</p>
                    </div>

                    <div className="case-print-title">Violation Incident Report</div>

                    <div className="case-print-meta">
                        <div><strong>Case Ref No:</strong> #{padId(caseRecord.id)}</div>
                        <div><strong>Date Generated:</strong> {dayjs().format('MMMM D, YYYY')}</div>
                    </div>

                    <div className="case-print-section-title">I. Student Information</div>
                    <table className="case-print-table">
                        <tbody>
                            <tr>
                                <th>Student Name:</th>
                                <td colSpan={3}><strong>{caseRecord.student?.full_name}</strong></td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{caseRecord.student?.department}</td>
                                <th>Year & Section:</th>
                                <td>
                                    {caseRecord.student?.year_level ?? 'N/A'} - {caseRecord.student?.section ?? 'N/A'}
                                </td>
                            </tr>
                            <tr>
                                <th>Email Address:</th>
                                <td colSpan={3}>{caseRecord.student?.email}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="case-print-section-title">II. Incident Details</div>
                    <table className="case-print-table">
                        <tbody>
                            <tr>
                                <th>Date of Incident:</th>
                                <td>{caseRecord.occurred_at ? dayjs(caseRecord.occurred_at).format('MMMM D, YYYY') : '-'}</td>
                                <th>Time of Incident:</th>
                                <td>{caseRecord.occurred_at ? dayjs(caseRecord.occurred_at).format('h:mm A') : '-'}</td>
                            </tr>
                            <tr>
                                <th>Violation Code:</th>
                                <td><strong>{caseRecord.violation?.code}</strong></td>
                                <th>Severity Level:</th>
                                <td>{caseRecord.violation?.severity}</td>
                            </tr>
                            <tr>
                                <th>Violation Title:</th>
                                <td colSpan={3}><strong>{caseRecord.violation?.title}</strong></td>
                            </tr>
                            <tr>
                                <th>Offense Level:</th>
                                <td colSpan={3}>{caseRecord.offense_level}</td>
                            </tr>
                            {caseRecord.witness && (
                                <tr>
                                    <th>Witness(es):</th>
                                    <td colSpan={3}>{caseRecord.witness}</td>
                                </tr>
                            )}
                            <tr>
                                <th colSpan={4} style={{ textAlign: 'center', borderBottom: '1px solid #000' }}>
                                    Narrative / Description of the Incident
                                </th>
                            </tr>
                            <tr>
                                <td colSpan={4}>
                                    <div style={{ minHeight: '80px', padding: '5px', whiteSpace: 'pre-wrap' }}>
                                        {caseRecord.description}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="case-print-section-title">III. Sanctions & Interventions</div>
                    <table className="case-print-table">
                        <tbody>
                            <tr>
                                <th>Prescribed Sanction:</th>
                                <td style={{ width: '80%' }}>
                                    <strong>{caseRecord.sanction || 'Determination pending.'}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Current Status:</th>
                                <td><strong>{(caseRecord.status || '').toUpperCase()}</strong></td>
                            </tr>
                            <tr>
                                <th colSpan={2} style={{ textAlign: 'center', borderBottom: '1px solid #000' }}>
                                    Actions Taken / Interventions
                                </th>
                            </tr>
                            <tr>
                                <td colSpan={2}>
                                    {!caseRecord.actions || caseRecord.actions.length === 0 ? (
                                        <div style={{ padding: '5px 10px', fontStyle: 'italic' }}>
                                            No actions have been recorded for this case yet.
                                        </div>
                                    ) : (
                                        <ul className="case-print-actions">
                                            {caseRecord.actions.map((action) => (
                                                <li key={action.id}>
                                                    <strong>{action.action_taken}</strong> — Recorded by{' '}
                                                    {action.user?.name} on{' '}
                                                    {dayjs(action.created_at).format('MMMM D, YYYY')}
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="case-print-signatures">
                        <div className="case-print-sig-box">
                            <div className="case-print-sig-line" />
                            <div className="case-print-sig-label">Student Signature over Printed Name</div>
                            <div style={{ marginTop: '5px', fontSize: '10pt' }}>Date: ________________________</div>
                        </div>
                        <div className="case-print-sig-box">
                            <div className="case-print-sig-line">{auth?.user?.name}</div>
                            <div className="case-print-sig-label">Reporting Official / OSA Representative</div>
                            <div style={{ marginTop: '5px', fontSize: '10pt' }}>Date: ________________________</div>
                        </div>
                    </div>

                    <div className="case-print-footer">
                        CONFIDENTIALITY NOTICE: This document contains confidential information intended only for the
                        use of the individual or entity named above. If you are not the intended recipient, you are
                        hereby notified that any disclosure, copying, distribution, or taking of any action in reliance
                        on the contents of this document is strictly prohibited.
                    </div>
                </div>
            </div>
        </PrintLayout>
    );
}
