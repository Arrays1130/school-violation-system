import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    FolderOpen, Search, Filter, X, 
    Eye, Edit, Trash2, AlertCircle, Clock, CheckCircle, Plus
} from 'lucide-react';
import Pagination from '@/Components/Pagination';

export default function Index({ auth, cases, summary, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [severity, setSeverity] = useState(filters?.severity || '');

    // Debounced search
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== filters?.search || status !== filters?.status || severity !== filters?.severity) {
                router.get(route('cases.index'), { search, status, severity }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                });
            }
        }, 300);
        return () => clearTimeout(timer);
    }, [search, status, severity]);

    const handleClear = () => {
        setSearch('');
        setStatus('');
        setSeverity('');
        router.get(route('cases.index'));
    };

    const getStatusBadge = (status) => {
        switch (status) {
            case 'Pending':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border border-amber-200"><Clock className="w-3 h-3"/> Pending</span>;
            case 'Hearing Scheduled':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200"><AlertCircle className="w-3 h-3"/> Hearing Scheduled</span>;
            case 'Hearing':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border border-indigo-200"><AlertCircle className="w-3 h-3"/> Hearing</span>;
            case 'Endorsed to Grievance':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-200"><AlertCircle className="w-3 h-3"/> Endorsed</span>;
            case 'Dismissed':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700"><CheckCircle className="w-3 h-3"/> Dismissed</span>;
            case 'Closed':
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200"><CheckCircle className="w-3 h-3"/> Closed</span>;
            default:
                return <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">{status}</span>;
        }
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
                        { label: 'Total Cases', value: summary.total, color: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-50 dark:bg-indigo-900/20' },
                        { label: 'Pending', value: summary.pending, color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 dark:bg-amber-900/20' },
                        { label: 'Hearing Scheduled', value: summary.hearing, color: 'text-blue-600', bg: 'bg-blue-50' },
                        { label: 'Closed Cases', value: summary.closed, color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-900/20' },
                    ].map((stat, i) => (
                        <div key={i} className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700/60 shadow-sm flex items-center gap-4">
                            <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${stat.bg} ${stat.color}`}>
                                <span className="text-xl font-black">{stat.value}</span>
                            </div>
                            <div>
                                <p className="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{stat.label}</p>
                            </div>
                        </div>
                    ))}
                </motion.div>

                {/* Search & Filters */}
                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                    <div className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div className="md:col-span-2">
                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Search Records</label>
                            <div className="relative">
                                <input 
                                    type="text" 
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by student name or violation..." 
                                    className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" 
                                />
                                <div className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <Search className="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Status</label>
                            <select 
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                                className="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none"
                            >
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Hearing Scheduled">Hearing Scheduled</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Severity</label>
                            <select 
                                value={severity}
                                onChange={(e) => setSeverity(e.target.value)}
                                className="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none"
                            >
                                <option value="">All Severities</option>
                                <option value="Minor">Minor</option>
                                <option value="Major">Major</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>

                        <button 
                            onClick={handleClear}
                            className="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-white rounded-xl text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 transition-colors flex items-center justify-center gap-2"
                        >
                            <X className="w-4 h-4" />
                            Clear
                        </button>
                    </div>
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
                                                    <div>{getStatusBadge(item.status)}</div>
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
                                                    >
                                                        <Eye className="w-4 h-4" />
                                                    </Link>
                                                    {item.status !== 'Closed' && (
                                                        <a 
                                                            href={route('cases.edit', item.id)} 
                                                            className="p-2 text-slate-400 hover:text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/20 rounded-xl transition-all duration-150" 
                                                            title="Edit Case"
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
                                        <td colSpan="4" className="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <FolderOpen className="w-12 h-12 text-slate-300 mb-3" />
                                                <p className="text-sm font-medium">No violation cases found.</p>
                                                <p className="text-xs mt-1">Try adjusting your filters or search query.</p>
                                            </div>
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
        </AuthenticatedLayout>
    );
}
