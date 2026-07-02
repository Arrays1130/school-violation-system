import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck, Edit3, Hash, Layers, AlertTriangle, Info } from 'lucide-react';

export default function Show({ auth, violation }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Rule</h2>}
        >
            <Head title={violation.code} />

            <div className="max-w-3xl mx-auto space-y-6 py-8 px-4 sm:px-6 lg:px-8">
                <div className="vt-page-hero">
                    <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link href={route('violations.index')} className="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all">
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="vt-hero-chip mb-2">
                                    <ShieldCheck className="w-3.5 h-3.5" />
                                    {violation.code}
                                </div>
                                <h1 className="text-2xl font-extrabold text-white tracking-tight">{violation.title}</h1>
                            </div>
                        </div>
                        <Link href={route('violations.edit', violation.id)} className="vt-hero-btn">
                            <Edit3 className="w-4 h-4" />
                            Edit Rule
                        </Link>
                    </div>
                </div>

                <div className="vt-content-card overflow-hidden">
                    <div className="p-8 space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div className="flex items-start gap-3">
                                <Hash className="w-5 h-5 text-slate-400 mt-0.5" />
                                <div>
                                    <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Code</p>
                                    <p className="text-sm font-bold text-slate-900 mt-1">{violation.code}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <Layers className="w-5 h-5 text-slate-400 mt-0.5" />
                                <div>
                                    <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Category</p>
                                    <p className="text-sm font-bold text-slate-900 mt-1">{violation.category}</p>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                            {violation.severity === 'Major' ? (
                                <AlertTriangle className="w-5 h-5 text-rose-500" />
                            ) : (
                                <Info className="w-5 h-5 text-emerald-500" />
                            )}
                            <div>
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Severity</p>
                                <p className="text-sm font-bold text-slate-900">{violation.severity}</p>
                            </div>
                        </div>

                        {violation.default_description && (
                            <div>
                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Standard Sanction / Description</p>
                                <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{violation.default_description}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
