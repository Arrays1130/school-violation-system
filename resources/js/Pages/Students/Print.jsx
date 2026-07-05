import React from 'react';
import PrintLayout from '@/Layouts/PrintLayout';
import { Link } from '@inertiajs/react';
import { Printer } from 'lucide-react';
import dayjs from 'dayjs';

export default function Print({ auth, student }) {
    const lastAction = (caseItem) => {
        const actions = caseItem.actions || [];
        if (actions.length === 0) return 'None';
        return actions[actions.length - 1].action_taken;
    };

    return (
        <PrintLayout title={`Violation History - ${student.full_name}`}>
            <style>{`
                body { font-family: sans-serif; line-height: 1.4; color: #000; }
                .print-header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
                .print-section { margin-bottom: 20px; }
                .print-label { font-weight: bold; text-transform: uppercase; font-size: 0.8em; }
                .print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .print-table, .print-table th, .print-table td { border: 1px solid #000; }
                .print-table th, .print-table td { padding: 8px; text-align: left; vertical-align: top; }
            `}</style>

            <div className="no-print mb-5 flex items-center gap-4">
                <button
                    type="button"
                    onClick={() => window.print()}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-900"
                >
                    <Printer className="w-4 h-4" />
                    Print Report
                </button>
                <Link href={route('students.show', student.id)} className="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    Back to Profile
                </Link>
            </div>

            <div className="print-header">
                <h1 className="text-xl font-bold">STUDENT VIOLATION RECORD</h1>
                <p>I-Link CST Violation System | Official Record</p>
                <p>Generated: {dayjs().format('MMMM D, YYYY h:mm A')}</p>
            </div>

            <div className="print-section">
                <div className="print-label">Student Details</div>
                <p>
                    <strong>Name:</strong> {student.full_name}
                    <br />
                    <strong>ID/Email:</strong> {student.email}
                    <br />
                    <strong>Year & Section:</strong> {student.year_level ?? 'N/A'} - {student.section ?? 'N/A'}
                    <br />
                    <strong>Department:</strong> {student.department}
                    <br />
                    <strong>Guardian:</strong> {student.guardian_name ?? 'N/A'} ({student.guardian_phone ?? 'No Phone'})
                </p>
            </div>

            <div className="print-section">
                <div className="print-label">Violation History</div>
                {!student.cases || student.cases.length === 0 ? (
                    <p>No violation records found.</p>
                ) : (
                    <table className="print-table">
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
                            {student.cases.map((caseItem) => (
                                <tr key={caseItem.id}>
                                    <td>{caseItem.occurred_at ? dayjs(caseItem.occurred_at).format('MMM D, YYYY') : '-'}</td>
                                    <td>{caseItem.violation?.code}</td>
                                    <td>
                                        {caseItem.violation?.title} ({caseItem.violation?.severity})
                                    </td>
                                    <td>{caseItem.description}</td>
                                    <td>{caseItem.witness ?? '-'}</td>
                                    <td>
                                        <strong>Status:</strong> {caseItem.status}
                                        <br />
                                        <strong>Action:</strong> {lastAction(caseItem)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            <div className="print-section mt-12">
                <table style={{ width: '100%', border: 'none' }}>
                    <tbody>
                        <tr>
                            <td style={{ border: 'none', borderTop: '1px solid #000', width: '45%', textAlign: 'center', paddingTop: '8px' }}>
                                Student Signature
                            </td>
                            <td style={{ border: 'none', width: '10%' }} />
                            <td style={{ border: 'none', borderTop: '1px solid #000', width: '45%', textAlign: 'center', paddingTop: '8px' }}>
                                {auth?.user?.name}
                                <br />
                                Reporting Official
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </PrintLayout>
    );
}
