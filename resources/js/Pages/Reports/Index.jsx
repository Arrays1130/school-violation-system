import React, { useEffect, useRef } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    LineChart, FileSpreadsheet, FileDown, Printer, Search, 
    ChevronDown, Filter, RotateCcw, ClipboardX, Tag
} from 'lucide-react';
import dayjs from 'dayjs';

export default function Index({ cases, departments, filters }) {
    const { data, setData, get } = useForm({
        student_search: filters.student_search || '',
        department: filters.department || '',
        status: filters.status || ''
    });

    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        const timeout = setTimeout(() => {
            get(route('reports.index'), {
                preserveState: true,
                preserveScroll: true
            });
        }, 300);
        return () => clearTimeout(timeout);
    }, [data.student_search, data.department, data.status]);

    const handleSearch = (e) => {
        e.preventDefault();
        get(route('reports.index'), {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        router.get(route('reports.index'));
    };

    const getStatusStyle = (status) => {
        const smap = {
            'Pending':                'bg-amber-50 dark:bg-amber-900/20 text-amber-700 border-amber-200',
            'Hearing Scheduled':      'bg-blue-50 dark:bg-blue-900/20 text-blue-700 border-blue-200',
            'Hearing':                'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 border-indigo-200',
            'Endorsed to Grievance':  'bg-rose-50 dark:bg-rose-900/20 text-rose-700 border-rose-200',
            'Dismissed':              'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700',
            'Closed':                 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 border-emerald-200',
        };
        return smap[status] || 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Violation Reports</h2>}
        >
            <Head title="Reports & Analytics" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* High-End Branded Header Card */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-2xl shadow-indigo-900/20 mb-8 border border-indigo-900/50">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                        <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                        
                        <div className="relative flex flex-col xl:flex-row xl:items-center justify-between gap-6 z-10">
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 dark:bg-slate-900/5 border border-white/10 text-indigo-200 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                    <LineChart className="w-3.5 h-3.5" />
                                    Administrative Insights
                                </div>
                                <h1 className="text-3xl font-black text-white tracking-tight">Reports & Analytics</h1>
                                <p className="text-indigo-200/70 text-sm mt-2 max-w-2xl leading-relaxed font-medium">Generate comprehensive student offense summaries, download high-fidelity PDF documents, and compile structural analytics.</p>
                            </div>
                            
                            {/* Action Panel */}
                            <div className="flex flex-wrap items-center gap-3">
                                <a href={route('reports.csv', filters)} className="px-5 py-2.5 bg-white/5 dark:bg-slate-900/5 border border-white/10 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/10 dark:bg-slate-900/10 transition-all flex items-center gap-2">
                                    <FileSpreadsheet className="w-4.5 h-4.5 text-emerald-400" />
                                    Export CSV
                                </a>
                                <a href={route('reports.pdf', filters)} className="px-5 py-2.5 bg-white/5 dark:bg-slate-900/5 border border-white/10 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/10 dark:bg-slate-900/10 transition-all flex items-center gap-2">
                                    <FileDown className="w-4.5 h-4.5 text-rose-400" />
                                    Export PDF
                                </a>
                                <a href={route('reports.print', filters)} target="_blank" className="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-400 hover:-translate-y-0.5 transition-all flex items-center gap-2 border border-indigo-400/50">
                                    <Printer className="w-4.5 h-4.5" />
                                    Print Report
                                </a>
                            </div>
                        </div>
                    </div>

                    {/* Search & Filters */}
                    <div className="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div className="md:col-span-4">
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Search Student</label>
                                <div className="relative">
                                    <input 
                                        type="text" 
                                        value={data.student_search}
                                        onChange={e => setData('student_search', e.target.value)}
                                        placeholder="Name, course or student ID..." 
                                        className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 font-medium"
                                    />
                                    <div className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                        <Search className="w-4 h-4" />
                                    </div>
                                </div>
                            </div>

                            <div className="md:col-span-3">
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Department</label>
                                <div className="relative">
                                    <select 
                                        value={data.department}
                                        onChange={e => setData('department', e.target.value)}
                                        className="w-full pl-3.5 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 appearance-none font-medium cursor-pointer"
                                    >
                                        <option value="">All Departments</option>
                                        {departments.map((dept, idx) => (
                                            <option key={idx} value={dept}>{dept}</option>
                                        ))}
                                    </select>
                                    <div className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <ChevronDown className="w-4 h-4" />
                                    </div>
                                </div>
                            </div>

                            <div className="md:col-span-3">
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Current Status</label>
                                <div className="relative">
                                    <select 
                                        value={data.status}
                                        onChange={e => setData('status', e.target.value)}
                                        className="w-full pl-3.5 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 appearance-none font-medium cursor-pointer"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Hearing Scheduled">Hearing Scheduled</option>
                                        <option value="Endorsed to Grievance">Endorsed</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                    <div className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <ChevronDown className="w-4 h-4" />
                                    </div>
                                </div>
                            </div>

                            <div className="md:col-span-2 flex gap-2">
                                <button type="submit" className="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all duration-200 flex items-center justify-center gap-2 active:scale-95">
                                    <Filter className="w-4 h-4" />
                                    Apply
                                </button>
                                <button type="button" onClick={clearFilters} className="px-4 py-2.5 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-all duration-200 active:scale-95" title="Clear Filters">
                                    <RotateCcw className="w-4 h-4" />
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Analytics Data List */}
                    <div className="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl rounded-3xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left">
                                <thead>
                                    <tr className="bg-slate-50/50 dark:bg-slate-800/50">
                                        <th scope="col" className="px-6 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Date & Timestamp</th>
                                        <th scope="col" className="px-6 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Student Profile</th>
                                        <th scope="col" className="px-6 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Violation Details</th>
                                        <th scope="col" className="px-6 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Lifecycle Status</th>
                                        <th scope="col" className="px-6 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Record Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    {cases.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors group">
                                            <td className="px-6 py-5 whitespace-nowrap">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-bold text-slate-900 dark:text-white">{dayjs(item.occurred_at).format('MMM DD, YYYY')}</span>
                                                    <span className="text-[11px] text-slate-400 font-semibold mt-0.5">{dayjs(item.occurred_at).format('hh:mm A')}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-5">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-200/50 shadow-inner group-hover:scale-105 transition-transform">
                                                        {item.student?.initials || '??'}
                                                    </div>
                                                    <div>
                                                        <p className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:text-indigo-400 transition-colors">{item.student?.full_name || 'Anonymous'}</p>
                                                        <p className="text-[11px] text-slate-400 font-bold mt-0.5 uppercase tracking-wider leading-snug max-w-[200px] md:max-w-xs lg:max-w-sm">{item.student?.department || 'N/A'} Department</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-5">
                                                <div className="text-sm font-bold text-slate-900 dark:text-white mb-1 leading-snug">{item.violation?.title || 'Undefined Infraction'}</div>
                                                <div className="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                    <Tag className="w-3.5 h-3.5 text-slate-400" />
                                                    Category: {item.violation?.category || 'Misc'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-5 whitespace-nowrap text-center">
                                                <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border ${getStatusStyle(item.status)} shadow-sm shadow-slate-100/50`}>
                                                    <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                    {item.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-5 whitespace-nowrap text-right">
                                                <a href={route('cases.print', item.id)} target="_blank" 
                                                   className="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-[11px] font-bold uppercase tracking-wider hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 hover:text-indigo-600 dark:text-indigo-400 hover:border-indigo-200 transition-all duration-200 shadow-sm active:scale-95">
                                                    <Printer className="w-3.5 h-3.5" />
                                                    Print
                                                </a>
                                            </td>
                                        </tr>
                                    ))}
                                    {cases.data.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-20 text-center">
                                                <div className="flex flex-col items-center justify-center text-slate-400 max-w-sm mx-auto">
                                                    <div className="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                                                        <ClipboardX className="w-6 h-6" />
                                                    </div>
                                                    <h3 className="text-sm font-bold text-slate-900 dark:text-white mb-1">No Violation Records Found</h3>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Adjust filters, search keywords, or specify different departments to generate custom ledger results.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        {cases.links && cases.links.length > 3 && (
                            <div className="px-6 py-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-center">
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
