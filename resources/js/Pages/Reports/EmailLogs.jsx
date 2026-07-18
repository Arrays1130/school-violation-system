import React, { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import { ArrowLeft, Mail, X, Eye, Inbox } from 'lucide-react';
import { stripHtml } from '@/lib/safeHtml';
import PageMotion, { MotionItem } from '@/Components/PageMotion';
import EmptyState from '@/Components/EmptyState';

export default function EmailLogs({ auth, logs, filters }) {
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState(filters?.search || '');
    const searchInputRef = useRef(null);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters?.search || '')) {
                const el = searchInputRef.current;
                const hadFocus = el && document.activeElement === el;
                const caret = el ? el.selectionStart : null;

                router.get(route('reports.email-logs'), { search }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onFinish: () => {
                        if (hadFocus && searchInputRef.current) {
                            const input = searchInputRef.current;
                            input.focus();
                            if (caret !== null) {
                                try { input.setSelectionRange(caret, caret); } catch (_) {}
                            }
                        }
                    },
                });
            }
        }, 600);
        return () => clearTimeout(timer);
    }, [search]);

    const handleClear = () => {
        setSearch('');
        router.get(route('reports.email-logs'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Email Logs</h2>}
        >
            <Head title="Email Logs" />

            <PageMotion>
                <MotionItem className="vt-page-hero">
                    <div className="relative flex items-center gap-5">
                        <Link href={route('reports.index')} className="w-10 h-10 rounded-xl bg-slate-900/10 border border-slate-600/80 flex items-center justify-center text-white/80 hover:text-white hover:bg-slate-700/80 transition-all">
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900/10 border border-slate-600/80 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2">
                                <Mail className="w-3.5 h-3.5" />
                                System Activity
                            </div>
                            <h2 className="text-2xl font-bold text-white tracking-tight">Email Logs</h2>
                            <p className="text-slate-400 text-xs mt-1.5">Monitor outgoing automated notifications and institutional correspondence.</p>
                        </div>
                    </div>
                </MotionItem>

                <MotionItem>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={filters?.search ? handleClear : undefined}
                        placeholder="Search by recipient email or subject..."
                    />
                </MotionItem>

                <MotionItem className="vt-content-card overflow-hidden">
                        <div className="overflow-x-auto -mx-4 sm:mx-0">
                            <table className="min-w-[640px] w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
                                    {['Date & Time', 'Recipient', 'Subject', 'Status', 'Actions'].map((h, i) => (
                                        <th key={h} className={`px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider ${i >= 3 ? 'text-center' : ''} ${h === 'Actions' ? 'text-right' : ''}`}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                                {logs.data.length > 0 ? logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="text-sm font-semibold text-slate-800 dark:text-slate-100">{log.created_at_date}</div>
                                            <div className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{log.created_at_time}</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-100 dark:border-blue-500/20">
                                                    <Mail className="w-4 h-4" />
                                                </div>
                                                <div className="text-sm font-medium text-slate-800 dark:text-slate-100">{log.recipient}</div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-700 dark:text-slate-300 font-medium">{log.subject}</td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-2.5 py-1 inline-flex text-xs font-medium rounded-md ${log.status === 'sent' ? 'bg-green-100 text-green-800 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-rose-500/10 dark:text-rose-400'}`}>
                                                {log.status?.charAt(0).toUpperCase() + log.status?.slice(1)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <button type="button" onClick={() => setSelected(log)} className="text-indigo-600 dark:text-indigo-400 hover:text-slate-900 dark:hover:text-indigo-300 inline-flex items-center gap-1 text-sm">
                                                <Eye className="w-4 h-4" />
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={5}>
                                            <EmptyState
                                                icon={Inbox}
                                                title="No email logs found"
                                                message="Outgoing notifications will appear here once sent."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {logs.links?.length > 3 && (
                        <div className="bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 px-6 py-4">
                            <Pagination links={logs.links} />
                        </div>
                    )}
                </MotionItem>
            </PageMotion>

            {selected && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onClick={() => setSelected(null)} />
                    <div className="fixed inset-0 z-10 overflow-y-auto flex min-h-full items-center justify-center p-4">
                        <div className="relative w-full max-w-2xl rounded-xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-700">
                            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 flex justify-between items-center">
                                <h3 className="text-lg font-bold text-slate-900 dark:text-white">Email Contents</h3>
                                <button type="button" onClick={() => setSelected(null)} className="text-slate-400 hover:text-gray-600 dark:hover:text-slate-200">
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <div className="px-6 py-6 space-y-4">
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 uppercase mb-1">To</h4>
                                    <p className="text-sm font-medium text-slate-800 dark:text-slate-100">{selected.recipient}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Subject</h4>
                                    <p className="text-sm font-medium text-slate-800 dark:text-slate-100">{selected.subject}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 uppercase mb-2">Message Body</h4>
                                    <div className="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 text-sm text-gray-700 dark:text-slate-300 whitespace-pre-wrap font-mono">{stripHtml(selected.body)}</div>
                                </div>
                            </div>
                            <div className="px-6 py-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 text-right">
                                <button type="button" onClick={() => setSelected(null)} className="px-4 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

