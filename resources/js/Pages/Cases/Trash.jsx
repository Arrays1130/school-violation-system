import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TrashTabs from '@/Components/TrashTabs';
import Pagination from '@/Components/Pagination';
import ConfirmDialog from '@/Components/ConfirmDialog';
import PageMotion, { MotionItem } from '@/Components/PageMotion';
import Breadcrumbs from '@/Components/Breadcrumbs';
import EmptyState from '@/Components/EmptyState';
import { Head, router } from '@inertiajs/react';
import {
    Trash2, Archive, RotateCcw, Clock, FileX,
} from 'lucide-react';
import dayjs from 'dayjs';

export default function Trash({ auth, cases }) {
    const [confirmAction, setConfirmAction] = useState(null);
    const [emptyingTrash, setEmptyingTrash] = useState(false);
    const canEmptyTrash = auth.user?.role === 'super_admin';

    const handleRestore = (id) => {
        setConfirmAction({ type: 'restore', id });
    };

    const handleForceDelete = (id) => {
        setConfirmAction({ type: 'delete', id });
    };

    const handleEmptyTrash = () => {
        setConfirmAction({ type: 'empty-trash' });
    };

    const handleConfirm = () => {
        if (!confirmAction) return;
        if (confirmAction.type === 'restore') {
            router.post(route('cases.restore', confirmAction.id));
            setConfirmAction(null);
        } else if (confirmAction.type === 'empty-trash') {
            setEmptyingTrash(true);
            router.delete(route('cases.empty-trash'), {
                onFinish: () => {
                    setEmptyingTrash(false);
                    setConfirmAction(null);
                },
            });
        } else {
            router.delete(route('cases.force-delete', confirmAction.id));
            setConfirmAction(null);
        }
    };

    const dialogProps = confirmAction?.type === 'restore'
        ? {
            title: 'Restore Violation Record?',
            description: "This record will be added back to the student's violation history.",
            confirmLabel: 'Yes, Restore Record',
            destructive: false,
        }
        : confirmAction?.type === 'empty-trash'
            ? {
                title: 'Empty trash permanently?',
                description: 'This cannot be undone. Every case in the Trash Bin will be erased forever.',
                confirmLabel: 'Yes, empty trash',
                destructive: true,
            }
            : {
                title: 'Permanently Delete Record?',
                description: 'This cannot be undone. The violation record will be erased forever.',
                confirmLabel: 'Yes, Delete Permanently',
                destructive: true,
            };

    const truncate = (text, len = 50) => {
        if (!text) return '';
        return text.length > len ? `${text.slice(0, len)}…` : text;
    };

    return (
        <AuthenticatedLayout user={auth.user} header="Trash Bin">
            <Head title="Trash Bin - Cases" />

            <ConfirmDialog
                open={!!confirmAction}
                onClose={() => !emptyingTrash && setConfirmAction(null)}
                onConfirm={handleConfirm}
                processing={emptyingTrash}
                {...dialogProps}
            />

            <PageMotion className="container mx-auto px-4 py-6 max-w-7xl">
                <MotionItem className="mb-6">
                    <Breadcrumbs items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'Violation Cases', href: route('cases.index') },
                        { label: 'Trash Bin' },
                    ]} />
                </MotionItem>

                <MotionItem className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 to-red-950 px-6 py-5 shadow-xl shadow-red-900/10 mb-6 border border-red-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(244,63,94,0.15),_transparent_50%)]" />
                    <div className="absolute top-0 right-0 p-4 opacity-10">
                        <Trash2 className="w-20 h-20 text-white" />
                    </div>
                    <div className="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                                <Archive className="w-3 h-3" />
                                Recovery Zone
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1">
                                Trash Bin
                            </h1>
                            <p className="text-red-100 text-xs sm:text-sm font-medium max-w-xl">
                                Recover deleted cases or permanently remove them from the system.
                            </p>
                        </div>
                        {canEmptyTrash && cases.data.length > 0 && (
                            <button
                                type="button"
                                onClick={handleEmptyTrash}
                                className="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white text-white hover:text-rose-700 border border-white/20 rounded-xl text-sm font-bold transition-all"
                            >
                                <Trash2 className="w-4 h-4" />
                                Empty trash
                            </button>
                        )}
                    </div>
                </MotionItem>

                <TrashTabs active="cases" />

                <MotionItem className="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Violation</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deleted At</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={4}>
                                            <EmptyState
                                                icon={FileX}
                                                title="No deleted violations"
                                                message="Violation records you delete will appear here for recovery."
                                            />
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((caseItem) => (
                                        <tr key={caseItem.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-bold text-slate-800 dark:text-slate-100">
                                                    {caseItem.violation?.code ?? 'N/A'} — {caseItem.violation?.title ?? 'Unknown Violation'}
                                                </div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-xs">
                                                    {truncate(caseItem.description)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-bold text-slate-800 dark:text-slate-100">
                                                    {caseItem.student?.full_name ?? 'Restoration Required'}
                                                </div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                                    {caseItem.student?.department ?? '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300">
                                                    <Clock className="w-4 h-4 text-slate-400" />
                                                    {caseItem.deleted_at
                                                        ? dayjs(caseItem.deleted_at).format('MMM D, YYYY h:mm A')
                                                        : '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRestore(caseItem.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-500 text-emerald-600 dark:text-emerald-400 hover:text-white rounded-xl text-sm font-bold transition-all border border-emerald-100 dark:border-emerald-500/20 hover:border-emerald-500 shadow-sm hover:shadow-emerald-500/20"
                                                    >
                                                        <RotateCcw className="w-4 h-4" />
                                                        Restore
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleForceDelete(caseItem.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-600 text-rose-600 dark:text-rose-400 hover:text-white rounded-xl text-sm font-bold transition-all border border-rose-100 dark:border-rose-500/20 hover:border-rose-600 shadow-sm hover:shadow-rose-600/20"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    {cases.links && cases.links.length > 3 && (
                        <div className="bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                            <Pagination links={cases.links} />
                        </div>
                    )}
                </MotionItem>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
