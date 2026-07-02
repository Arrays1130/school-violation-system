import React from 'react';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TrashTabs from '@/Components/TrashTabs';
import Pagination from '@/Components/Pagination';
import { Head, Link, router } from '@inertiajs/react';
import {
    ChevronRight, Trash2, Archive, RotateCcw, Clock, FileX,
} from 'lucide-react';
import dayjs from 'dayjs';

export default function Trash({ auth, cases }) {
    const handleRestore = (id) => {
        Swal.fire({
            title: 'Restore Violation Record?',
            text: "This record will be added back to the student's violation history.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, Restore Record',
        }).then((result) => {
            if (result.isConfirmed) {
                router.post(route('cases.restore', id));
            }
        });
    };

    const handleForceDelete = (id) => {
        Swal.fire({
            title: 'Permanently Delete Record?',
            text: 'This cannot be undone. The violation record will be erased forever.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Yes, Delete Permanently',
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('cases.force-delete', id));
            }
        });
    };

    const truncate = (text, len = 50) => {
        if (!text) return '';
        return text.length > len ? `${text.slice(0, len)}…` : text;
    };

    return (
        <AuthenticatedLayout user={auth.user} header="Trash Bin">
            <Head title="Trash Bin - Cases" />

            <div className="container mx-auto px-4 py-6 max-w-7xl">
                <div className="flex items-center gap-2 text-sm text-slate-500 mb-6">
                    <Link href={route('dashboard')} className="hover:text-indigo-600 font-medium transition-colors">
                        Dashboard
                    </Link>
                    <ChevronRight className="w-4 h-4 text-slate-300" />
                    <Link href={route('cases.index')} className="hover:text-indigo-600 font-medium transition-colors">
                        Violation Cases
                    </Link>
                    <ChevronRight className="w-4 h-4 text-slate-300" />
                    <span className="text-slate-800 font-bold">Trash Bin</span>
                </div>

                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 to-red-950 px-6 py-5 shadow-xl shadow-red-900/10 mb-6 border border-red-900/20">
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
                    </div>
                </div>

                <TrashTabs active="cases" />

                <div className="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="bg-slate-50/50 border-b border-slate-100">
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Violation</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Student</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Deleted At</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={4} className="px-6 py-24 text-center">
                                            <div className="flex flex-col items-center justify-center">
                                                <div className="w-24 h-24 bg-slate-50 border border-slate-100 rounded-3xl flex items-center justify-center mb-6 shadow-sm">
                                                    <FileX className="w-12 h-12 text-slate-300" />
                                                </div>
                                                <h3 className="text-xl font-bold text-slate-800 mb-2">
                                                    No deleted violations
                                                </h3>
                                                <p className="text-sm font-medium text-slate-500 max-w-sm mx-auto">
                                                    Violation records you delete will appear here for recovery.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((caseItem) => (
                                        <tr key={caseItem.id} className="hover:bg-slate-50/80 transition-colors group">
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-bold text-slate-800">
                                                    {caseItem.violation?.code ?? 'N/A'} — {caseItem.violation?.title ?? 'Unknown Violation'}
                                                </div>
                                                <div className="text-xs font-medium text-slate-500 mt-0.5 truncate max-w-xs">
                                                    {truncate(caseItem.description)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-bold text-slate-800">
                                                    {caseItem.student?.full_name ?? 'Restoration Required'}
                                                </div>
                                                <div className="text-xs font-medium text-slate-500 mt-0.5">
                                                    {caseItem.student?.department ?? '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2 text-sm font-medium text-slate-600">
                                                    <Clock className="w-4 h-4 text-slate-400" />
                                                    {caseItem.deleted_at
                                                        ? dayjs(caseItem.deleted_at).format('MMM D, Y h:mm A')
                                                        : '-'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRestore(caseItem.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-emerald-100 hover:border-emerald-500 shadow-sm hover:shadow-emerald-500/20"
                                                    >
                                                        <RotateCcw className="w-4 h-4" />
                                                        Restore
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleForceDelete(caseItem.id)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-sm font-bold transition-all border border-rose-100 hover:border-rose-600 shadow-sm hover:shadow-rose-600/20"
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
                        <div className="bg-slate-50/50 border-t border-slate-100 px-6 py-4">
                            <Pagination links={cases.links} />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
