import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import {
    ArrowLeft,
    ShieldCheck,
    Edit3,
    Users,
    Eye,
    GraduationCap,
} from 'lucide-react';

export default function Show({ auth, violation, cases }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Rule</h2>}
        >
            <Head title={violation.title} />

            <div className="space-y-6 py-8 px-4 sm:px-6 lg:px-8">
                <div className="vt-page-hero">
                    <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link
                                href={route('violations.index')}
                                className="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all"
                            >
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="vt-hero-chip mb-2">
                                    <ShieldCheck className="w-3.5 h-3.5" />
                                    {violation.code}
                                </div>
                                <h1 className="text-2xl font-extrabold text-white tracking-tight">{violation.title}</h1>
                                <p className="text-indigo-100/70 text-sm mt-1.5">
                                    {violation.category} · {violation.severity} · {cases.total} student{cases.total === 1 ? '' : 's'}
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-3">
                            <Link href={route('violations.edit', violation.id)} className="vt-hero-btn">
                                <Edit3 className="w-4 h-4" />
                                Edit Rule
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <Users className="w-4 h-4 text-indigo-500" />
                        <h2 className="text-sm font-bold text-slate-800 dark:text-slate-200">
                            Students with this violation
                        </h2>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Date / Status</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Student</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Sanction</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800">
                                {cases.data.length > 0 ? (
                                    cases.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-col gap-1.5">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">
                                                        {item.occurred_at
                                                            ? new Date(item.occurred_at).toLocaleDateString('en-US', {
                                                                  month: 'short',
                                                                  day: 'numeric',
                                                                  year: 'numeric',
                                                              })
                                                            : '—'}
                                                    </span>
                                                    <div>
                                                        <StatusBadge item={item} variant="compact" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">
                                                        {item.student?.full_name ?? 'Unknown student'}
                                                    </span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {item.student?.department}
                                                        {item.student?.section ? ` — ${item.student.section}` : ''}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="text-sm text-slate-700 dark:text-slate-300">
                                                    {item.sanction || '—'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">
                                                <Link
                                                    href={route('cases.show', item.id)}
                                                    className="inline-flex p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all"
                                                    title="View Case Details"
                                                    aria-label={`View case for ${item.student?.full_name ?? 'student'}`}
                                                >
                                                    <Eye className="w-4 h-4" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4">
                                            <EmptyState
                                                icon={GraduationCap}
                                                title="No students yet"
                                                message="No recorded cases for this violation."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {cases.links && cases.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={cases.links} />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
