import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    FolderOpen, Search, Filter, X, 
    Eye, Edit, Trash2, AlertCircle, Clock, CheckCircle, Plus
} from 'lucide-react';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import StatusBadge from '@/Components/StatusBadge';
import FilterBar from '@/Components/FilterBar';
import useInertiaLoading from '@/hooks/useInertiaLoading';
import { ListPageSkeleton } from '@/Components/ui/Skeleton';

export default function Index({ auth, cases, summary, departments = [], academicYears = [], filters }) {
    const isLoading = useInertiaLoading();
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [severity, setSeverity] = useState(filters?.severity || '');
    const [department, setDepartment] = useState(filters?.department || '');
    const [academicYear, setAcademicYear] = useState(filters?.academic_year || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

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
                router.get(route('cases.index'), filterParams, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                });
            }
        }, 300);
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

    const getSeverityBadge = (severity) => {
        switch (severity) {
            case 'Minor':
                return <span className="text-sky-600 font-bold text-xs">Minor</span>;
            case 'Major':
                return <span className="text-amber-600 dark:text-amber-400 font-bold text-xs">Major</span>;
            case 'Critical':
                return <span className="text-rose-600 dark:text-rose-400 font-bold text-xs">Critical</span>;
            default:
                return <span className="text-slate-600 dark:text-slate-400 font-bold text-xs">{severity}</span>;
        }
    };

    const containerVariants = {
        hidden: { opacity: 0 },
        show: { opacity: 1, transition: { staggerChildren: 0.1 } }
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 20 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Cases</h2>}
        >
            <Head title="Violation Cases" />

            {isLoading ? (
                <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <ListPageSkeleton />
                </div>
            ) : (
            <motion.div 
                className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >
                
                {/* Modern Header */}
                <motion.div variants={itemVariants} className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <FolderOpen className="w-3.5 h-3.5" />
                                Cases Management
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">Violation Cases</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage and track student violation records, hearings, and sanctions.</p>
                        </div>
                        
                        <div className="flex items-center gap-3">
                            <Link href={route('cases.create')} className="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                                <Plus className="w-4 h-4" />
                                Record Violation
                            </Link>
                        </div>

                    </div>
                </motion.div>

                {/* Summary Cards */}
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

                {/* Search & Filters */}
                <motion.div variants={itemVariants}>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by student name or violation..."
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
                            <div className="min-w-0">
                                <label htmlFor="case-date-from" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">From</label>
                                <input id="case-date-from" type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                            <div className="min-w-0">
                                <label htmlFor="case-date-to" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">To</label>
                                <input id="case-date-to" type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm" />
                            </div>
                    </FilterBar>
                </motion.div>

                {/* Records List */}
                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto no-scrollbar">
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left block md:table">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80 hidden md:table-header-group">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Date / Status</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Student</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Violation Details</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800 block md:table-row-group">
                                {cases.data.length > 0 ? (
                                    cases.data.map((item) => (
                                        <motion.tr variants={itemVariants} key={item.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors duration-150 group block md:table-row border-b border-slate-100 dark:border-slate-800 md:border-none p-4 md:p-0">
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex flex-col gap-1.5">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">{new Date(item.occurred_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                                                    <div><StatusBadge item={item} variant="compact" /></div>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">{item.student?.full_name}</span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{item.student?.department} — {item.student?.section}</span>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex flex-col">
                                                    <div className="flex items-center gap-2">
                                                        {getSeverityBadge(item.violation?.severity)}
                                                        <span className="text-sm font-medium text-slate-800 dark:text-slate-200">{item.violation?.title}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell text-left md:text-right text-sm font-medium mt-4 md:mt-0 border-t border-slate-100 dark:border-slate-800 md:border-none pt-4 md:pt-4">
                                                <div className="flex items-center justify-start md:justify-end gap-1">
                                                    <Link 
                                                        href={route('cases.show', item.id)} 
                                                        className="p-2 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-xl transition-all duration-150" 
                                                        title="View Case Details"
                                                        aria-label={`View case for ${item.student?.full_name ?? 'student'}`}
                                                    >
                                                        <Eye className="w-4 h-4" />
                                                    </Link>
                                                    {item.status !== 'Closed' && (
                                                        <a 
                                                            href={route('cases.edit', item.id)} 
                                                            className="p-2 text-slate-400 hover:text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/20 rounded-xl transition-all duration-150" 
                                                            title="Edit Case"
                                                            aria-label={`Edit case for ${item.student?.full_name ?? 'student'}`}
                                                        >
                                                            <Edit className="w-4 h-4" />
                                                        </a>
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
                                                title="No violation cases found."
                                                message="Try adjusting your filters or search query."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {cases.links && cases.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={cases.links} />
                        </div>
                    )}
                </motion.div>
                </motion.div>
            )}
        </AuthenticatedLayout>
    );
}
