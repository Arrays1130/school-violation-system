import React, { useEffect, useRef } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    Database, ArrowLeft, Search, X, Eye, Printer, ClipboardX 
} from 'lucide-react';
import dayjs from 'dayjs';

export default function Retrieval({ cases, departments, violations, academicYears, filters }) {
    const { data, setData, get } = useForm({
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        date_month: filters.date_month || '',
        violation_id: filters.violation_id || '',
        department: filters.department || '',
        academic_year: filters.academic_year || '',
        student_search: filters.student_search || '',
        severity: filters.severity || '',
    });

    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        const timeout = setTimeout(() => {
            get(route('reports.retrieval'), {
                preserveState: true,
                preserveScroll: true
            });
        }, 300);
        return () => clearTimeout(timeout);
    }, [data.student_search, data.department, data.academic_year, data.violation_id, data.severity, data.date_from, data.date_to, data.date_month]);

    const handleSearch = (e) => {
        e.preventDefault();
        get(route('reports.retrieval'), {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        router.get(route('reports.retrieval'));
    };

    const hasFilters = Object.values(data).some(v => v !== '');

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Search Records</h2>}
        >
            <Head title="Record Retrieval" />

            <div className="py-8">
                <div className="space-y-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-2xl shadow-indigo-900/20 border border-indigo-900/50">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                        <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                        
                        <div className="relative flex items-center gap-6 z-10">
                            <Link href={route('reports.index')} className="w-12 h-12 rounded-2xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-1">
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                    <Database className="w-3.5 h-3.5" />
                                    Record Retrieval
                                </div>
                                <h2 className="text-3xl font-black text-white tracking-tight">Search Records</h2>
                                <p className="text-indigo-200/70 text-sm mt-2 font-medium">Search through all past records with advanced, multi-parameter filters.</p>
                            </div>
                        </div>
                    </div>

                    {/* Advanced Filters */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-700/60 rounded-3xl p-8 shadow-sm relative overflow-hidden">
                        <form onSubmit={handleSearch} className="space-y-6 relative z-10">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                {/* Date Range */}
                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Start Date</label>
                                    <input type="date" value={data.date_from} onChange={e => setData('date_from', e.target.value)} 
                                           className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm" />
                                </div>

                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">End Date</label>
                                    <input type="date" value={data.date_to} onChange={e => setData('date_to', e.target.value)} 
                                           className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm" />
                                </div>

                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Month</label>
                                    <input type="month" value={data.date_month} onChange={e => setData('date_month', e.target.value)} 
                                           className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm" />
                                </div>

                                {/* Identity & Logic */}
                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Violation Type</label>
                                    <select value={data.violation_id} onChange={e => setData('violation_id', e.target.value)} 
                                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm appearance-none cursor-pointer">
                                        <option value="">All Violations</option>
                                        {violations.map(v => (
                                            <option key={v.id} value={v.id}>
                                                {v.code} — {v.title.length > 30 ? v.title.substring(0, 30) + '...' : v.title}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 pt-2">
                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Department</label>
                                    <select value={data.department} onChange={e => setData('department', e.target.value)} 
                                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm appearance-none cursor-pointer">
                                        <option value="">All Departments</option>
                                        {departments.map((dept, idx) => (
                                            <option key={idx} value={dept}>{dept}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Academic Year</label>
                                    <select value={data.academic_year} onChange={e => setData('academic_year', e.target.value)} 
                                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm appearance-none cursor-pointer">
                                        <option value="">All Years</option>
                                        {academicYears && academicYears.map((year, idx) => (
                                            <option key={idx} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="lg:col-span-2">
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Search Student</label>
                                    <div className="relative group">
                                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <input type="text" value={data.student_search} onChange={e => setData('student_search', e.target.value)} 
                                               placeholder="Search by name or ID..." 
                                               className="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-slate-400 text-sm font-medium shadow-sm" />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-1 mb-2">Severity Level</label>
                                    <select value={data.severity} onChange={e => setData('severity', e.target.value)} 
                                            className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium shadow-sm appearance-none cursor-pointer">
                                        <option value="">All Severities</option>
                                        <option value="Minor">Minor</option>
                                        <option value="Major">Major</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>
                            </div>

                            <div className="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                    <span className="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]"></span>
                                    <span className="text-slate-800 dark:text-slate-200 font-black text-sm">{cases.total}</span> <span className="uppercase tracking-wider font-bold">Records Found</span>
                                </div>
                                
                                <div className="flex w-full sm:w-auto items-center gap-3">
                                    {hasFilters && (
                                        <button type="button" onClick={clearFilters} className="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-3 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold rounded-xl transition-all border border-transparent hover:border-rose-200 text-sm active:scale-95 shadow-sm">
                                            <X className="w-4 h-4 mr-2" /> Clear
                                        </button>
                                    )}
                                    <button type="submit" className="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/25 active:scale-95 text-sm">
                                        <Search className="w-4 h-4" />
                                        Search Database
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {/* Search Results */}
                    <div className="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/60 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                                        <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                                        <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                        <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Violation Type</th>
                                        <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                        <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50 bg-white/50 dark:bg-slate-900/50">
                                    {cases.data.map((item) => {
                                        const sevColorMap = {
                                            'Minor': 'bg-blue-50 text-blue-700 border-blue-100',
                                            'Major': 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 border-amber-100',
                                            'Critical': 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 border-rose-100',
                                        };
                                        const sevColor = sevColorMap[item.violation?.severity] || 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
                                        
                                        const closed = item.status === 'Closed';

                                        return (
                                            <tr key={item.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors group">
                                                <td className="px-8 py-5 whitespace-nowrap">
                                                    <div className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:text-indigo-400 transition-colors">{dayjs(item.occurred_at).format('MMM DD, YYYY')}</div>
                                                    <div className="text-[11px] font-bold text-slate-400 mt-0.5">{dayjs(item.occurred_at).format('hh:mm A')}</div>
                                                </td>
                                                <td className="px-8 py-5">
                                                    <div className="text-sm font-bold text-slate-900 dark:text-white mb-0.5">
                                                        {item.student?.full_name || 'Unknown Student'}
                                                    </div>
                                                    <div className="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                                        {item.student?.department || '-'}
                                                    </div>
                                                </td>
                                                <td className="px-8 py-5">
                                                    <div className="flex flex-col gap-1.5">
                                                        <div className="flex items-center gap-2">
                                                            <span className="px-2 py-0.5 bg-slate-100 text-slate-500 dark:text-slate-400 text-[10px] font-black rounded border border-slate-200 dark:border-slate-700 tracking-widest uppercase shadow-sm">
                                                                {item.violation?.code}
                                                            </span>
                                                            <div className="text-sm font-bold text-slate-800 dark:text-slate-200 truncate max-w-[200px]" title={item.violation?.title}>
                                                                {item.violation?.title}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <span className={`inline-flex px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border ${sevColor}`}>
                                                                {item.violation?.severity}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-8 py-5">
                                                    <span className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold shadow-sm ${
                                                        closed ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 border border-emerald-200 shadow-emerald-500/10' : 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 border border-indigo-200 shadow-indigo-500/10'
                                                    }`}>
                                                        <span className={`w-1.5 h-1.5 rounded-full ${closed ? 'bg-emerald-500' : 'bg-indigo-500 shadow-[0_0_5px_currentColor]'}`}></span>
                                                        {item.status}
                                                    </span>
                                                </td>
                                                <td className="px-8 py-5 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link href={route('cases.show', item.id)} className="p-2.5 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-xl transition-all shadow-sm bg-white dark:bg-slate-900 border border-transparent hover:border-indigo-200" title="View">
                                                            <Eye className="w-4 h-4" />
                                                        </Link>
                                                        <a href={route('cases.print', item.id)} target="_blank" className="p-2.5 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-xl transition-all shadow-sm bg-white dark:bg-slate-900 border border-transparent hover:border-indigo-200" title="Print Case">
                                                            <Printer className="w-4 h-4" />
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}

                                    {cases.data.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-8 py-24 text-center">
                                                <div className="flex flex-col items-center justify-center">
                                                    <div className="w-16 h-16 mb-5 rounded-3xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center border border-slate-100 dark:border-slate-800 shadow-inner">
                                                        <ClipboardX className="w-8 h-8 text-slate-300" />
                                                    </div>
                                                    <p className="text-slate-900 dark:text-white font-black text-base mb-1">No records found</p>
                                                    <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">Try adjusting your filters or search terms.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        {cases.links && cases.links.length > 3 && (
                            <div className="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-center">
                                <div className="flex flex-wrap gap-1">
                                    {cases.links.map((link, k) => (
                                        <Link
                                            key={k}
                                            href={link.url || '#'}
                                            className={`px-3 py-1.5 text-[13px] font-semibold rounded-lg transition-colors ${
                                                link.active 
                                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' 
                                                    : !link.url 
                                                        ? 'text-slate-300 cursor-not-allowed' 
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 bg-slate-100'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
