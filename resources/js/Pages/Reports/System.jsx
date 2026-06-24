import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    LayoutDashboard, ArrowLeft, FolderOpen, Clock, Gavel, Send, CheckCircle,
    TrendingUp, PieChart, Building2, AlertTriangle
} from 'lucide-react';
import dayjs from 'dayjs';
import { 
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, ResponsiveContainer,
    PieChart as RechartsPieChart, Pie, Cell, Legend
} from 'recharts';

export default function System({ 
    total, pending, hearing, endorsed, closed, 
    byDepartment, topViolations, 
    monthlyMinorData, monthlyMajorData, currentYear 
}) {

    // Format chart data for LineChart
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const trendData = months.map((m, idx) => ({
        name: m,
        Minor: Object.values(monthlyMinorData || {})[idx] || 0,
        Major: Object.values(monthlyMajorData || {})[idx] || 0,
    }));

    // Format chart data for PieChart
    const pieData = [
        { name: 'Pending', value: pending, color: '#f59e0b' },
        { name: 'Hearing', value: hearing, color: '#6366f1' },
        { name: 'Endorsed', value: endorsed, color: '#f43f5e' },
        { name: 'Closed', value: closed, color: '#10b981' },
    ].filter(item => item.value > 0);

    const cards = [
        { label: 'Total Cases', value: total, icon: FolderOpen, gradient: 'from-blue-500 to-indigo-600', color: 'text-blue-500' },
        { label: 'Pending', value: pending, icon: Clock, gradient: 'from-amber-500 to-orange-600', color: 'text-amber-500' },
        { label: 'Hearing', value: hearing, icon: Gavel, gradient: 'from-indigo-500 to-violet-600', color: 'text-indigo-500' },
        { label: 'Endorsed', value: endorsed, icon: Send, gradient: 'from-rose-500 to-pink-600', color: 'text-rose-500' },
        { label: 'Closed', value: closed, icon: CheckCircle, gradient: 'from-emerald-500 to-teal-600', color: 'text-emerald-500' },
    ];

    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            return (
                <div className="bg-slate-900/95 backdrop-blur-md text-white p-3 rounded-xl border border-slate-700/50 shadow-xl">
                    <p className="text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">{label}</p>
                    {payload.map((entry, index) => (
                        <div key={index} className="flex items-center gap-2 mb-1 last:mb-0 text-sm font-semibold">
                            <span className="w-2 h-2 rounded-full" style={{ backgroundColor: entry.color }}></span>
                            <span>{entry.name}:</span>
                            <span className="text-white">{entry.value}</span>
                        </div>
                    ))}
                </div>
            );
        }
        return null;
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">System Reports</h2>}
        >
            <Head title="System Overview Reports" />

            <div className="relative space-y-6 max-w-[1400px] mx-auto pb-12 pt-6 px-4 sm:px-6 lg:px-8">
                
                {/* Sub-header background element */}
                <div className="absolute top-0 left-0 w-full h-[40vh] bg-slate-50/50 dark:bg-slate-800/50 -z-10 border-b border-slate-200/50 dark:border-slate-700/50"></div>

                {/* Header */}
                <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6 pt-4 mb-8">
                    <div className="flex items-center gap-5">
                        <Link href={route('reports.index')}
                            className="w-11 h-11 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 transition-all hover:-translate-x-1">
                            <ArrowLeft className="w-5 h-5" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-widest mb-2">
                                <LayoutDashboard className="w-3.5 h-3.5" />
                                System Overview
                            </div>
                            <h1 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">System Reports</h1>
                            <p className="text-slate-500 dark:text-slate-400 mt-1 font-medium text-sm">Overview statistics — total cases, status breakdown & department analysis.</p>
                        </div>
                    </div>
                    <div className="text-sm px-5 py-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm text-slate-500 dark:text-slate-400 font-bold shrink-0">
                        As of {dayjs().format('MMMM DD, YYYY')}
                    </div>
                </div>

                {/* ─── Stat Cards ─── */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
                    {cards.map((card, idx) => {
                        const Icon = card.icon;
                        return (
                            <div key={idx} className="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-3xl p-6 ring-1 ring-slate-200/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between min-h-[140px]">
                                <div className={`absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br ${card.gradient} opacity-[0.08] group-hover:opacity-[0.15] rounded-full blur-2xl transition-all duration-500 group-hover:scale-150`}></div>
                                
                                <div className="flex items-start justify-between relative z-10 mb-4">
                                    <div className={`p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-100 ${card.color} group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300`}>
                                        <Icon className="w-5 h-5 stroke-[2.5]" />
                                    </div>
                                </div>
                                
                                <div className="relative z-10">
                                    <p className="text-[12px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">{card.label}</p>
                                    <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter tabular-nums">{card.value.toLocaleString()}</p>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* ─── Bento Grid Layout ─── */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {/* 1. Monthly Trend Chart (Spans 2 Cols) */}
                    <div className="lg:col-span-2 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col min-h-[400px]">
                        <div className="flex items-center justify-between mb-8">
                            <div>
                                <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">Comparative Monthly Cases</h3>
                                <p className="text-sm font-bold text-slate-500 dark:text-slate-400 mt-1">Minor vs Major infractions for {currentYear}</p>
                            </div>
                            <div className="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 ring-1 ring-indigo-100">
                                <TrendingUp className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="flex-1 w-full relative">
                            <ResponsiveContainer width="100%" height="100%">
                                <LineChart data={trendData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                                    <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12, fontWeight: 600 }} dy={10} />
                                    <YAxis axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12, fontWeight: 600 }} dx={-10} />
                                    <RechartsTooltip content={<CustomTooltip />} />
                                    <Line type="monotone" dataKey="Minor" stroke="#3b82f6" strokeWidth={3} dot={{ r: 4, fill: '#3b82f6', strokeWidth: 2, stroke: '#fff' }} activeDot={{ r: 6 }} />
                                    <Line type="monotone" dataKey="Major" stroke="#f59e0b" strokeWidth={3} dot={{ r: 4, fill: '#f59e0b', strokeWidth: 2, stroke: '#fff' }} activeDot={{ r: 6 }} />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    {/* 2. Status Donut (Spans 1 Col) */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col min-h-[400px]">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">Status Distribution</h3>
                                <p className="text-sm font-bold text-slate-500 dark:text-slate-400 mt-1">Lifecycle of current cases</p>
                            </div>
                            <div className="p-3 rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                                <PieChart className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="flex-1 w-full flex items-center justify-center relative">
                            {total > 0 ? (
                                <div className="w-full h-full relative">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <RechartsPieChart>
                                            <Pie
                                                data={pieData}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={70}
                                                outerRadius={100}
                                                paddingAngle={5}
                                                dataKey="value"
                                                stroke="none"
                                            >
                                                {pieData.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={entry.color} />
                                                ))}
                                            </Pie>
                                            <RechartsTooltip content={<CustomTooltip />} />
                                        </RechartsPieChart>
                                    </ResponsiveContainer>
                                    <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                        <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{total}</p>
                                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total</p>
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm font-bold text-slate-400 uppercase tracking-widest">No cases yet</p>
                            )}
                        </div>
                    </div>

                    {/* 3. Department Breakdown (Spans 2 Cols) */}
                    <div className="lg:col-span-2 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px] overflow-hidden">
                        <div className="p-8 pb-4 flex items-center justify-between bg-white dark:bg-slate-900 z-10 border-b border-slate-50 flex-none">
                            <div>
                                <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">Cases by Department</h3>
                                <p className="text-sm font-bold text-slate-500 dark:text-slate-400 mt-1">Volume per college</p>
                            </div>
                            <div className="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 ring-1 ring-emerald-100">
                                <Building2 className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto custom-scrollbar p-8 pt-4">
                            {byDepartment.length === 0 ? (
                                <div className="h-full flex items-center justify-center">
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">No data available</p>
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    {byDepartment.map((row, idx) => {
                                        const maxDept = Math.max(...byDepartment.map(d => d.total));
                                        const pct = maxDept > 0 ? Math.round((row.total / maxDept) * 100) : 0;
                                        return (
                                            <div key={idx} className="group">
                                                <div className="flex justify-between items-center mb-2.5">
                                                    <span className="text-sm font-bold text-slate-800 dark:text-slate-200">{row.department || 'Unassigned'}</span>
                                                    <span className="text-sm font-black text-slate-900 dark:text-white">{row.total} <span className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">cases</span></span>
                                                </div>
                                                <div className="h-3 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                                    <div className="h-full bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full transition-all duration-1000 group-hover:from-indigo-500 group-hover:to-indigo-700"
                                                         style={{ width: `${pct}%` }}></div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* 4. Top Violations (Spans 1 Col) */}
                    <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px] overflow-hidden">
                        <div className="p-8 pb-4 flex items-center justify-between border-b border-slate-50 flex-none bg-white dark:bg-slate-900 z-10">
                            <div>
                                <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">Top Violations</h3>
                                <p className="text-sm font-bold text-slate-500 dark:text-slate-400 mt-1">Most frequent infractions</p>
                            </div>
                            <div className="p-3 rounded-2xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 ring-1 ring-rose-100">
                                <AlertTriangle className="w-5 h-5" />
                            </div>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto custom-scrollbar p-6 pt-4 space-y-3">
                            {topViolations.length === 0 ? (
                                <div className="h-full flex items-center justify-center">
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">No violations</p>
                                </div>
                            ) : (
                                topViolations.map((v, i) => {
                                    const sevMap = {
                                        'Minor': 'bg-blue-50 text-blue-700 border-blue-100',
                                        'Major': 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 border-amber-100',
                                        'Critical': 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 border-rose-100',
                                    };
                                    const sc = sevMap[v.severity] || 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
                                    
                                    return (
                                        <div key={i} className="flex items-center gap-4 p-4 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 ring-1 ring-transparent hover:ring-slate-100 transition-all duration-200 group">
                                            <div className="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-sm font-black text-slate-400 shrink-0 group-hover:bg-indigo-50 dark:bg-indigo-900/20 group-hover:text-indigo-600 dark:text-indigo-400 transition-colors shadow-inner">
                                                {i + 1}
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <div className="text-sm font-bold text-slate-900 dark:text-white truncate mb-1" title={v.title}>{v.title}</div>
                                                <div className="flex items-center gap-2">
                                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold border ${sc} tracking-wider uppercase`}>{v.severity}</span>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <span className="text-lg font-black text-slate-800 dark:text-slate-200">{v.total}</span>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>

                </div>
            </div>
            
            <style>{`
                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background-color: #e2e8f0;
                    border-radius: 20px;
                }
                .custom-scrollbar:hover::-webkit-scrollbar-thumb {
                    background-color: #cbd5e1;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
