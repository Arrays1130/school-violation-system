import React, { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft, Eye, Edit, Users, FolderOpen,
} from 'lucide-react';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import StatusBadge from '@/Components/StatusBadge';
import FilterBar from '@/Components/FilterBar';
import useInertiaLoading from '@/hooks/useInertiaLoading';

export default function ByViolation({
    auth,
    violation,
    cases,
    departments = [],
    academicYears = [],
    filters,
}) {
    const isLoading = useInertiaLoading();
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [department, setDepartment] = useState(filters?.department || '');
    const [academicYear, setAcademicYear] = useState(filters?.academic_year || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');
    const searchInputRef = useRef(null);

    const filterParams = {
        search,
        status,
        department,
        academic_year: academicYear,
        date_from: dateFrom,
        date_to: dateTo,
    };

    useEffect(() => {
        const timer = setTimeout(() => {
            if (
                search !== (filters?.search || '') ||
                status !== (filters?.status || '') ||
                department !== (filters?.department || '') ||
                academicYear !== (filters?.academic_year || '') ||
                dateFrom !== (filters?.date_from || '') ||
                dateTo !== (filters?.date_to || '')
            ) {
                const el = searchInputRef.current;
                const hadFocus = el && document.activeElement === el;
                const caret = el ? el.selectionStart : null;

                router.get(route('cases.by-violation', violation.id), filterParams, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ['cases', 'filters'],
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
    }, [search, status, department, academicYear, dateFrom, dateTo]);

    const handleClear = () => {
        setSearch('');
        setStatus('');
        setDepartment('');
        setAcademicYear('');
        setDateFrom('');
        setDateTo('');
        router.get(route('cases.by-violation', violation.id));
    };

    const containerVariants = {
        hidden: { opacity: 0 },
        show: { opacity: 1, transition: { staggerChildren: 0.08 } },
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 16 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } },
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Cases</h2>}
        >
            <Head title={`${violation.title} — Students`} />

            <motion.div
                className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >
                <motion.div variants={itemVariants} className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />

                    <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link
                                href={route('cases.index')}
                                className="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all"
                            >
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2">
                                    <Users className="w-3.5 h-3.5" />
                                    {violation.code}
                                </div>
                                <h1 className="text-2xl font-extrabold text-white tracking-tight">{violation.title}</h1>
                                <p className="text-indigo-100/70 text-sm mt-1.5">
                                    {violation.category || 'Uncategorized'} · {violation.severity} · {cases.total} student{cases.total === 1 ? '' : 's'}
                                </p>
                            </div>
                        </div>
                    </div>
                </motion.div>

                <motion.div variants={itemVariants}>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by student name..."
                        filtersClassName="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 [&>*]:min-w-0"
                    >
                        <div className="min-w-0">
                            <label htmlFor="bv-status" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Status</label>
                            <select id="bv-status" value={status} onChange={(e) => setStatus(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="endorsed">Endorsed</option>
                                <option value="Hearing Scheduled">Hearing Scheduled</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="bv-department" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Department</label>
                            <select id="bv-department" value={department} onChange={(e) => setDepartment(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Departments</option>
                                {departments.map((dept) => <option key={dept} value={dept}>{dept}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="bv-academic-year" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Academic Year</label>
                            <select id="bv-academic-year" value={academicYear} onChange={(e) => setAcademicYear(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Years</option>
                                {academicYears.map((year) => <option key={year} value={year}>{year}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0 sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="min-w-0">
                                <label htmlFor="bv-date-from" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">From</label>
                                <input id="bv-date-from" type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                            <div className="min-w-0">
                                <label htmlFor="bv-date-to" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">To</label>
                                <input id="bv-date-to" type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                        </div>
                    </FilterBar>
                </motion.div>

                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    {isLoading && (
                        <div className="h-0.5 w-full bg-indigo-500/70 animate-pulse" aria-hidden="true" />
                    )}
                    <div className={`overflow-x-auto no-scrollbar transition-opacity duration-200 ${isLoading ? 'opacity-50' : 'opacity-100'}`}>
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left block md:table">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80 hidden md:table-header-group">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Date / Status</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Student</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Sanction</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800 block md:table-row-group">
                                {cases.data.length > 0 ? (
                                    cases.data.map((item) => (
                                        <motion.tr
                                            variants={itemVariants}
                                            key={item.id}
                                            className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors duration-150 group block md:table-row border-b border-slate-100 dark:border-slate-800 md:border-none p-4 md:p-0"
                                        >
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
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
                                                    <div><StatusBadge item={item} variant="compact" /></div>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">{item.student?.full_name}</span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {item.student?.department}
                                                        {item.student?.section ? ` — ${item.student.section}` : ''}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 block md:table-cell">
                                                <span className="text-sm text-slate-700 dark:text-slate-300">{item.sanction || '—'}</span>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell text-left md:text-right text-sm font-medium mt-4 md:mt-0 border-t border-slate-100 dark:border-slate-800 md:border-none pt-4 md:pt-4">
                                                <div className="flex items-center justify-start md:justify-end gap-1">
                                                    <Link
                                                        href={route('cases.show', item.id)}
                                                        className="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all duration-150"
                                                        title="View Case Details"
                                                        aria-label={`View case for ${item.student?.full_name ?? 'student'}`}
                                                    >
                                                        <Eye className="w-4 h-4" />
                                                    </Link>
                                                    {item.status !== 'Closed' && (
                                                        <Link
                                                            href={route('cases.edit', item.id)}
                                                            className="p-2 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-all duration-150"
                                                            title="Edit Case"
                                                            aria-label={`Edit case for ${item.student?.full_name ?? 'student'}`}
                                                        >
                                                            <Edit className="w-4 h-4" />
                                                        </Link>
                                                    )}
                                                </div>
                                            </td>
                                        </motion.tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4">
                                            <EmptyState
                                                icon={FolderOpen}
                                                title="No students found."
                                                message="No cases match your filters for this violation."
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
                </motion.div>
            </motion.div>
        </AuthenticatedLayout>
    );
}
