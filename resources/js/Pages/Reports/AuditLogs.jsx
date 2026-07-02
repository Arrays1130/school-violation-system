import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import {
    ArrowLeft, ShieldCheck, Download, Search, Filter, X, Eye,
    ClipboardList, PlusCircle, Pencil, Trash2, Activity
} from 'lucide-react';

const eventIcons = {
    'plus-circle': PlusCircle,
    pencil: Pencil,
    'trash-2': Trash2,
    activity: Activity,
};

export default function AuditLogs({ auth, logs, stats, users, subjectTypes, filters }) {
    const [detail, setDetail] = useState(null);

    const applyFilters = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('reports.audit-logs'), Object.fromEntries(form), { preserveState: true, preserveScroll: true });
    };

    const clearFilters = () => router.get(route('reports.audit-logs'));

    const hasFilters = Object.values(filters || {}).some((v) => v);

    const EventIcon = ({ name }) => {
        const Icon = eventIcons[name] || Activity;
        return <Icon className="w-3 h-3" />;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-800 leading-tight">Audit Logs</h2>}
        >
            <Head title="System Audit Logs" />

            <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div className="vt-page-hero">
                    <div className="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div className="flex items-start gap-4">
                            <Link href={route('reports.index')} className="mt-1 w-10 h-10 rounded-xl bg-slate-900/10 border border-slate-600/80 flex items-center justify-center text-white/80 hover:text-white hover:bg-slate-700/80 transition-all">
                                <ArrowLeft className="w-4 h-4" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/10 border border-slate-600/80 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2">
                                    <ShieldCheck className="w-3.5 h-3.5" />
                                    Security & Accountability
                                </div>
                                <h1 className="text-3xl font-extrabold text-white tracking-tight">System Audit Logs</h1>
                                <p className="text-slate-400 text-sm mt-1 max-w-2xl">Track modifications to sensitive student records, system configuration, and user roles.</p>
                            </div>
                        </div>
                        <a
                            href={route('reports.audit-logs.export', filters)}
                            className="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900/10 border border-slate-600/80 text-white rounded-xl text-sm font-bold hover:bg-slate-700/80 transition-all shrink-0"
                        >
                            <Download className="w-4 h-4 text-emerald-400" />
                            Export CSV
                        </a>
                    </div>
                </div>

                <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                    {[
                        { label: 'Total (filtered)', value: stats.total, color: 'text-slate-800' },
                        { label: 'Today', value: stats.today, color: 'text-indigo-600' },
                        { label: 'Created', value: stats.created, color: 'text-emerald-700' },
                        { label: 'Updated', value: stats.updated, color: 'text-blue-700' },
                        { label: 'Deleted', value: stats.deleted, color: 'text-red-700' },
                    ].map((s) => (
                        <div key={s.label} className="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{s.label}</p>
                            <p className={`text-2xl font-extrabold mt-1 ${s.color}`}>{Number(s.value).toLocaleString()}</p>
                        </div>
                    ))}
                </div>

                <div className="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                    <form onSubmit={applyFilters} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                        <div className="lg:col-span-3">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">Search</label>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input type="text" name="search" defaultValue={filters?.search || ''} placeholder="User, email, record ID..." className="w-full pl-9 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm" />
                            </div>
                        </div>
                        <div className="lg:col-span-2">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">Action</label>
                            <select name="event" defaultValue={filters?.event || ''} className="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm">
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                        <div className="lg:col-span-2">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">Module</label>
                            <select name="subject_type" defaultValue={filters?.subject_type || ''} className="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm">
                                <option value="">All Modules</option>
                                {Object.entries(subjectTypes).map(([type, label]) => (
                                    <option key={type} value={type}>{label}</option>
                                ))}
                            </select>
                        </div>
                        <div className="lg:col-span-2">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">User</label>
                            <select name="causer_id" defaultValue={filters?.causer_id || ''} className="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm">
                                <option value="">All Users</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>{user.name} ({user.role?.replace('_', ' ')})</option>
                                ))}
                            </select>
                        </div>
                        <div className="lg:col-span-1">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">From</label>
                            <input type="date" name="date_from" defaultValue={filters?.date_from || ''} className="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm" />
                        </div>
                        <div className="lg:col-span-1">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">To</label>
                            <input type="date" name="date_to" defaultValue={filters?.date_to || ''} className="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm" />
                        </div>
                        <div className="lg:col-span-1 flex gap-2">
                            <button type="submit" className="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg">
                                <Filter className="w-4 h-4" />
                                Filter
                            </button>
                            {hasFilters && (
                                <button type="button" onClick={clearFilters} className="inline-flex items-center justify-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg border border-gray-300">
                                    <X className="w-4 h-4" />
                                </button>
                            )}
                        </div>
                    </form>
                </div>

                <div className="bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-150 text-left">
                            <thead>
                                <tr className="bg-gray-50/60 dark:bg-slate-800/60">
                                    {['Timestamp', 'User', 'Action', 'Module', 'Summary', 'Details'].map((h) => (
                                        <th key={h} className={`px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider ${h === 'Details' ? 'text-right' : ''}`}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white text-sm">
                                {logs.data.length > 0 ? logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="font-semibold text-slate-800">{log.created_at_date}</div>
                                            <div className="text-[11px] text-slate-500">{log.created_at_time}</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-100">{log.causer_initial}</div>
                                                <div>
                                                    <div className="font-medium text-slate-800">{log.causer_name}</div>
                                                    <div className="text-xs text-slate-400">{log.causer_email}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 uppercase ${log.event_color}`}>
                                                <EventIcon name={log.event_icon} />
                                                {log.event}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="font-medium text-slate-800">{log.subject_label}</div>
                                            {log.subject_id && (
                                                log.subject_url ? (
                                                    <Link href={log.subject_url} className="text-xs text-indigo-600 hover:text-indigo-900 font-medium">#{log.subject_id} →</Link>
                                                ) : (
                                                    <span className="text-xs text-slate-400">#{log.subject_id}</span>
                                                )
                                            )}
                                        </td>
                                        <td className="px-6 py-4 max-w-xs">
                                            {log.has_changes ? (
                                                <p className="text-slate-400 text-xs leading-relaxed">{log.change_summary}</p>
                                            ) : (
                                                <span className="text-slate-400 italic text-xs">{log.description || 'No field changes'}</span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right whitespace-nowrap">
                                            {log.detail ? (
                                                <button type="button" onClick={() => setDetail(log.detail)} className="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                                                    <Eye className="w-3.5 h-3.5" />
                                                    View
                                                </button>
                                            ) : (
                                                <span className="text-slate-300 text-xs">—</span>
                                            )}
                                        </td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-16 text-center text-slate-500">
                                            <ClipboardList className="w-10 h-10 text-gray-300 mx-auto mb-3" />
                                            <p className="font-medium">No audit logs found</p>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {logs.links?.length > 3 && (
                        <div className="px-6 py-4 border-t border-gray-150 bg-gray-50/50 dark:bg-slate-800/50">
                            <Pagination links={logs.links} />
                        </div>
                    )}
                </div>
            </div>

            {detail && (
                <div className="fixed inset-0 z-50 overflow-y-auto px-4 py-6" onKeyDown={(e) => e.key === 'Escape' && setDetail(null)}>
                    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={() => setDetail(null)} />
                    <div className="relative max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 overflow-hidden mt-8">
                        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-indigo-50/50 dark:from-slate-800 dark:to-slate-800">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <p className="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-1">Audit Entry</p>
                                    <h3 className="text-lg font-bold text-slate-100">#{detail.id}</h3>
                                    <p className="text-xs text-slate-500 mt-1">{detail.timestamp}</p>
                                </div>
                                <button type="button" onClick={() => setDetail(null)} className="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div className="px-6 py-4 grid grid-cols-2 gap-4 border-b border-slate-100 dark:border-slate-700 text-sm">
                            <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase">User</p>
                                <p className="font-semibold text-slate-800 mt-0.5">{detail.user}</p>
                                <p className="text-xs text-slate-400">{detail.email}</p>
                            </div>
                            <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase">Action / Module</p>
                                <p className="font-semibold text-slate-800 mt-0.5 capitalize">{detail.event}</p>
                                <p className="text-xs text-slate-500">{detail.module}{detail.recordId ? ` #${detail.recordId}` : ''}</p>
                            </div>
                        </div>
                        <div className="px-6 py-5 max-h-[50vh] overflow-y-auto">
                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Field Changes</p>
                            <div className="space-y-3">
                                {detail.fields?.map((field) => (
                                    <div key={field.field} className="rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                                        <div className="px-3 py-2 bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                                            <span className="text-xs font-bold text-slate-300">{field.label}</span>
                                        </div>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-700">
                                            <div className="px-3 py-2.5">
                                                <p className="text-[10px] font-bold text-red-500 uppercase mb-1">Before</p>
                                                <p className="text-xs text-slate-300 break-words">{field.old ?? '—'}</p>
                                            </div>
                                            <div className="px-3 py-2.5 bg-emerald-50/30 dark:bg-emerald-900/10">
                                                <p className="text-[10px] font-bold text-emerald-600 uppercase mb-1">After</p>
                                                <p className="text-xs text-slate-300 break-words">{field.new ?? '—'}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="px-6 py-4 bg-slate-800 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                            <button type="button" onClick={() => setDetail(null)} className="px-4 py-2 bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

