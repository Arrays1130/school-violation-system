import React, { useEffect, useRef } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    ShieldCheck, ArrowLeft, Gavel, CheckCircle2, Clock4, Percent,
    Filter, RotateCcw, List, Eye, Printer, Database
} from 'lucide-react';
import dayjs from 'dayjs';

export default function Sanctions({ 
    cases, departments, totalSanctions, sanctionsServed, sanctionsPending, complianceRate, filters 
}) {
    const { data, setData, get } = useForm({
        department: filters.department || '',
        severity: filters.severity || '',
        sanction_status: filters.sanction_status || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        const timeout = setTimeout(() => {
            get(route('reports.sanctions'), {
                preserveState: true,
                preserveScroll: true
            });
        }, 300);
        return () => clearTimeout(timeout);
    }, [data.department, data.severity, data.sanction_status, data.date_from, data.date_to]);

    const handleSearch = (e) => {
        e.preventDefault();
        get(route('reports.sanctions'), {
            preserveState: true,
            preserveScroll: true
        });
    };

    const clearFilters = () => {
        router.get(route('reports.sanctions'));
    };

    const statCards = [
        { label: 'Total Sanctions', value: totalSanctions, icon: Gavel, color: 'indigo' },
        { label: 'Sanction Served', value: sanctionsServed, icon: CheckCircle2, color: 'emerald' },
        { label: 'Pending Sanction', value: sanctionsPending, icon: Clock4, color: 'amber' },
        { label: 'Compliance Rate', value: `${complianceRate}%`, icon: Percent, color: 'blue' },
    ];

    const pal = {
        indigo: { bg: 'bg-indigo-50 dark:bg-indigo-900/20/80', icon: 'text-indigo-600 dark:text-indigo-400', border: 'border-indigo-100', num: 'text-indigo-700', shadow: 'shadow-indigo-500/10' },
        emerald: { bg: 'bg-emerald-50 dark:bg-emerald-900/20/80', icon: 'text-emerald-600 dark:text-emerald-400', border: 'border-emerald-100', num: 'text-emerald-700', shadow: 'shadow-emerald-500/10' },
        amber: { bg: 'bg-amber-50 dark:bg-amber-900/20/80', icon: 'text-amber-600 dark:text-amber-400', border: 'border-amber-100', num: 'text-amber-700', shadow: 'shadow-amber-500/10' },
        blue: { bg: 'bg-blue-50/80', icon: 'text-blue-600', border: 'border-blue-100', num: 'text-blue-700', shadow: 'shadow-blue-500/10' },
    };

    const hasFilters = data.department || data.severity || data.sanction_status || data.date_from || data.date_to;

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Sanctions Report</h2>}
        >
            <Head title="Sanctions Report" />

            <div className="py-8">
                <div className="space-y-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Header */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-emerald-950 p-8 shadow-2xl shadow-emerald-900/20 border border-emerald-900/50">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_55%)]"></div>
                        <div className="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl"></div>

                        <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-6 z-10">
                            <div className="flex items-center gap-5">
                                <Link href={route('reports.index')}
                                    className="w-11 h-11 rounded-2xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-emerald-100 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all backdrop-blur-md hover:-translate-x-1 shadow-sm">
                                    <ArrowLeft className="w-5 h-5" />
                                </Link>
                                <div>
                                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-emerald-100 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                        <ShieldCheck className="w-3.5 h-3.5" />
                                        Violation Outcomes
                                    </div>
                                    <h1 className="text-3xl font-black text-white tracking-tight">Sanctions Report</h1>
                                    <p className="text-emerald-100/70 text-sm mt-2 font-medium">Track imposed sanctions, compliance status, and sanction outcomes per student.</p>
                                </div>
                            </div>
                            <div className="text-sm px-5 py-2.5 bg-white/10 dark:bg-slate-900/10 rounded-xl border border-white/10 shadow-sm text-emerald-100 font-bold shrink-0 backdrop-blur-md">
                                As of {dayjs().format('MMMM DD, YYYY')}
                            </div>
                        </div>
                    </div>

                    {/* ─── Stat Cards ─── */}
                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        {statCards.map((card, idx) => {
                            const p = pal[card.color];
                            const Icon = card.icon;
                            return (
                                <div key={idx} className={`bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border ${p.border} rounded-3xl p-6 shadow-md ${p.shadow} hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group`}>
                                    <div className={`absolute -right-6 -bottom-6 w-24 h-24 rounded-full ${p.bg} opacity-50 group-hover:scale-150 transition-transform duration-500`}></div>
                                    <div className="relative z-10">
                                        <div className={`w-12 h-12 rounded-2xl ${p.bg} flex items-center justify-center mb-4 ring-1 ring-white/50 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300`}>
                                            <Icon className={`w-5 h-5 ${p.icon}`} />
                                        </div>
                                        <div className={`text-3xl font-black ${p.num} tabular-nums tracking-tight`}>{card.value}</div>
                                        <div className="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-2">{card.label}</div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Compliance Bar */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-700/60 rounded-3xl p-8 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Overall Sanction Compliance</span>
                            <span className="text-xl font-black text-emerald-600 dark:text-emerald-400">{complianceRate}%</span>
                        </div>
                        <div className="h-4 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div className="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-1000"
                                style={{ width: `${complianceRate}%` }}></div>
                        </div>
                        <div className="flex justify-between text-xs font-bold text-slate-400 mt-3 uppercase tracking-wider">
                            <span>{sanctionsServed} served</span>
                            <span>{sanctionsPending} pending</span>
                        </div>
                    </div>

                    {/* ─── Filters ─── */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-700/60 rounded-2xl p-6 shadow-sm">
                        <form onSubmit={handleSearch} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 items-end">
                            <div>
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Department</label>
                                <select 
                                    value={data.department}
                                    onChange={e => setData('department', e.target.value)}
                                    className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer"
                                >
                                    <option value="">All Departments</option>
                                    {departments.map((dept, idx) => (
                                        <option key={idx} value={dept}>{dept}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Severity</label>
                                <select 
                                    value={data.severity}
                                    onChange={e => setData('severity', e.target.value)}
                                    className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer"
                                >
                                    <option value="">All Severities</option>
                                    <option value="Minor">Minor</option>
                                    <option value="Major">Major</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sanction Status</label>
                                <select 
                                    value={data.sanction_status}
                                    onChange={e => setData('sanction_status', e.target.value)}
                                    className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all appearance-none cursor-pointer"
                                >
                                    <option value="">All</option>
                                    <option value="served">✅ Sanction Served</option>
                                    <option value="pending">⏳ Pending Sanction</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date From</label>
                                <input 
                                    type="date" 
                                    value={data.date_from}
                                    onChange={e => setData('date_from', e.target.value)}
                                    className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                />
                            </div>

                            <div className="flex gap-2">
                                <div className="flex-1">
                                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date To</label>
                                    <input 
                                        type="date" 
                                        value={data.date_to}
                                        onChange={e => setData('date_to', e.target.value)}
                                        className="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                    />
                                </div>
                            </div>

                            <div className="flex gap-3 lg:col-span-5 pt-2">
                                <button type="submit" className="flex-1 sm:flex-none px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2 active:scale-95">
                                    <Filter className="w-4 h-4" /> Apply Filters
                                </button>
                                {hasFilters && (
                                    <button type="button" onClick={clearFilters} className="px-6 py-3 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 rounded-xl flex items-center gap-2 border border-slate-200 dark:border-slate-700 transition-all text-sm font-bold active:scale-95">
                                        <RotateCcw className="w-4 h-4" /> Clear
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>

                    {/* ─── Table ─── */}
                    <div className="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/60 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
                        <div className="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white/50 dark:bg-slate-900/50">
                            <div className="flex items-center gap-3">
                                <div className="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100">
                                    <List className="w-4 h-4" />
                                </div>
                                <span className="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                    {cases.total} record{cases.total !== 1 ? 's' : ''} found
                                </span>
                            </div>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Violation</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Offense</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Sanction Imposed</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Closed</th>
                                        <th className="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50 bg-white/50 dark:bg-slate-900/50">
                                    {cases.data.map((item) => {
                                        const served = item.status === 'Closed';
                                        
                                        const sevColorMap = {
                                            'Minor': 'bg-blue-50 text-blue-700 border-blue-100',
                                            'Major': 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 border-amber-100',
                                            'Critical': 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 border-rose-100',
                                        };
                                        const sevColor = sevColorMap[item.violation?.severity] || 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
                                        
                                        const lvl = item.offense_level || 1;
                                        let sfx = 'th';
                                        if (lvl % 10 === 1 && lvl % 100 !== 11) sfx = 'st';
                                        else if (lvl % 10 === 2 && lvl % 100 !== 12) sfx = 'nd';
                                        else if (lvl % 10 === 3 && lvl % 100 !== 13) sfx = 'rd';

                                        return (
                                            <tr key={item.id} className="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/20 transition-colors group">
                                                {/* Student */}
                                                <td className="px-8 py-5">
                                                    <div className="flex items-center gap-4">
                                                        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-200/50 shadow-inner group-hover:scale-105 transition-transform">
                                                            {item.student?.initials || '??'}
                                                        </div>
                                                        <div>
                                                            <div className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:text-emerald-400 transition-colors">{item.student?.full_name || 'Unknown'}</div>
                                                            <div className="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{item.student?.student_id || '—'}</div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Violation */}
                                                <td className="px-8 py-5">
                                                    <div className="text-sm font-bold text-slate-800 dark:text-slate-200 leading-snug max-w-[180px] truncate mb-1.5" title={item.violation?.title}>
                                                        {item.violation?.title || 'N/A'}
                                                    </div>
                                                    <span className={`inline-flex px-2 py-0.5 rounded text-[10px] font-black border uppercase tracking-wider ${sevColor}`}>
                                                        {item.violation?.severity || '—'}
                                                    </span>
                                                </td>

                                                {/* Offense Level */}
                                                <td className="px-8 py-5">
                                                    <div className="inline-flex items-baseline gap-1 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                                                        <span className="text-sm font-black text-slate-900 dark:text-white">{lvl}</span>
                                                        <span className="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">{sfx}</span>
                                                    </div>
                                                </td>

                                                {/* Sanction Imposed */}
                                                <td className="px-8 py-5">
                                                    <div className="text-sm font-bold text-slate-800 dark:text-slate-200 max-w-[200px] whitespace-normal leading-relaxed">
                                                        {item.sanction || <span className="text-slate-400 italic font-medium">To be determined</span>}
                                                    </div>
                                                </td>

                                                {/* Status */}
                                                <td className="px-8 py-5 text-center">
                                                    {served ? (
                                                        <div className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:text-emerald-400 shadow-sm shadow-emerald-500/20" title="Sanction Served">
                                                            <CheckCircle2 className="w-5 h-5" />
                                                        </div>
                                                    ) : (
                                                        <div className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 dark:text-amber-400 shadow-sm shadow-amber-500/20" title="Pending">
                                                            <Clock4 className="w-5 h-5" />
                                                        </div>
                                                    )}
                                                </td>

                                                {/* Date Closed */}
                                                <td className="px-8 py-5">
                                                    {item.closed_at ? (
                                                        <div className="text-sm font-bold text-slate-800 dark:text-slate-200">
                                                            {dayjs(item.closed_at).format('MMM DD, YYYY')}
                                                        </div>
                                                    ) : (
                                                        <div className="text-sm text-slate-400 font-medium italic">—</div>
                                                    )}
                                                </td>

                                                {/* Action */}
                                                <td className="px-8 py-5 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link href={route('cases.show', item.id)} className="p-2.5 text-slate-400 hover:text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:bg-emerald-900/20 rounded-xl transition-all shadow-sm bg-white dark:bg-slate-900 border border-transparent hover:border-emerald-200" title="View Case">
                                                            <Eye className="w-4 h-4" />
                                                        </Link>
                                                        <a href={route('cases.print', item.id)} target="_blank" className="p-2.5 text-slate-400 hover:text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:bg-emerald-900/20 rounded-xl transition-all shadow-sm bg-white dark:bg-slate-900 border border-transparent hover:border-emerald-200" title="Print Document">
                                                            <Printer className="w-4 h-4" />
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                    
                                    {cases.data.length === 0 && (
                                        <tr>
                                            <td colSpan="7" className="px-8 py-24 text-center">
                                                <div className="flex flex-col items-center justify-center">
                                                    <div className="w-16 h-16 mb-5 rounded-3xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center border border-slate-100 dark:border-slate-800 shadow-inner">
                                                        <Database className="w-8 h-8 text-slate-300" />
                                                    </div>
                                                    <p className="text-slate-900 dark:text-white font-black text-base mb-1">No Sanction Records</p>
                                                    <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">Adjust your filters to see more results.</p>
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
                                                    ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-200' 
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
