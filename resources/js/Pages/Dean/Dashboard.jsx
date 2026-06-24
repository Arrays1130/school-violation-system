import React, { useEffect, useState, useMemo } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { 
    Calendar, 
    Clock, 
    Users as UsersIcon, 
    CheckCircle, 
    AlertTriangle,
    Eye,
    X,
    FileText,
    TrendingUp,
    Activity,
    Search,
    ChevronRight,
    ArrowUpRight
} from "lucide-react";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Bar, Line, Doughnut } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

export default function DeanDashboard({
    auth,
    department,
    stats,
    trends,
    chartData,
    recentCases,
    upcomingHearings = [],
    notifications = [],
    unreadCount = 0,
    topRepeaters = []
}) {
    const [selectedCase, setSelectedCase] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");

    const chartBaseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#f8fafc',
                bodyColor: '#cbd5e1',
                titleFont: { size: 13, weight: '700', family: "'Inter', sans-serif" },
                bodyFont: { size: 13, weight: '500', family: "'Inter', sans-serif" },
                padding: 12,
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
                cornerRadius: 12,
                displayColors: true,
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                boxPadding: 6,
                shadowColor: 'rgba(0, 0, 0, 0.2)',
                shadowBlur: 15,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) label += context.parsed.y + (context.parsed.y === 1 ? ' case' : ' cases');
                        return label;
                    }
                }
            }
        },
        scales: {
            x: { 
                grid: { display: false }, 
                ticks: { font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, color: '#94a3b8' },
                border: { display: false }
            },
            y: { 
                grid: { color: 'rgba(241, 245, 249, 0.5)', drawBorder: false, borderDash: [5, 5] }, 
                ticks: { stepSize: 1, font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, color: '#94a3b8' },
                beginAtZero: true,
                border: { display: false }
            },
        },
    };

    const trendChartData = {
        labels: Object.keys(chartData.monthlyTrend || {}).map(m => {
            const parts = m.split('-');
            const date = new Date(parts[0], parseInt(parts[1]) - 1, 1);
            return date.toLocaleDateString('en-US', { month: 'short' });
        }),
        datasets: [{
            label: 'Incidents',
            data: Object.values(chartData.monthlyTrend),
            borderColor: '#6366f1',
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: true,
            backgroundColor: (context) => {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return 'rgba(99, 102, 241, 0.1)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.01)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.25)');
                return gradient;
            },
        }]
    };

    const commonViolationsData = {
        labels: chartData.topViolations.map(v => v.title.substring(0, 15) + (v.title.length > 15 ? "..." : "")),
        datasets: [{
            label: 'Cases',
            data: chartData.topViolations.map(v => v.count),
            backgroundColor: (context) => {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return 'rgba(99, 102, 241, 0.85)';
                const dataIndex = context.dataIndex;
                const gradients = [
                    ['#818cf8', '#4f46e5'],
                    ['#38bdf8', '#0284c7'],
                    ['#fbbf24', '#d97706'],
                    ['#34d399', '#059669'],
                    ['#f472b6', '#db2777'],
                ];
                const colorPair = gradients[dataIndex % gradients.length];
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, colorPair[0]);
                gradient.addColorStop(1, colorPair[1]);
                return gradient;
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            barThickness: 24,
            hoverBackgroundColor: '#1e293b'
        }]
    };

    const severityColors = {
        'Minor': '#38bdf8', // sky-400
        'Major': '#f59e0b', // amber-500
        'Critical': '#e11d48', // rose-600
    };

    const severityData = {
        labels: Object.keys(chartData.severityBreakdown).map(key => `${key} (${chartData.severityBreakdown[key]})`),
        datasets: [{
            data: Object.values(chartData.severityBreakdown),
            backgroundColor: Object.keys(chartData.severityBreakdown).map(key => severityColors[key] || '#94a3b8'),
            borderColor: '#ffffff',
            borderWidth: 6,
            borderRadius: 8,
            hoverOffset: 8,
        }]
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Dean Dashboard - ${department}`} />

            {/* Sub-header background element */}
            <div className="absolute top-0 left-0 w-full h-[40vh] bg-slate-50 dark:bg-slate-800 -z-10 border-b border-slate-200/50 dark:border-slate-700/50"></div>

            <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                
                {/* ── HEADER SECTION ── */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-widest mb-2">
                            <Activity className="w-3.5 h-3.5" />
                            {department} Analytics
                        </div>
                        <h1 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Dean's Dashboard</h1>
                        <p className="text-slate-500 dark:text-slate-400 mt-1 font-medium">Monitor department cases, hearings, and student incidents.</p>
                    </div>
                </div>

                {/* ── STATS ROW (Glass Cards) ── */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    {/* Total Cases */}
                    <div className="group relative bg-white dark:bg-slate-900 rounded-3xl p-6 ring-1 ring-slate-200/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div className="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br from-blue-500 to-indigo-600 opacity-[0.08] group-hover:opacity-[0.15] rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                        <div className="flex items-start justify-between relative z-10 mb-4">
                            <div className="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-100 text-blue-500 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                                <Activity className="w-5 h-5 stroke-[2.5]" />
                            </div>
                            {trends.total && (
                                <div className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold ${
                                    trends.total.direction === 'up' 
                                        ? (trends.total.isPositive ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400') // More cases = negative usually
                                        : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
                                }`}>
                                    {trends.total.direction === 'up' ? <ArrowUpRight className="w-3 h-3" /> : <TrendingUp className="w-3 h-3 rotate-180" />}
                                    {trends.total.percentage}%
                                </div>
                            )}
                        </div>
                        <div className="relative z-10">
                            <p className="text-[13px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Cases</p>
                            <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{stats.total}</p>
                        </div>
                    </div>

                    {/* Pending Review */}
                    <div className="group relative bg-white dark:bg-slate-900 rounded-3xl p-6 ring-1 ring-slate-200/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div className="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br from-amber-500 to-orange-600 opacity-[0.08] group-hover:opacity-[0.15] rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                        <div className="flex items-start justify-between relative z-10 mb-4">
                            <div className="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-100 text-amber-500 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                                <Clock className="w-5 h-5 stroke-[2.5]" />
                            </div>
                            <div className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                                Action Needed
                            </div>
                        </div>
                        <div className="relative z-10">
                            <p className="text-[13px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Pending Review</p>
                            <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{stats.pending}</p>
                        </div>
                    </div>

                    {/* Resolution Rate */}
                    <div className="group relative bg-white dark:bg-slate-900 rounded-3xl p-6 ring-1 ring-slate-200/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_-10px_rgba(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div className="absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br from-emerald-500 to-teal-600 opacity-[0.08] group-hover:opacity-[0.15] rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                        <div className="flex items-start justify-between relative z-10 mb-4">
                            <div className="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-100 text-emerald-500 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                                <CheckCircle className="w-5 h-5 stroke-[2.5]" />
                            </div>
                            <div className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                                System Healthy
                            </div>
                        </div>
                        <div className="relative z-10">
                            <p className="text-[13px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Resolution Rate</p>
                            <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">
                                {stats.total > 0 ? Math.round((stats.closed / stats.total) * 100) : 0}%
                            </p>
                        </div>
                    </div>
                </div>

                {/* ── BENTO GRID ── */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
                    
                    {/* BENTO ITEM 1: Line Chart (Spans 2 Cols) */}
                    <div className="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px]">
                        <div className="flex items-center justify-between mb-8">
                            <div>
                                <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Violation Trends</h3>
                                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Monthly case logging over time</p>
                            </div>
                            <div className="flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-800">
                                <div className="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                <span className="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Live</span>
                            </div>
                        </div>
                        <div className="flex-1 min-h-0 w-full relative">
                            <Line data={trendChartData} options={{...chartBaseOptions, maintainAspectRatio: false}} />
                        </div>
                    </div>

                    {/* BENTO ITEM 2: Severity Doughnut (Spans 1 Col) */}
                    <div className="bg-white dark:bg-slate-900 rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px]">
                        <div className="flex items-center justify-between mb-2">
                            <div>
                                <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Severity Split</h3>
                                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Cases by severity</p>
                            </div>
                            <div className="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400">
                                <AlertTriangle className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="flex-1 min-h-0 w-full flex items-center justify-center relative mt-4">
                            <Doughnut data={severityData} options={{ 
                                ...chartBaseOptions, 
                                maintainAspectRatio: false, 
                                cutout: '75%', 
                                scales: { x: { display: false }, y: { display: false } },
                                plugins: { ...chartBaseOptions.plugins, legend: { display: true, position: 'bottom', labels: { boxWidth: 8, font: { size: 12, weight: '600', family: "'Inter', sans-serif" }, color: '#64748b', usePointStyle: true, padding: 20 } } } 
                            }} />
                            <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-30px]">
                                <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{stats.total}</p>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total</p>
                            </div>
                        </div>
                    </div>

                    {/* BENTO ITEM 3: Recent Activity (Spans 2 Cols) */}
                    <div className="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 flex flex-col h-[400px] overflow-hidden">
                        <div className="p-8 pb-4 flex items-center justify-between bg-white dark:bg-slate-900 z-10 border-b border-slate-50 flex-none">
                            <div>
                                <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Recent Violations</h3>
                                <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Latest recorded incidents</p>
                            </div>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input 
                                    type="text" 
                                    placeholder="Search..." 
                                    className="pl-9 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-48 transition-all"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto no-scrollbar p-6 pt-2 space-y-4">
                            {recentCases.map((item) => (
                                <div key={item.id} onClick={() => { setSelectedCase(item); setIsModalOpen(true); }} className="group relative flex items-start gap-4 p-3 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 rounded-2xl transition-all cursor-pointer">
                                    <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm flex-shrink-0 group-hover:scale-110 transition-transform">
                                        {(item.student?.full_name || 'U').substring(0, 1)}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-600 dark:text-indigo-400 transition-colors">{item.student?.full_name || 'Anonymous'}</p>
                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 truncate">{item.violation?.title}</p>
                                        <div className="flex items-center gap-2 mt-2">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${item.status === 'Closed' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 text-emerald-700' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-100 text-amber-700'}`}>
                                                {item.status}
                                            </span>
                                            <span className="text-[10px] font-semibold text-slate-400">{new Date(item.created_at).toLocaleDateString()}</span>
                                        </div>
                                    </div>
                                    <div className="text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Eye className="w-5 h-5" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* BENTO ITEM 4: Sidebar: Hearings & Repeaters (Spans 1 Col) */}
                    <div className="flex flex-col h-[400px] gap-6">
                        {/* Upcoming Hearings */}
                        <div className="bg-white dark:bg-slate-900 rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 overflow-hidden flex flex-col flex-1 min-h-0">
                            <div className="px-6 py-4 border-b border-slate-50 bg-white dark:bg-slate-900 flex-none">
                                <h3 className="text-sm font-black text-slate-900 dark:text-white tracking-tight">Upcoming Hearings</h3>
                            </div>
                            <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-2">
                                {upcomingHearings.length > 0 ? upcomingHearings.map(h => (
                                    <div key={h.id} className="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 transition-colors group/hearing">
                                        <div className="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400 group-hover/hearing:scale-110 transition-transform">
                                            <Calendar className="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p className="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover/hearing:text-indigo-600 dark:text-indigo-400 transition-colors">{h.case.student.full_name}</p>
                                            <p className="text-[10px] text-slate-400 mt-0.5 font-medium">{new Date(h.scheduled_at).toLocaleDateString()}</p>
                                            <p className="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5 font-bold uppercase tracking-wider">{h.venue}</p>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="text-center py-6 h-full flex flex-col items-center justify-center">
                                        <Calendar className="w-6 h-6 text-slate-200 mx-auto mb-2" />
                                        <p className="text-slate-400 text-[11px] font-medium uppercase tracking-wider">No scheduled hearings</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Frequent Offenders */}
                        <div className="bg-white dark:bg-slate-900 rounded-[2rem] ring-1 ring-slate-200/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.06)] transition-all duration-500 overflow-hidden flex flex-col flex-1 min-h-0">
                            <div className="px-6 py-4 border-b border-slate-50 bg-white dark:bg-slate-900 flex-none">
                                <h3 className="text-sm font-black text-slate-900 dark:text-white tracking-tight">Frequent Offenders</h3>
                            </div>
                            <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-2">
                                {topRepeaters.map((s, idx) => (
                                    <div key={s.id} className="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <span className={`w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-extrabold border ${idx === 0 ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border-rose-100' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-100 dark:border-slate-800'}`}>
                                                {idx + 1}
                                            </span>
                                            <span className="text-xs font-bold text-slate-800 dark:text-slate-200">{s.full_name}</span>
                                        </div>
                                        <span className="text-[10px] font-bold text-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded border border-indigo-100/50">
                                            {s.cases_count} cases
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {/* Simple Case Modal */}
            {isModalOpen && selectedCase && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-all">
                    <div className="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-slate-200/50">
                        <div className="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                            <h3 className="text-lg font-black text-slate-900 dark:text-white">Case Details</h3>
                            <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 p-2 rounded-full transition-colors">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="p-8 overflow-y-auto">
                            <div className="flex items-center gap-4 mb-8">
                                <div className="h-16 w-16 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-2xl font-black">
                                    {selectedCase.student?.full_name?.charAt(0)}
                                </div>
                                <div>
                                    <h4 className="text-xl font-black text-slate-900 dark:text-white">{selectedCase.student?.full_name}</h4>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">{selectedCase.student?.id_number} • {selectedCase.student?.department}</p>
                                    <div className="mt-2">
                                        <span className="px-2.5 py-1 bg-slate-100 rounded-lg text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">{selectedCase.student?.year_level}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Graphical Timeline Tracker */}
                            {(() => {
                                const currentStep = selectedCase.status === 'Closed' ? 4 :
                                                    selectedCase.status === 'Hearing' ? 3 :
                                                    selectedCase.status === 'Hearing Scheduled' ? 2 : 1;
                                const steps = [
                                    { title: "Pending", icon: FileText },
                                    { title: "Scheduled", icon: Calendar },
                                    { title: "Hearing", icon: UsersIcon },
                                    { title: "Closed", icon: CheckCircle },
                                ];
                                return (
                                    <div className="py-8 border-y border-slate-100 dark:border-slate-800 mb-8">
                                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8 text-center">Case Progress</p>
                                        <div className="relative max-w-md mx-auto">
                                            {/* Background Line */}
                                            <div className="absolute top-5 left-[12%] right-[12%] h-1 bg-slate-100 rounded-full z-0"></div>
                                            {/* Active Line Fill */}
                                            <div className="absolute top-5 left-[12%] h-1 bg-indigo-500 rounded-full z-0 transition-all duration-1000"
                                                 style={{ width: `${((currentStep - 1) / 3) * 76}%` }}></div>
                                            
                                            <div className="grid grid-cols-4 gap-2 relative z-10">
                                                {steps.map((step, idx) => {
                                                    const Icon = step.icon;
                                                    const isCompleted = idx < currentStep;
                                                    const isCurrent = idx + 1 === currentStep;
                                                    const bg = isCompleted ? 'bg-indigo-600 text-white' : 
                                                               isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-white dark:bg-slate-900 text-slate-300 border-2 border-slate-200 dark:border-slate-700';
                                                    return (
                                                        <div key={step.title} className="flex flex-col items-center">
                                                            <div className={`w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-500 ${bg}`}>
                                                                <Icon className="w-5 h-5" />
                                                            </div>
                                                            <p className={`text-[11px] mt-4 font-bold uppercase tracking-wider ${isCompleted || isCurrent ? 'text-slate-900 dark:text-white' : 'text-slate-400'}`}>
                                                                {step.title}
                                                            </p>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })()}

                            <div className="grid grid-cols-2 gap-4 mb-6">
                                <div className="p-5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Policy Violation</p>
                                    <p className="font-bold text-slate-900 dark:text-white text-sm">{selectedCase.violation?.title}</p>
                                </div>
                                <div className="p-5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Case Status</p>
                                    <p className="font-bold text-slate-900 dark:text-white text-sm">{selectedCase.status}</p>
                                </div>
                            </div>
                            <div>
                                <p className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Case Narrative</p>
                                <div className="p-6 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed font-medium">
                                    {selectedCase.description || 'No descriptive evidence provided in the initial filing.'}
                                </div>
                            </div>
                        </div>
                        <div className="px-8 py-6 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 mt-auto">
                            <button onClick={() => setIsModalOpen(false)} className="px-6 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 rounded-xl transition-colors">Close</button>
                            <a href={`/cases/${selectedCase.id}`} className="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-md hover:shadow-lg hover:shadow-indigo-500/20 transition-all">
                                Full Details
                            </a>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
