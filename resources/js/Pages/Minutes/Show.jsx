import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit2, Printer, UserCheck, FileText, Calendar, MapPin, RefreshCw } from 'lucide-react';

export default function Show({ auth, meetingMinute }) {
    
    const meetingDateObj = new Date(meetingMinute.meeting_date);
    const meetingDateStr = isNaN(meetingDateObj) ? 'N/A' : meetingDateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    const meetingTimeStr = isNaN(meetingDateObj) ? 'N/A' : meetingDateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{meetingMinute.title}</h2>}
        >
            <Head title={meetingMinute.title} />

            <div className="max-w-4xl mx-auto space-y-8 py-8 px-4 sm:px-6 lg:px-8">
                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link href={route('meeting-minutes.index')} className="w-10 h-10 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0">
                                <ArrowLeft className="w-4 h-4" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                                    Official Record #{meetingMinute.id}
                                </div>
                                <h2 className="text-2xl font-bold text-white tracking-tight leading-tight">{meetingMinute.title}</h2>
                                <div className="flex flex-wrap items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                                    <span className="flex items-center gap-1.5">
                                        <Calendar className="w-4 h-4 text-indigo-400" />
                                        {meetingDateStr} • {meetingTimeStr}
                                    </span>
                                    <span className="flex items-center gap-1.5">
                                        <MapPin className="w-4 h-4 text-indigo-400" />
                                        {meetingMinute.venue}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-3 shrink-0">
                            <Link href={route('meeting-minutes.edit', meetingMinute.id)}
                               className="inline-flex items-center px-4 py-2.5 bg-white/10 dark:bg-slate-900/10 border border-white/10 rounded-xl text-xs font-bold text-white hover:bg-white/20 dark:bg-slate-900/20 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5">
                                <Edit2 className="w-4 h-4 mr-1.5 text-amber-400" />
                                Edit Record
                            </Link>
                            <button onClick={() => window.print()}
                                    className="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                                <Printer className="w-4 h-4 mr-1.5" />
                                Print Minutes
                            </button>
                        </div>
                    </div>
                </div>

                {/* Main Content Card */}
                <div className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden border-l-4 border-indigo-600">
                    <div className="p-8 space-y-8">

                        {/* Associated Case */}
                        {meetingMinute.case && (
                            <div className="bg-slate-50/50 dark:bg-slate-800/50 rounded-2xl border border-gray-150 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center border border-gray-200 dark:border-slate-700 shadow-sm">
                                        <UserCheck className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Involved Student</p>
                                        <h4 className="text-base font-extrabold text-slate-800 dark:text-slate-200 capitalize mt-1.5">{meetingMinute.case.student?.full_name || meetingMinute.case.student?.first_name + ' ' + meetingMinute.case.student?.last_name}</h4>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="px-3.5 py-1.5 bg-indigo-600/10 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-150">
                                        Case #{meetingMinute.case.id}
                                    </span>
                                    <span className="text-xs font-semibold text-slate-500 dark:text-slate-400 font-mono">{meetingMinute.case.student?.student_id}</span>
                                </div>
                            </div>
                        )}

                        {/* Minutes Content */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <FileText className="w-4.5 h-4.5" />
                                </div>
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Proceedings & Official Findings</h3>
                            </div>
                            
                            <div className="bg-slate-50/30 dark:bg-slate-800/30 rounded-2xl border border-slate-100 dark:border-slate-800 p-8">
                                <div className="whitespace-pre-wrap text-slate-700 dark:text-slate-300 leading-relaxed text-base border-l-2 border-indigo-200 pl-6 font-serif italic">
                                    {meetingMinute.content}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="bg-slate-50 dark:bg-slate-800 px-8 py-5 border-t border-gray-150 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-full bg-slate-800 border border-slate-750 flex items-center justify-center text-white font-extrabold text-xs">
                                {(meetingMinute.creator?.name || 'S').substring(0, 1).toUpperCase()}
                            </div>
                            <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none">Recorded By</p>
                                <p className="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1">{meetingMinute.creator?.name || 'System Administrator'}</p>
                            </div>
                        </div>
                        <p className="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <RefreshCw className="w-3.5 h-3.5" />
                            Last updated {new Date(meetingMinute.updated_at).toLocaleString()}
                        </p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
