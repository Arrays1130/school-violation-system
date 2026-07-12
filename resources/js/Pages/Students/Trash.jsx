import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Archive, Trash2, RotateCcw, Clock, Wind } from 'lucide-react';
import ConfirmDialog from '@/Components/ConfirmDialog';
import PageMotion, { MotionItem } from '@/Components/PageMotion';
import TrashTabs from '@/Components/TrashTabs';
import Pagination from '@/Components/Pagination';
import Breadcrumbs from '@/Components/Breadcrumbs';
import EmptyState from '@/Components/EmptyState';

function getInitials(fullName) {
    if (!fullName) return '??';
    let initials = '';
    for (const name of fullName.split(' ')) {
        if (name) {
            initials += name[0].toUpperCase();
            if (initials.length >= 2) break;
        }
    }
    return initials || fullName.substring(0, 2).toUpperCase();
}

function formatDeletedAt(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
}


export default function Trash({ auth, students }) {
    const [confirmAction, setConfirmAction] = useState(null);

    const handleRestore = (id) => {
        setConfirmAction({ type: 'restore', id });
    };

    const handleForceDelete = (id) => {
        setConfirmAction({ type: 'delete', id });
    };

    const handleConfirm = () => {
        if (!confirmAction) return;
        if (confirmAction.type === 'restore') {
            router.post(route('students.restore', confirmAction.id));
        } else {
            router.delete(route('students.force-delete', confirmAction.id));
        }
        setConfirmAction(null);
    };

    const dialogProps = confirmAction?.type === 'restore'
        ? {
            title: 'Restore Student?',
            description: 'This will bring the student and all their violation records back to the active list.',
            confirmLabel: 'Yes, Restore Student',
            destructive: false,
        }
        : {
            title: 'Permanently Delete Student?',
            description: 'This cannot be undone. The student and all records will be erased forever.',
            confirmLabel: 'Yes, Delete Permanently',
            destructive: true,
        };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Trash Bin</h2>}
        >
            <Head title="Trash Bin - Students" />

            <ConfirmDialog
                open={!!confirmAction}
                onClose={() => setConfirmAction(null)}
                onConfirm={handleConfirm}
                {...dialogProps}
            />

            <PageMotion className="container mx-auto px-4 py-6 max-w-7xl">
                <MotionItem className="mb-6">
                    <Breadcrumbs items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'Students', href: route('students.index') },
                        { label: 'Trash Bin' },
                    ]} />
                </MotionItem>

                <MotionItem className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 to-red-950 px-6 py-5 shadow-xl shadow-red-900/10 mb-6 border border-red-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(244,63,94,0.15),_transparent_50%)]" />
                    <div className="absolute top-0 right-0 p-4 opacity-10">
                        <Trash2 className="w-20 h-20 text-white" />
                    </div>
                    <div className="relative z-10">
                        <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                            <Archive className="w-3 h-3" />
                            Recovery Zone
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-black text-white tracking-tight mb-1">Trash Bin</h1>
                        <p className="text-red-100 text-xs sm:text-sm font-medium max-w-xl">
                            Recover deleted students or permanently remove them from the system.
                        </p>
                    </div>
                </MotionItem>

                <TrashTabs active="students" />

                <MotionItem className="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Details</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Department</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deleted At</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {students.data?.length > 0 ? (
                                    students.data.map((student) => (
                                        <tr key={student.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20 flex items-center justify-center text-sm font-bold shadow-sm">
                                                        {getInitials(student.full_name)}
                                                    </div>
                                                    <div>
                                                        <div className="text-sm font-bold text-slate-800 dark:text-slate-100">{student.full_name}</div>
                                                        <div className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">{student.email}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="inline-flex items-center px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                                    {(student.department || '').trim()}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300">
                                                    <Clock className="w-4 h-4 text-slate-400" />
                                                    {formatDeletedAt(student.deleted_at)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRestore(student.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-emerald-100 hover:border-emerald-500 shadow-sm hover:shadow-emerald-500/20"
                                                    >
                                                        <RotateCcw className="w-4 h-4" />
                                                        Restore
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleForceDelete(student.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-rose-100 hover:border-rose-600 shadow-sm hover:shadow-rose-600/20"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={4}>
                                            <EmptyState
                                                icon={Wind}
                                                title="Trash is empty"
                                                message="No deleted students found. Students you delete will appear here for recovery."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {students.links && students.links.length > 3 && (
                        <div className="bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                            <Pagination links={students.links} />
                        </div>
                    )}
                </MotionItem>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
