import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Gavel, Edit3, ShieldAlert, Calendar, MapPin, Users, Quote, FileCheck2, Printer, FileEdit } from 'lucide-react';

export default function Show({ auth, hearing }) {
    
    // Format dates
    const scheduledDateObj = new Date(hearing.scheduled_at);
    const scheduledDateStr = isNaN(scheduledDateObj) ? 'N/A' : scheduledDateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const scheduledTimeStr = isNaN(scheduledDateObj) ? 'N/A' : scheduledDateObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Hearing Details</h2>}
        >
            <Head title={`Hearing - Case #${hearing.case.id}`} />

            <div className="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                {/* Breadcrumb & Actions Panel */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div className="flex items-center gap-5">
                            <Link href={route('cases.show', hearing.case.id)} className="w-12 h-12 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                                <ArrowLeft className="w-5.5 h-5.5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                    <Gavel className="w-3.5 h-3.5" />
                                    Hearing Summary
                                </div>
                                <h2 className="text-3xl font-bold text-white tracking-tight">Violation Hearing</h2>
                                <p className="text-indigo-100/70 text-sm mt-1">
                                    Accused Student: <span className="text-white font-medium">{hearing.case.student?.full_name || hearing.case.student?.first_name + ' ' + hearing.case.student?.last_name}</span>
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <Link href={route('hearings.edit', hearing.id)} className="px-5 py-2.5 bg-white/10 dark:bg-slate-900/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 dark:bg-slate-900/20 transition-all flex items-center gap-2">
                                <Edit3 className="w-4.5 h-4.5" />
                                Edit Hearing Setup
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Official Digital Document Card */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    
                    {/* Document Header */}
                    <div className="bg-white dark:bg-slate-900 px-8 py-8 border-b border-gray-100 dark:border-slate-800">
                        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
                            <div className="space-y-3">
                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                                    <ShieldAlert className="w-3.5 h-3.5" />
                                    Hearing Record
                                </span>
                                <h1 className="text-2xl font-semibold text-slate-800 dark:text-slate-200 tracking-tight">
                                    Violation Board Hearing
                                </h1>
                                <div className="flex flex-wrap items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                                    <span className="uppercase tracking-wider font-bold">Ref ID: HR-{hearing.id}</span>
                                    <div className="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                                    <span className="font-medium">Case Violation: {hearing.case.violation?.title}</span>
                                </div>
                            </div>
                            
                            <div className="text-left md:text-right">
                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-md text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                                    <Calendar className="w-3 h-3" /> Scheduled Time
                                </span>
                                <p className="text-xl font-bold text-slate-800 dark:text-slate-200 tracking-tight">{scheduledDateStr}</p>
                                <p className="text-sm font-bold text-slate-500 dark:text-slate-400 mt-0.5">{scheduledTimeStr}</p>
                            </div>
                        </div>
                    </div>

                    {/* Document Details Block */}
                    <div className="p-8 space-y-8 bg-white dark:bg-slate-900">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-2">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <MapPin className="w-4 h-4 text-indigo-500" />
                                    Venue / Room
                                </h3>
                                <div className="p-4 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-bold text-gray-900 dark:text-white shadow-sm">
                                    {hearing.venue}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <Users className="w-4 h-4 text-purple-500" />
                                    Accompanying Participants
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {hearing.participants && hearing.participants.length > 0 ? (
                                        hearing.participants.map((participant, i) => (
                                            <span key={i} className="inline-flex items-center px-3.5 py-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-300 shadow-sm">
                                                {participant}
                                            </span>
                                        ))
                                    ) : (
                                        <span className="text-slate-400 text-sm font-medium">None specified</span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {hearing.notes && (
                            <div className="space-y-2">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <Quote className="w-4 h-4 text-pink-500" />
                                    Hearing Protocol Notes
                                </h3>
                                <div className="p-5 bg-indigo-50 dark:bg-indigo-900/20/40 rounded-xl border border-indigo-100 text-sm text-indigo-900/80 leading-relaxed italic relative font-medium">
                                    <div className="absolute right-4 top-3 text-indigo-200">
                                        <Quote className="w-8 h-8 opacity-40" />
                                    </div>
                                    "{hearing.notes}"
                                </div>
                            </div>
                        )}

                        {/* Minutes of the Meeting */}
                        <div className="pt-8 border-t border-gray-100 dark:border-slate-800 space-y-5">
                            <div className="flex items-center justify-between">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <FileCheck2 className="w-4 h-4 text-emerald-500" />
                                    Minutes of the Meeting (MOM)
                                </h3>
                                {hearing.meeting_minutes && (
                                    <a href={route('hearings.print-mom', hearing.id)} target="_blank" rel="noreferrer" className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-sm shadow-indigo-600/20 transition-all duration-200">
                                        <Printer className="w-4 h-4" />
                                        Print Record
                                    </a>
                                )}
                            </div>

                            {hearing.meeting_minutes ? (
                                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm relative group">
                                    <div className="text-sm text-gray-800 dark:text-slate-200 leading-relaxed whitespace-pre-wrap font-medium">
                                        {hearing.meeting_minutes}
                                    </div>
                                </div>
                            ) : (
                                <div className="py-16 bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 flex flex-col items-center justify-center text-center shadow-sm">
                                    <div className="w-12 h-12 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 flex items-center justify-center mb-4 shadow-sm text-slate-400">
                                        <FileEdit className="w-5 h-5" />
                                    </div>
                                    <h4 className="text-sm font-semibold text-slate-800 dark:text-slate-200">No Minutes Recorded</h4>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1 max-w-sm leading-relaxed">Minutes of this official hearing session have not yet been transcribed. Click below to add documentation.</p>
                                    <Link href={route('hearings.edit', hearing.id)} className="mt-5 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition-all duration-200">
                                        Add Meeting Minutes
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
