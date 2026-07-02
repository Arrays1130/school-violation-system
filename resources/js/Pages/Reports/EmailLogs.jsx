import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Pagination from '@/Components/Pagination';
import { ArrowLeft, Mail, Search, X, Eye, Inbox } from 'lucide-react';

export default function EmailLogs({ auth, logs, filters }) {
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState(filters?.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('reports.email-logs'), { search }, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-800 leading-tight">Email Logs</h2>}
        >
            <Head title="Email Logs" />

            <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="vt-page-hero">
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
                </div>

                <div className="bg-white rounded-lg p-5 mb-6 shadow-sm border border-slate-200">
                    <form onSubmit={handleSearch} className="flex flex-col md:flex-row gap-4 items-end">
                        <div className="flex-1 w-full">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 uppercase mb-2">Search Recipient</label>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by recipient email or subject..."
                                    className="w-full pl-9 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm"
                                />
                            </div>
                        </div>
                        <div className="flex gap-3">
                            <button type="submit" className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg">
                                <Search className="w-4 h-4" />
                                Search
                            </button>
                            {filters?.search && (
                                <Link href={route('reports.email-logs')} className="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg border border-gray-300">
                                    <X className="w-4 h-4" />
                                </Link>
                            )}
                        </div>
                    </form>
                </div>

                <div className="vt-content-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
                                    {['Date & Time', 'Recipient', 'Subject', 'Status', 'Actions'].map((h, i) => (
                                        <th key={h} className={`px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider ${i >= 3 ? 'text-center' : ''} ${h === 'Actions' ? 'text-right' : ''}`}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-slate-700">
                                {logs.data.length > 0 ? logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="text-sm font-semibold text-slate-800">{log.created_at_date}</div>
                                            <div className="text-xs text-slate-500 mt-0.5">{log.created_at_time}</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                                    <Mail className="w-4 h-4" />
                                                </div>
                                                <div className="text-sm font-medium text-slate-800">{log.recipient}</div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-700 dark:text-slate-300 font-medium">{log.subject}</td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-2.5 py-1 inline-flex text-xs font-medium rounded-md ${log.status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {log.status?.charAt(0).toUpperCase() + log.status?.slice(1)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <button type="button" onClick={() => setSelected(log)} className="text-indigo-600 hover:text-slate-900 inline-flex items-center gap-1 text-sm">
                                                <Eye className="w-4 h-4" />
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-slate-500">
                                            <Inbox className="w-8 h-8 text-gray-300 mx-auto mb-3" />
                                            <p>No email logs found.</p>
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
                </div>
            </div>

            {selected && (
                <div className="fixed inset-0 z-50">
                    <div className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onClick={() => setSelected(null)} />
                    <div className="fixed inset-0 z-10 overflow-y-auto flex min-h-full items-center justify-center p-4">
                        <div className="relative w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200">
                            <div className="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <h3 className="text-lg font-bold text-slate-100">Email Contents</h3>
                                <button type="button" onClick={() => setSelected(null)} className="text-slate-400 hover:text-gray-600">
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <div className="px-6 py-6 space-y-4">
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 uppercase mb-1">To</h4>
                                    <p className="text-sm font-medium text-slate-800">{selected.recipient}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 uppercase mb-1">Subject</h4>
                                    <p className="text-sm font-medium text-slate-800">{selected.subject}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-500 uppercase mb-2">Message Body</h4>
                                    <div className="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 text-sm text-gray-700 dark:text-slate-300 whitespace-pre-wrap font-mono" dangerouslySetInnerHTML={{ __html: selected.body }} />
                                </div>
                            </div>
                            <div className="px-6 py-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 text-right">
                                <button type="button" onClick={() => setSelected(null)} className="px-4 py-2 bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50">
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

