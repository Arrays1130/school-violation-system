import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck, Clock, Edit2, Printer, FileCheck2, FileText, ExternalLink } from 'lucide-react';

export default function Show({ auth, handbook }) {
    const updatedDate = new Date(handbook.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">{handbook.title}</h2>}
        >
            <Head title={handbook.title} />

            <div className="space-y-8 max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link href={route('handbooks.index')} className="w-10 h-10 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0">
                                <ArrowLeft className="w-4 h-4" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                                    <ShieldCheck className="w-3.5 h-3.5 text-indigo-400" />
                                    Institutional Policy
                                </div>
                                <h2 className="text-2xl font-bold text-white tracking-tight leading-tight max-w-xl">{handbook.title}</h2>
                                <div className="flex items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                                    <span className="flex items-center gap-1.5">
                                        <Clock className="w-4 h-4 text-indigo-400" />
                                        Updated {updatedDate}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-3 shrink-0">
                            <Link href={route('handbooks.edit', handbook.id)}
                                className="inline-flex items-center px-4 py-2.5 bg-white/10 dark:bg-slate-900/10 border border-white/10 rounded-xl text-xs font-bold text-white hover:bg-white/20 dark:bg-slate-900/20 shadow-sm backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5">
                                <Edit2 className="w-4 h-4 mr-1.5 text-amber-400" />
                                Edit Policy
                            </Link>
                            <button onClick={() => window.print()}
                                    className="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                                <Printer className="w-4 h-4 mr-1.5" />
                                Print Policy
                            </button>
                        </div>
                    </div>
                </div>

                {/* Official Policy Document Card */}
                <div className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden border-l-4 border-indigo-600">
                    
                    {/* Document Narrative Content */}
                    <div className="p-8 md:p-10 space-y-8 bg-white dark:bg-slate-900">
                        <div className="prose max-w-none text-slate-700 dark:text-slate-300 leading-relaxed text-sm font-medium space-y-4 whitespace-pre-wrap pl-6 border-l-2 border-indigo-100 font-serif italic text-base">
                            {handbook.content}
                        </div>

                        {/* Attachment Panel */}
                        {handbook.attachment && (
                            <div className="pt-8 border-t border-gray-100 dark:border-slate-800 space-y-4">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <FileCheck2 className="w-4.5 h-4.5 text-indigo-500 animate-pulse" />
                                    Reference Documents
                                </h3>
                                <div className="bg-slate-50/50 dark:bg-slate-800/50 border border-gray-150 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div className="flex items-start gap-4">
                                        <div className="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <FileText className="w-6.5 h-6.5 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <div>
                                            <h4 className="text-sm font-bold text-slate-800 dark:text-slate-200">Policy Document</h4>
                                            <p className="text-xs text-slate-400 mt-1 font-medium">Portable Document Format (.pdf)</p>
                                        </div>
                                    </div>
                                    <a href={handbook.attachment} target="_blank" rel="noreferrer" className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all duration-200 hover:-translate-y-0.5 shrink-0">
                                        <ExternalLink className="w-4 h-4" />
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Policy Authentication Footer */}
                    <div className="px-8 py-5 bg-slate-50 dark:bg-slate-800 border-t border-gray-150 flex items-center justify-between text-slate-400">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="w-4.5 h-4.5 text-indigo-500" />
                            <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Officially Approved Policy Guideline</span>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
