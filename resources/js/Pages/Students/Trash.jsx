import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Archive, Trash2, RotateCcw, Clock, Wind, ChevronRight } from 'lucide-react';
import Swal from 'sweetalert2';
import TrashTabs from '@/Components/TrashTabs';
import Pagination from '@/Components/Pagination';

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

const swalClass = {
    popup: 'rounded-2xl border border-slate-100 shadow-xl',
    confirmButton: 'px-5 py-2.5 text-white rounded-xl font-bold text-sm shadow-sm transition-all duration-150',
    cancelButton: 'px-5 py-2.5 bg-slate-500 hover:bg-slate-600 text-white rounded-xl font-bold text-sm shadow-sm transition-all duration-150 ml-3',
};

export default function Trash({ auth, students }) {
    const handleRestore = (id) => {
        Swal.fire({
            title: 'Restore Student?',
            text: 'This will bring the student and all their violation records back to the active list.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, Restore Student',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            customClass: {
                ...swalClass,
                confirmButton: swalClass.confirmButton + ' bg-emerald-500 hover:bg-emerald-600',
            },
            buttonsStyling: false,
        }).then((result) => {
            if (result.isConfirmed) {
                router.post(route('students.restore', id));
            }
        });
    };

    const handleForceDelete = (id) => {
        Swal.fire({
            title: 'Permanently Delete Student?',
            text: 'This cannot be undone. The student and all records will be erased forever.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete Permanently',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            customClass: {
                ...swalClass,
                confirmButton: swalClass.confirmButton + ' bg-rose-600 hover:bg-rose-700',
            },
            buttonsStyling: false,
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('students.force-delete', id));
            }
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Trash Bin</h2>}
        >
            <Head title="Trash Bin - Students" />

            <div className="container mx-auto px-4 py-6 max-w-7xl">
                <div className="flex items-center gap-2 text-sm text-slate-500 mb-6">
                    <Link href={route('dashboard')} className="hover:text-indigo-600 font-medium transition-colors">
                        Dashboard
                    </Link>
                    <ChevronRight className="w-4 h-4 text-slate-300" />
                    <Link href={route('students.index')} className="hover:text-indigo-600 font-medium transition-colors">
                        Students
                    </Link>
                    <ChevronRight className="w-4 h-4 text-slate-300" />
                    <span className="text-slate-800 font-bold">Trash Bin</span>
                </div>

                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-900 to-red-950 px-6 py-5 shadow-xl shadow-red-900/10 mb-6 border border-red-900/20">
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
                </div>

                <TrashTabs active="students" />

                <div className="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="bg-slate-50/50 border-b border-slate-100">
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Student Details</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Department</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Deleted At</th>
                                    <th className="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {students.data?.length > 0 ? (
                                    students.data.map((student) => (
                                        <tr key={student.id} className="hover:bg-slate-50/80 transition-colors group">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-sm font-bold shadow-sm">
                                                        {getInitials(student.full_name)}
                                                    </div>
                                                    <div>
                                                        <div className="text-sm font-bold text-slate-800">{student.full_name}</div>
                                                        <div className="text-xs font-medium text-slate-500 mt-0.5">{student.email}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="inline-flex items-center px-3 py-1 rounded-lg bg-slate-50 border border-slate-200/60 text-xs font-semibold text-slate-600">
                                                    {(student.department || '').trim()}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2 text-sm font-medium text-slate-600">
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
                                        <td colSpan={4} className="px-6 py-24 text-center">
                                            <div className="flex flex-col items-center justify-center">
                                                <div className="w-24 h-24 bg-slate-50 border border-slate-100 rounded-3xl flex items-center justify-center mb-6 shadow-sm">
                                                    <Wind className="w-12 h-12 text-slate-300" />
                                                </div>
                                                <h3 className="text-xl font-bold text-slate-800 mb-2">Trash is empty</h3>
                                                <p className="text-sm font-medium text-slate-500 max-w-sm mx-auto">
                                                    No deleted students found. Students you delete will appear here for recovery.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {students.links && students.links.length > 3 && (
                        <div className="bg-slate-50/50 border-t border-slate-100 px-6 py-4">
                            <Pagination links={students.links} />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
