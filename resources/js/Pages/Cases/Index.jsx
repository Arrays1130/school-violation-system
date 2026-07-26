import React, { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    FolderOpen, Plus, Users, ShieldQuestion,
} from 'lucide-react';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import useInertiaLoading from '@/hooks/useInertiaLoading';

export default function Index({ auth, violations, summary, departments = [], academicYears = [], filters }) {
    const isLoading = useInertiaLoading();
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [severity, setSeverity] = useState(filters?.severity || '');
    const [department, setDepartment] = useState(filters?.department || '');
    const [academicYear, setAcademicYear] = useState(filters?.academic_year || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');
    const searchInputRef = useRef(null);

    const filterParams = { search, status, severity, department, academic_year: academicYear, date_from: dateFrom, date_to: dateTo };

    useEffect(() => {
        const timer = setTimeout(() => {
            if (
                search !== (filters?.search || '') ||
                status !== (filters?.status || '') ||
                severity !== (filters?.severity || '') ||
                department !== (filters?.department || '') ||
                academicYear !== (filters?.academic_year || '') ||
                dateFrom !== (filters?.date_from || '') ||
                dateTo !== (filters?.date_to || '')
            ) {
                const el = searchInputRef.current;
                const hadFocus = el && document.activeElement === el;
                const caret = el ? el.selectionStart : null;

                router.get(route('cases.index'), filterParams, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ['violations', 'summary', 'filters'],
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
    }, [search, status, severity, department, academicYear, dateFrom, dateTo]);

    const handleClear = () => {
        setSearch('');
        setStatus('');
        setSeverity('');
        setDepartment('');
        setAcademicYear('');
        setDateFrom('');
        setDateTo('');
        router.get(route('cases.index'));
    };

    const getSeverityBadge = (severityValue) => {
        switch (severityValue) {
            case 'Minor':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 text-emerald-700">
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                        Minor
                    </span>
                );
            case 'Major':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-900/20 border border-rose-200 text-rose-700">
                        <span className="w-1.5 h-1.5 rounded-full bg-rose-500" />
                        Major
                    </span>
                );
            default:
                return <span className="text-slate-600 dark:text-slate-400 font-bold text-xs">{severityValue}</span>;
        }
    };

    const containerVariants = {
        hidden: { opacity: 0 },
        show: { opacity: 1, transition: { staggerChildren: 0.1 } },
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 20 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } },
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Cases</h2>}
        >
            <Head title="Violation Cases" />

            <motion.div
                className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >
                <motion.div variants={itemVariants} className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl" />

                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <FolderOpen className="w-3.5 h-3.5" />
                                Cases Management
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">Violation Cases</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">
                                Browse by violation type, then open a rule to see the students involved.
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Link href={route('cases.create')} className="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                                <Plus className="w-4 h-4" />
                                Record Violation
                            </Link>
                        </div>
                    </div>
                </motion.div>

                <motion.div variants={itemVariants} className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {[
                        { label: 'Total Cases', value: summary.total, color: 'text-indigo-600 dark:text-indigo-400' },
                        { label: 'Pending', value: summary.pending, color: 'text-amber-600 dark:text-amber-400' },
                        { label: 'Hearing Scheduled', value: summary.hearing, color: 'text-blue-600 dark:text-blue-400' },
                        { label: 'Closed Cases', value: summary.closed, color: 'text-emerald-600 dark:text-emerald-400' },
                    ].map((stat, i) => (
                        <div key={i} className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                            <p className={`text-3xl font-black tabular-nums ${stat.color}`}>{stat.value}</p>
                            <p className="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">{stat.label}</p>
                        </div>
                    ))}
                </motion.div>

                <motion.div variants={itemVariants}>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by violation name or code..."
                        filtersClassName="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 [&>*]:min-w-0"
                    >
                        <div className="min-w-0">
                            <label htmlFor="case-status" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Status</label>
                            <select id="case-status" value={status} onChange={(e) => setStatus(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="endorsed">Endorsed</option>
                                <option value="Hearing Scheduled">Hearing Scheduled</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="case-severity" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Severity</label>
                            <select id="case-severity" value={severity} onChange={(e) => setSeverity(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Severities</option>
                                <option value="Minor">Minor</option>
                                <option value="Major">Major</option>
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="case-department" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Department</label>
                            <select id="case-department" value={department} onChange={(e) => setDepartment(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Departments</option>
                                {departments.map((dept) => <option key={dept} value={dept}>{dept}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="case-academic-year" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Academic Year</label>
                            <select id="case-academic-year" value={academicYear} onChange={(e) => setAcademicYear(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Years</option>
                                {academicYears.map((year) => <option key={year} value={year}>{year}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0 sm:col-span-2 lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="min-w-0">
                                <label htmlFor="case-date-from" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">From</label>
                                <input id="case-date-from" type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                            <div className="min-w-0">
                                <label htmlFor="case-date-to" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">To</label>
                                <input id="case-date-to" type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                        </div>
                    </FilterBar>
                </motion.div>

                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    {isLoading && (
                        <div className="h-0.5 w-full bg-indigo-500/70 animate-pulse" aria-hidden="true" />
                    )}
                    <div className={`overflow-x-auto no-scrollbar transition-opacity duration-200 ${isLoading ? 'opacity-50' : 'opacity-100'}`}>
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Violation</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Category</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Severity</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Students</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800">
                                {violations.data.length > 0 ? (
                                    violations.data.map((violation) => (
                                        <motion.tr
                                            variants={itemVariants}
                                            key={violation.id}
                                            onClick={() => router.visit(route('cases.by-violation', violation.id))}
                                            whileHover={{ scale: 1.005, x: 2 }}
                                            whileTap={{ scale: 0.995 }}
                                            className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors duration-150 group cursor-pointer origin-left"
                                        >
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-4">
                                                    <span className="inline-flex items-center justify-center px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-xs border border-slate-200 dark:border-slate-700 uppercase tracking-wider">
                                                        {violation.code}
                                                    </span>
                                                    <div className="min-w-0">
                                                        <p className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                                            {violation.title}
                                                        </p>
                                                        <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                            Click to view students
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                                                    {violation.category || '—'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                {getSeverityBadge(violation.severity)}
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300">
                                                    <Users className="w-3.5 h-3.5" />
                                                    {violation.cases_count ?? 0}
                                                </span>
                                            </td>
                                        </motion.tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4">
                                            <EmptyState
                                                icon={ShieldQuestion}
                                                title="No violations found."
                                                message="Try adjusting your filters, or record a new violation case."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {violations.links && violations.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={violations.links} />
                        </div>
                    )}
                </motion.div>
            </motion.div>
        </AuthenticatedLayout>
    );
}
