import React, { useState, useEffect, useRef, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft, Users, FolderOpen, CalendarDays, ChevronRight, Plus, X,
} from 'lucide-react';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import useInertiaLoading from '@/hooks/useInertiaLoading';

function formatDay(dateStr) {
    if (!dateStr) return '—';
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function StatusChips({ counts = {} }) {
    const chips = [
        { key: 'pending', label: 'Pending', className: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800' },
        { key: 'hearing', label: 'Hearing', className: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800' },
        { key: 'endorsed', label: 'Endorsed', className: 'bg-violet-50 text-violet-800 border-violet-200 dark:bg-violet-900/20 dark:text-violet-300 dark:border-violet-800' },
        { key: 'closed', label: 'Closed', className: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800' },
    ].filter((chip) => (counts[chip.key] ?? 0) > 0);

    if (!chips.length) return null;

    return (
        <div className="flex flex-wrap items-center gap-1.5 justify-end">
            {chips.map((chip) => (
                <span
                    key={chip.key}
                    className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border ${chip.className}`}
                >
                    {chip.label} {counts[chip.key]}
                </span>
            ))}
        </div>
    );
}

export default function ByViolation({
    auth,
    violation,
    dayGroups,
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

    const activeFilterChips = useMemo(() => {
        const chips = [];
        if (search) chips.push({ key: 'search', label: `Search: ${search}`, clear: () => setSearch('') });
        if (status) {
            const statusLabel = status === 'endorsed' ? 'Endorsed' : status;
            chips.push({ key: 'status', label: `Status: ${statusLabel}`, clear: () => setStatus('') });
        }
        if (department) chips.push({ key: 'department', label: `Department: ${department}`, clear: () => setDepartment('') });
        if (academicYear) chips.push({ key: 'academic_year', label: `Year: ${academicYear}`, clear: () => setAcademicYear('') });
        if (dateFrom) chips.push({ key: 'date_from', label: `From: ${dateFrom}`, clear: () => setDateFrom('') });
        if (dateTo) chips.push({ key: 'date_to', label: `To: ${dateTo}`, clear: () => setDateTo('') });
        return chips;
    }, [search, status, department, academicYear, dateFrom, dateTo]);

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
                    only: ['dayGroups', 'filters'],
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

    const dayHref = (date) => {
        const params = Object.fromEntries(
            Object.entries(filterParams).filter(([, v]) => v !== '' && v != null),
        );
        return route('cases.by-violation.day', {
            violation: violation.id,
            date,
            ...params,
        });
    };

    const severityBadge = violation.severity === 'Major'
        ? 'bg-rose-500/20 text-rose-100 border-rose-400/30'
        : 'bg-emerald-500/20 text-emerald-100 border-emerald-400/30';

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Cases</h2>}
        >
            <Head title={`${violation.title} — Day Cases`} />

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
                                <div className="flex flex-wrap items-center gap-2 mb-2">
                                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest">
                                        <Users className="w-3.5 h-3.5" />
                                        {violation.code}
                                    </div>
                                    {violation.severity && (
                                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border ${severityBadge}`}>
                                            {violation.severity}
                                        </span>
                                    )}
                                </div>
                                <h1 className="text-2xl font-extrabold text-white tracking-tight">{violation.title}</h1>
                                <p className="text-indigo-100/70 text-sm mt-1.5">
                                    {violation.category || 'Uncategorized'} · {dayGroups.total} day case{dayGroups.total === 1 ? '' : 's'} · newest first
                                </p>
                            </div>
                        </div>

                        <Link
                            href={route('cases.create', { violation_id: violation.id })}
                            className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/30 self-start sm:self-center"
                        >
                            <Plus className="w-4 h-4" />
                            Record violation
                        </Link>
                    </div>
                </motion.div>

                <motion.div variants={itemVariants}>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by student name or case code..."
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

                {activeFilterChips.length > 0 && (
                    <motion.div variants={itemVariants} className="flex flex-wrap items-center gap-2">
                        {activeFilterChips.map((chip) => (
                            <button
                                key={chip.key}
                                type="button"
                                onClick={chip.clear}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                            >
                                {chip.label}
                                <X className="w-3.5 h-3.5" aria-hidden="true" />
                                <span className="sr-only">Remove {chip.label}</span>
                            </button>
                        ))}
                        <button
                            type="button"
                            onClick={handleClear}
                            className="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200"
                        >
                            Clear all
                        </button>
                    </motion.div>
                )}

                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    {isLoading && (
                        <div className="h-0.5 w-full bg-indigo-500/70 animate-pulse" aria-hidden="true" />
                    )}
                    <div className={`transition-opacity duration-200 ${isLoading ? 'opacity-50' : 'opacity-100'}`}>
                        {dayGroups.data.length > 0 ? (
                            <ul className="divide-y divide-slate-100 dark:divide-slate-800">
                                {dayGroups.data.map((group) => (
                                    <motion.li key={group.date} variants={itemVariants}>
                                        <Link
                                            href={dayHref(group.date)}
                                            className="flex items-center gap-4 px-5 py-4 sm:px-6 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/20 transition-colors group"
                                        >
                                            <div className="hidden sm:flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                                <CalendarDays className="w-5 h-5" />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <p className="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                    {group.display_label}
                                                </p>
                                                <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                                    {formatDay(group.date)}
                                                </p>
                                            </div>
                                            <div className="flex flex-col sm:flex-row items-end sm:items-center gap-2 shrink-0">
                                                <StatusChips counts={group.status_counts} />
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700">
                                                    <Users className="w-3.5 h-3.5" />
                                                    {group.student_count} student{group.student_count === 1 ? '' : 's'}
                                                </span>
                                                <ChevronRight className="w-5 h-5 text-slate-300 group-hover:text-indigo-500 transition-colors hidden sm:block" />
                                            </div>
                                        </Link>
                                    </motion.li>
                                ))}
                            </ul>
                        ) : (
                            <EmptyState
                                icon={FolderOpen}
                                title="No day cases found."
                                message="No cases match your filters for this violation."
                            />
                        )}
                    </div>

                    {dayGroups.links && dayGroups.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={dayGroups.links} />
                        </div>
                    )}
                </motion.div>
            </motion.div>
        </AuthenticatedLayout>
    );
}
