import React from 'react';
import PrintLayout from '@/Layouts/PrintLayout';
import { Printer } from 'lucide-react';
import dayjs from 'dayjs';
import BrandText from '@/Components/BrandText';

export default function PrintMom({ hearing }) {
    const padCaseId = (id) => String(id).padStart(4, '0');
    const participants = hearing.participants || [];

    return (
        <PrintLayout title={`Minutes of Meeting - Case #${hearing.case?.id}`}>
            <style>{`
                .mom-body {
                    font-family: 'Times New Roman', Times, serif;
                    line-height: 1.6;
                    color: #000;
                    margin: 0;
                    padding: 40px;
                    background: #f4f4f4;
                }
                .mom-container {
                    max-width: 8in;
                    margin: 0 auto;
                    padding: 1in;
                    background: #fff;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .mom-header { text-align: center; margin-bottom: 30px; }
                .mom-header h1 { margin: 0; font-size: 22px; font-weight: bold; text-transform: uppercase; }
                .mom-header h2 { margin: 5px 0 0; font-size: 16px; font-weight: normal; }
                .mom-header p { margin: 5px 0; font-size: 12px; }
                .mom-line-separator {
                    border-top: 2px solid #000;
                    border-bottom: 1px solid #000;
                    height: 2px;
                    margin: 20px 0;
                }
                .mom-title {
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    text-decoration: underline;
                    margin-bottom: 30px;
                    letter-spacing: 1px;
                }
                .mom-meta { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .mom-meta td { padding: 6px 0; vertical-align: top; font-size: 14px; }
                .mom-meta td.label { width: 160px; font-weight: bold; }
                .mom-section { margin-bottom: 30px; }
                .mom-section-title {
                    font-weight: bold;
                    font-size: 14px;
                    text-transform: uppercase;
                    margin-bottom: 10px;
                    text-decoration: underline;
                }
                .mom-section-content {
                    font-size: 14px;
                    text-align: justify;
                    white-space: pre-wrap;
                    padding-left: 20px;
                }
                .mom-participants { padding-left: 40px; margin: 0; font-size: 14px; }
                .mom-signatures { margin-top: 80px; width: 100%; display: table; }
                .mom-sig-block { display: table-cell; width: 50%; vertical-align: bottom; }
                .mom-sig-line {
                    width: 80%;
                    border-top: 1px solid #000;
                    margin-top: 50px;
                    text-align: center;
                    font-size: 14px;
                    font-weight: bold;
                    padding-top: 5px;
                }
                .mom-sig-sub { text-align: center; font-size: 12px; width: 80%; }
                @media print {
                    .mom-body { background: #fff; padding: 0; }
                    .mom-container { box-shadow: none; padding: 0; max-width: 100%; margin: 0; border: none; }
                }
            `}</style>

            <button
                type="button"
                onClick={() => window.print()}
                className="no-print fixed top-5 right-5 bg-blue-600 hover:bg-blue-700 text-white border-none py-3 px-6 rounded-lg font-bold font-sans shadow-md inline-flex items-center gap-2"
            >
                <Printer className="w-4 h-4" />
                Print Document
            </button>

            <div className="mom-body">
                <div className="mom-container">
                    <div className="mom-header">
                        <h1><BrandText>I-LINK COLLEGE OF SCIENCE AND TECHNOLOGY</BrandText></h1>
                        <h2>Office of Student Affairs</h2>
                        <p><strong>HEARING RECORD</strong></p>
                    </div>

                    <div className="mom-line-separator" />

                    <div className="mom-title">MINUTES OF MEETING</div>

                    <table className="mom-meta">
                        <tbody>
                            <tr>
                                <td className="label">Case Number:</td>
                                <td>#{padCaseId(hearing.case?.id)}</td>
                            </tr>
                            <tr>
                                <td className="label">Date:</td>
                                <td>{hearing.scheduled_at ? dayjs(hearing.scheduled_at).format('MMMM D, YYYY') : '-'}</td>
                            </tr>
                            <tr>
                                <td className="label">Time:</td>
                                <td>{hearing.scheduled_at ? dayjs(hearing.scheduled_at).format('h:mm A') : '-'}</td>
                            </tr>
                            <tr>
                                <td className="label">Venue:</td>
                                <td>{hearing.venue}</td>
                            </tr>
                            <tr>
                                <td className="label">Student Name:</td>
                                <td><strong>{hearing.case?.student?.full_name}</strong></td>
                            </tr>
                            <tr>
                                <td className="label">Program/Course:</td>
                                <td>{hearing.case?.student?.department}</td>
                            </tr>
                            <tr>
                                <td className="label">Violation/Offense:</td>
                                <td>
                                    {hearing.case?.violation?.code ?? 'N/A'} - {hearing.case?.violation?.title ?? 'N/A'}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="mom-section">
                        <div className="mom-section-title">I. Present in the Meeting:</div>
                        <ul className="mom-participants">
                            {participants.length === 0 ? (
                                <li>None listed</li>
                            ) : (
                                participants.map((participant, index) => (
                                    <li key={index}>{participant}</li>
                                ))
                            )}
                        </ul>
                    </div>

                    <div className="mom-section">
                        <div className="mom-section-title">II. Minutes / Proceedings:</div>
                        <div className="mom-section-content">
                            {hearing.meeting_minutes ?? 'No details recorded.'}
                        </div>
                    </div>

                    <div className="mom-section">
                        <div className="mom-section-title">III. Notes / Remarks:</div>
                        <div className="mom-section-content">{hearing.notes ?? 'None'}</div>
                    </div>

                    <div className="mom-signatures">
                        <div className="mom-sig-block">
                            <div className="mom-sig-line">{hearing.case?.student?.full_name}</div>
                            <div className="mom-sig-sub">
                                Signature over Printed Name
                                <br />
                                (Student/Guardian)
                            </div>
                        </div>
                        <div className="mom-sig-block">
                            <div className="mom-sig-line">OSA Representative</div>
                            <div className="mom-sig-sub">
                                Signature over Printed Name
                                <br />
                                (Discipline Officer)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </PrintLayout>
    );
}
