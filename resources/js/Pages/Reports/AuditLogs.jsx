import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import FilterBar, { filterFieldClass, filterLabelClass } from '@/Components/FilterBar';
import Breadcrumbs from '@/Components/Breadcrumbs';
import EmptyState from '@/Components/EmptyState';
import {
    ArrowLeft, ShieldCheck, Download, Eye, X,
    ClipboardList, PlusCircle, Pencil, Trash2, Activity
} from 'lucide-react';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

const eventIcons = {
    'plus-circle': PlusCircle,
    pencil: Pencil,
    'trash-2': Trash2,
    activity: Activity,
};

export default function AuditLogs({ auth, logs, stats, users, subjectTypes, filters }) {
    const [detail, setDetail] = useState(null);
    const [search, setSearch] = useState(filters?.search || '');
    const [event, setEvent] = useState(filters?.event || '');
    const [subjectType, setSubjectType] = useState(filters?.subject_type || '');
    const [causerId, setCauserId] = useState(filters?.causer_id || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    useEffect(() => {
        const timer = setTimeout(() => {
            const next = { search, event, subject_type: subjectType, causer_id: causerId, date_from: dateFrom, date_to: dateTo };
            const current = {
                search: filters?.search || '',
                event: filters?.event || '',
                subject_type: filters?.subject_type || '',
                causer_id: filters?.causer_id || '',
                date_from: filters?.date_from || '',
                date_to: filters?.date_to || '',
            };
            if (JSON.stringify(next) !== JSON.stringify(current)) {
                router.get(route('reports.audit-logs'), next, { preserveState: true, preserveScroll: true, replace: true });
            }
        }, 300);
        return () => clearTimeout(timer);
    }, [search, event, subjectType, causerId, dateFrom, dateTo]);

    const clearFilters = () => {
        setSearch('');
        setEvent('');
        setSubjectType('');
        setCauserId('');
        setDateFrom('');
        setDateTo('');
        router.get(route('reports.audit-logs'));
    };

    const hasFilters = search || event || subjectType || causerId || dateFrom || dateTo;

    const EventIcon = ({ name }) => {
        const Icon = eventIcons[name] || Activity;
        return <Icon className="w-3 h-3" />;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Audit Logs</h2>}
        >
            <Head title="System Audit Logs" />

            <PageMotion>
                <MotionItem>
                    <Breadcrumbs items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'Reports', href: route('reports.index') },
                        { label: 'Audit Logs' },
                    ]} />
                </MotionItem>

                <MotionItem className="vt-page-hero">
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
                </MotionItem>

                <MotionItem className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    {[
                        { label: 'Total (filtered)', value: stats.total, color: 'text-slate-800 dark:text-slate-100' },
                        { label: 'Today', value: stats.today, color: 'text-indigo-600 dark:text-indigo-400' },
                        { label: 'Created', value: stats.created, color: 'text-emerald-700 dark:text-emerald-400' },
                        { label: 'Updated', value: stats.updated, color: 'text-blue-700 dark:text-blue-400' },
                        { label: 'Deleted', value: stats.deleted, color: 'text-red-700 dark:text-red-400' },
                    ].map((s) => (
                        <div key={s.label} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm dark:shadow-[0_2px_12px_-4px_rgba(0,0,0,0.4)] px-4 py-3">
                            <p className={`text-2xl font-black tabular-nums leading-none ${s.color}`}>{Number(s.value).toLocaleString()}</p>
                            <p className="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1.5">{s.label}</p>
                        </div>
                    ))}
                </MotionItem>

                <MotionItem>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onClear={hasFilters ? clearFilters : undefined}
                        placeholder="User, email, record ID..."
                    >
                        <div>
                            <label htmlFor="audit-event" className={filterLabelClass}>Action</label>
                            <select id="audit-event" value={event} onChange={(e) => setEvent(e.target.value)} className={filterFieldClass}>
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="audit-module" className={filterLabelClass}>Module</label>
                            <select id="audit-module" value={subjectType} onChange={(e) => setSubjectType(e.target.value)} className={filterFieldClass}>
                                <option value="">All Modules</option>
                                {Object.entries(subjectTypes).map(([type, label]) => (
                                    <option key={type} value={type}>{label}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label htmlFor="audit-user" className={filterLabelClass}>User</label>
                            <select id="audit-user" value={causerId} onChange={(e) => setCauserId(e.target.value)} className={filterFieldClass}>
                                <option value="">All Users</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>{user.name} ({user.role?.replace('_', ' ')})</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label htmlFor="audit-date-from" className={filterLabelClass}>From</label>
                            <input id="audit-date-from" type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className={filterFieldClass} />
                        </div>
                        <div>
                            <label htmlFor="audit-date-to" className={filterLabelClass}>To</label>
                            <input id="audit-date-to" type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className={filterFieldClass} />
                        </div>
                    </FilterBar>
                </MotionItem>

                <MotionItem className="bg-white dark:bg-slate-900 rounded-2xl ring-1 ring-slate-100 dark:ring-slate-800 shadow-sm dark:shadow-[0_2px_12px_-4px_rgba(0,0,0,0.4)] overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left">
                            <thead>
                                <tr className="bg-gray-50/60 dark:bg-slate-800/60">
                                    {['Timestamp', 'User', 'Action', 'Module', 'Summary', 'Details'].map((h) => (
                                        <th key={h} className={`px-6 py-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider ${h === 'Details' ? 'text-right' : ''}`}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 text-sm">
                                {logs.data.length > 0 ? logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="font-semibold text-slate-800 dark:text-slate-100">{log.created_at_date}</div>
                                            <div className="text-[11px] text-slate-500 dark:text-slate-400">{log.created_at_time}</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-100 dark:border-indigo-500/20">{log.causer_initial}</div>
                                                <div>
                                                    <div className="font-medium text-slate-800 dark:text-slate-100">{log.causer_name}</div>
                                                    <div className="text-xs text-slate-400 dark:text-slate-500">{log.causer_email}</div>
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
                                            <div className="font-medium text-slate-800 dark:text-slate-100">{log.subject_label}</div>
                                            {log.subject_id && (
                                                log.subject_url ? (
                                                    <Link href={log.subject_url} className="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">#{log.subject_id} →</Link>
                                                ) : (
                                                    <span className="text-xs text-slate-400 dark:text-slate-500">#{log.subject_id}</span>
                                                )
                                            )}
                                        </td>
                                        <td className="px-6 py-4 max-w-xs">
                                            {log.has_changes ? (
                                                <p className="text-slate-400 dark:text-slate-500 text-xs leading-relaxed">{log.change_summary}</p>
                                            ) : (
                                                <span className="text-slate-400 dark:text-slate-500 italic text-xs">{log.description || 'No field changes'}</span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right whitespace-nowrap">
                                            {log.detail ? (
                                                <button type="button" onClick={() => setDetail(log.detail)} className="inline-flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium text-xs">
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
                                        <td colSpan={6}>
                                            <EmptyState
                                                icon={ClipboardList}
                                                title="No audit logs found"
                                                message="Try adjusting your filters or check back after system activity."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {logs.links?.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50">
                            <Pagination links={logs.links} />
                        </div>
                    )}
                </MotionItem>
            </PageMotion>

            {detail && (
                <div className="fixed inset-0 z-50 overflow-y-auto px-4 py-6" onKeyDown={(e) => e.key === 'Escape' && setDetail(null)}>
                    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={() => setDetail(null)} />
                    <div className="relative max-w-2xl mx-auto bg-white dark:bg-slate-900 rounded-2xl shadow-2xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden mt-8">
                        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-slate-50 to-indigo-50/50 dark:from-slate-800 dark:to-slate-800">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <p className="text-[10px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-1">Audit Entry</p>
                                    <h3 className="text-lg font-bold text-slate-900 dark:text-white">#{detail.id}</h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">{detail.timestamp}</p>
                                </div>
                                <button type="button" onClick={() => setDetail(null)} className="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div className="px-6 py-4 grid grid-cols-2 gap-4 border-b border-slate-100 dark:border-slate-800 text-sm">
                            <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase">User</p>
                                <p className="font-semibold text-slate-800 dark:text-slate-100 mt-0.5">{detail.user}</p>
                                <p className="text-xs text-slate-400 dark:text-slate-500">{detail.email}</p>
                            </div>
                            <div>
                                <p className="text-[10px] font-bold text-slate-400 uppercase">Action / Module</p>
                                <p className="font-semibold text-slate-800 dark:text-slate-100 mt-0.5 capitalize">{detail.event}</p>
                                <p className="text-xs text-slate-500 dark:text-slate-400">{detail.module}{detail.recordId ? ` #${detail.recordId}` : ''}</p>
                            </div>
                        </div>
                        <div className="px-6 py-5 max-h-[50vh] overflow-y-auto">
                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Field Changes</p>
                            <div className="space-y-3">
                                {detail.fields?.map((field) => (
                                    <div key={field.field} className="rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                                        <div className="px-3 py-2 bg-slate-100 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                                            <span className="text-xs font-bold text-slate-600 dark:text-slate-300">{field.label}</span>
                                        </div>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-700">
                                            <div className="px-3 py-2.5">
                                                <p className="text-[10px] font-bold text-red-500 uppercase mb-1">Before</p>
                                                <p className="text-xs text-slate-600 dark:text-slate-300 break-words">{field.old ?? '—'}</p>
                                            </div>
                                            <div className="px-3 py-2.5 bg-emerald-50/30 dark:bg-emerald-900/10">
                                                <p className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-1">After</p>
                                                <p className="text-xs text-slate-600 dark:text-slate-300 break-words">{field.new ?? '—'}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                            <button type="button" onClick={() => setDetail(null)} className="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

