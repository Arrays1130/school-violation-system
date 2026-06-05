import { useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Users2, FileText, AlertCircle, Gavel, ArrowUpRight, TrendingUp, ShieldAlert, Zap, Layers, Globe, ChevronRight, Plus, Activity } from 'lucide-react';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, BarElement,
    ArcElement, PointElement, LineElement, Title, Tooltip, Legend,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Title, Tooltip, Legend);

const statusConfig = {
    'Pending':           { bg: 'bg-amber-50',     text: 'text-amber-700',     dot: 'bg-amber-500',  border: 'border-amber-200' },
    'Open':              { bg: 'bg-red-50',       text: 'text-red-700',       dot: 'bg-red-500',    border: 'border-red-200'  },
    'Closed':            { bg: 'bg-green-50',     text: 'text-green-700',     dot: 'bg-green-500',  border: 'border-green-200' },
    'Hearing Scheduled': { bg: 'bg-blue-50',      text: 'text-blue-700',      dot: 'bg-blue-500',   border: 'border-blue-200' },
    'Endorsed':          { bg: 'bg-purple-50',    text: 'text-purple-700',    dot: 'bg-purple-500', border: 'border-purple-200' },
};

function StatusBadge({ status }) {
    const cfg = statusConfig[status] || { bg: 'bg-gray-50', text: 'text-gray-600', dot: 'bg-gray-400', border: 'border-gray-200' };
    return (
        <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border ${cfg.bg} ${cfg.text} ${cfg.border}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`}></span>
            {status}
        </span>
    );
}

export default function Dashboard({ auth, stats, casesPerDept, casesPerSeverity, studentsWithViolations = [], recentCases = [], monthlyTrend = {}, topViolations = [], trends = {} }) {

    const statCards = [
        {
            key: 'total_students',
            label: 'Total Students',
            value: stats.total_students,
            icon: Users2,
            color: 'text-blue-600',
            bg: 'bg-blue-50',
            href: '/students',
        },
        {
            key: 'total_cases',
            label: 'Disciplinary Cases',
            value: stats.total_cases,
            icon: FileText,
            color: 'text-indigo-600',
            bg: 'bg-indigo-50',
            href: '/cases',
        },
        {
            key: 'open_cases',
            label: 'Active Cases',
            value: stats.open_cases,
            icon: ShieldAlert,
            color: 'text-red-600',
            bg: 'bg-red-50',
            href: '/cases',
        },
        {
            key: 'hearings_this_month',
            label: 'Scheduled Hearings',
            value: stats.hearings_this_month,
            icon: Gavel,
            color: 'text-amber-600',
            bg: 'bg-amber-50',
            href: '/reports',
        },
    ];

    const chartBaseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.98)',
                titleColor: '#0f172a',
                bodyColor: '#334155',
                titleFont: { size: 12, weight: '700', family: "'Inter', sans-serif" },
                bodyFont: { size: 12, weight: '500', family: "'Inter', sans-serif" },
                padding: 12,
                borderColor: '#f1f5f9',
                borderWidth: 1,
                cornerRadius: 10,
                displayColors: true,
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
                boxPadding: 6,
                shadowColor: 'rgba(0, 0, 0, 0.05)',
                shadowBlur: 10,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y + (context.parsed.y === 1 ? ' case' : ' cases');
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: { 
                grid: { display: false }, 
                ticks: { font: { size: 11, weight: '500', family: "'Inter', sans-serif" }, color: '#64748b' } 
            },
            y: { 
                grid: { color: '#f1f5f9', drawBorder: false }, 
                ticks: { stepSize: 1, font: { size: 11, weight: '500', family: "'Inter', sans-serif" }, color: '#64748b' },
                beginAtZero: true 
            },
        },
    };

    const deptChartData = {
        labels: Object.keys(casesPerDept),
        datasets: [{
            label: 'Cases',
            data: Object.values(casesPerDept),
            backgroundColor: (context) => {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return 'rgba(59, 130, 246, 0.85)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, '#3b82f6');
                gradient.addColorStop(1, '#6366f1');
                return gradient;
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
            barThickness: 28,
            hoverBackgroundColor: (context) => {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return '#2563eb';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, '#2563eb');
                gradient.addColorStop(1, '#4f46e5');
                return gradient;
            },
        }],
    };

    const severityChartData = {
        labels: Object.keys(casesPerSeverity),
        datasets: [{
            data: Object.values(casesPerSeverity),
            backgroundColor: ['#0d9488', '#d97706', '#e11d48'], // Polished Teal, Amber, Rose
            borderColor: '#ffffff',
            borderWidth: 3,
            borderRadius: 4,
            hoverOffset: 4,
        }],
    };

    const trendChartData = {
        labels: Object.keys(monthlyTrend || {}).map(m => {
            const parts = m.split('-');
            const date = new Date(parts[0], parseInt(parts[1]) - 1, 1);
            return date.toLocaleDateString('en-US', { month: 'short' });
        }),
        datasets: [{
            label: 'Incidents',
            data: Object.values(monthlyTrend || {}),
            borderColor: '#6366f1',
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 2.5,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.4,
            fill: true,
            backgroundColor: (context) => {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return 'rgba(99, 102, 241, 0.08)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.005)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.22)');
                return gradient;
            },
        }],
    };

    return (
        <AuthenticatedLayout user={auth.user} header="Disciplinary Dashboard">
            <Head title="Dashboard" />

            <div className="max-w-7xl mx-auto flex flex-col gap-6">
                
                {/* ── MODERN PRISM HEADER ── */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <Activity className="w-3.5 h-3.5" />
                                Analytics
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">System Overview</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Welcome back, {auth.user.name}. Here is the current disciplinary status.</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <a href="/cases/create" className="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                                <Plus className="w-4.5 h-4.5" />
                                Log Incident
                            </a>
                        </div>
                    </div>
                </div>

                {/* ── STATS GRID ── */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {statCards.map((card) => {
                        const Icon = card.icon;
                        const trend = trends[card.key];
                        return (
                            <a
                                key={card.label}
                                href={card.href}
                                className="group bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-1 hover:border-indigo-200/50 transition-all duration-300 flex flex-col justify-between min-h-[140px]"
                            >
                                <div className="flex items-start justify-between">
                                    <div className="space-y-1">
                                        <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{card.label}</p>
                                        <p className="text-3xl font-extrabold text-slate-800 tracking-tight mt-1.5">{card.value?.toLocaleString()}</p>
                                    </div>
                                    <div className={`p-3 rounded-xl ${card.bg} ${card.color} shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`}>
                                        <Icon className="w-5 h-5" />
                                    </div>
                                </div>
                                
                                {trend && (
                                    <div className="flex items-center gap-1.5 mt-4 pt-3 border-t border-slate-50">
                                        <span className={`inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold ${
                                            trend.direction === 'up' 
                                                ? (trend.isPositive ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100') 
                                                : 'bg-slate-50 text-slate-600 border border-slate-100'
                                        }`}>
                                            {trend.direction === 'up' ? '↑' : trend.direction === 'down' ? '↓' : '•'} {trend.percentage}%
                                        </span>
                                        <span className="text-[10px] text-slate-400 font-medium">vs last month</span>
                                    </div>
                                )}
                            </a>
                        );
                    })}
                </div>

                {/* ── DASHBOARD GRID: 2 EQUAL COLUMNS ── */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-8">
                    
                    {/* 1. Violation Trends */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-indigo-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Violation Trends</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Monthly Case Logging</p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                        <div className="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                                        <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Live</span>
                                    </div>
                                    <div className="p-2.5 rounded-xl bg-indigo-50 text-indigo-600 shadow-sm shadow-indigo-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                        <TrendingUp className="w-5 h-5" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="flex-1 min-h-0 w-full pb-2">
                            <Line data={trendChartData} options={{...chartBaseOptions, maintainAspectRatio: false}} />
                        </div>
                    </div>

                    {/* 2. Cases by Department */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-blue-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Cases by Department</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Cases by College</p>
                                </div>
                                <div className="p-2.5 rounded-xl bg-blue-50 text-blue-600 shadow-sm shadow-blue-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <Layers className="w-5 h-5" />
                                </div>
                            </div>
                        </div>
                        <div className="flex-1 min-h-0 w-full pb-2">
                            <Bar data={deptChartData} options={{...chartBaseOptions, maintainAspectRatio: false}} />
                        </div>
                    </div>

                    {/* 3. Violation Severity */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-rose-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Violation Severity</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Cases by Severity Class</p>
                                </div>
                                <div className="p-2.5 rounded-xl bg-rose-50 text-rose-600 shadow-sm shadow-rose-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <ShieldAlert className="w-5 h-5" />
                                </div>
                            </div>
                        </div>
                        <div className="flex-1 min-h-0 w-full flex items-center justify-center relative pb-2">
                            <Doughnut data={severityChartData} options={{ ...chartBaseOptions, maintainAspectRatio: false, cutout: '75%', plugins: { ...chartBaseOptions.plugins, legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11, family: "'Inter', sans-serif" }, color: '#4b5563', usePointStyle: true, padding: 20 } } } }} />
                            <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-30px]">
                                <p className="text-[10px] uppercase font-bold tracking-widest text-slate-400">Total</p>
                                <p className="text-2xl font-extrabold text-slate-800 tracking-tight mt-0.5">{stats.total_cases}</p>
                            </div>
                        </div>
                    </div>

                    {/* 4. Most Common Offenses */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-amber-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Most Common Offenses</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Most frequent disciplinary infractions</p>
                                </div>
                                <div className="p-2.5 rounded-xl bg-amber-50 text-amber-600 shadow-sm shadow-amber-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <Zap className="w-5 h-5" />
                                </div>
                            </div>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto no-scrollbar pr-2 space-y-4">
                            {topViolations.map((v, idx) => {
                                const colors = [
                                    { bg: 'bg-rose-50 text-rose-700 border-rose-100 bg-gradient-to-r from-rose-500 to-red-500', progress: 'bg-gradient-to-r from-rose-500 to-red-500' },
                                    { bg: 'bg-orange-50 text-orange-700 border-orange-100 bg-gradient-to-r from-orange-500 to-amber-500', progress: 'bg-gradient-to-r from-orange-500 to-amber-500' },
                                    { bg: 'bg-amber-50 text-amber-700 border-amber-100 bg-gradient-to-r from-amber-500 to-yellow-500', progress: 'bg-gradient-to-r from-amber-500 to-yellow-500' },
                                    { bg: 'bg-blue-50 text-blue-700 border-blue-100 bg-gradient-to-r from-blue-500 to-indigo-500', progress: 'bg-gradient-to-r from-blue-500 to-indigo-500' },
                                    { bg: 'bg-slate-50 text-slate-700 border-slate-100 bg-gradient-to-r from-slate-400 to-slate-500', progress: 'bg-gradient-to-r from-slate-400 to-slate-500' }
                                ];
                                const c = colors[idx] || colors[4];
                                const maxCount = topViolations[0]?.count || 1;
                                const percent = Math.round((v.count / maxCount) * 100);

                                return (
                                    <div key={v.title} className="space-y-1.5">
                                        <div className="flex items-center justify-between text-xs font-semibold">
                                            <div className="flex items-center gap-2.5 min-w-0">
                                                <span className={`w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-bold text-white ${c.progress} shadow-sm shadow-indigo-500/5 flex-shrink-0`}>
                                                    {idx + 1}
                                                </span>
                                                <span className="font-bold text-slate-700 truncate">{v.title}</span>
                                            </div>
                                            <span className="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100/50 flex-shrink-0 ml-2">{v.count} cases</span>
                                        </div>
                                        <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                            <div className={`h-full ${c.progress} rounded-full`} style={{ width: `${percent}%` }}></div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* 5. Recent Activity */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-emerald-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Recent Activity</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Latest tracked infractions</p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <a href="/cases" className="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors border border-indigo-100/50">
                                        View All
                                    </a>
                                    <div className="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 shadow-sm shadow-emerald-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                        <Activity className="w-5 h-5" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto no-scrollbar pr-2 divide-y divide-slate-50">
                            {recentCases.map((item, idx) => {
                                const gradients = [
                                    'from-blue-500 to-indigo-600',
                                    'from-teal-400 to-emerald-600',
                                    'from-orange-400 to-rose-600',
                                    'from-indigo-500 to-purple-600',
                                    'from-cyan-400 to-blue-600'
                                ];
                                const g = gradients[idx % gradients.length];
                                return (
                                    <div key={item.id} className="py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors group/item rounded-lg px-2">
                                        <div className="flex items-center gap-4 min-w-0">
                                            <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${g} flex items-center justify-center text-white shadow-md shadow-indigo-500/5 font-extrabold text-sm flex-shrink-0`}>
                                                {(item.student?.full_name || 'U').substring(0, 1)}
                                            </div>
                                            <div className="min-w-0">
                                                <p className="text-sm font-bold text-slate-800 truncate group-hover/item:text-indigo-600 transition-colors">{item.student?.full_name || 'Anonymous'}</p>
                                                <div className="flex items-center gap-2 mt-1">
                                                    <p className="text-xs text-slate-400 truncate font-semibold">{item.violation?.title}</p>
                                                    <span className="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    <p className="text-xs text-slate-400 font-medium whitespace-nowrap">{new Date(item.created_at).toLocaleDateString()}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 ml-2 flex-shrink-0">
                                            <StatusBadge status={item.status} />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* 6. Top Offenders */}
                    <div className="bg-white rounded-2xl border border-slate-100 border-l-4 border-l-purple-500 shadow-sm p-6 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 hover:border-indigo-100 flex flex-col h-[420px] group">
                        <div className="flex-none">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-base font-bold text-slate-800 tracking-tight">Top Offenders</h3>
                                    <p className="text-xs text-slate-400 mt-1 font-medium">Students with persistent infractions</p>
                                </div>
                                <div className="p-2.5 rounded-xl bg-purple-50 text-purple-600 shadow-sm shadow-purple-500/5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <Users2 className="w-5 h-5" />
                                </div>
                            </div>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto no-scrollbar pr-2 space-y-4">
                            {studentsWithViolations.map((student, idx) => {
                                const badgeStyles = [
                                    'bg-rose-50 text-rose-700 border-rose-100',
                                    'bg-orange-50 text-orange-700 border-orange-100',
                                    'bg-amber-50 text-amber-700 border-amber-100',
                                    'bg-blue-50 text-blue-700 border-blue-100',
                                    'bg-slate-50 text-slate-700 border-slate-100'
                                ];
                                const style = badgeStyles[idx] || badgeStyles[4];
                                return (
                                    <div key={student.id} className="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50/50 transition-colors group/item">
                                        <div className="flex items-center gap-3 min-w-0">
                                            <span className={`w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-extrabold border flex-shrink-0 ${style} transition-transform group-hover/item:scale-110 shadow-sm`}>
                                                {idx + 1}
                                            </span>
                                            <div className="min-w-0">
                                                <p className="text-xs font-bold text-slate-700 truncate group-hover/item:text-indigo-600 transition-colors">{student.full_name}</p>
                                                <p className="text-[10px] font-semibold text-slate-400 uppercase mt-0.5 tracking-wider truncate">{student.department}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center ml-2 flex-shrink-0">
                                            <span className="text-xs font-bold text-slate-800 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100 group-hover/item:border-indigo-100 group-hover/item:bg-indigo-50/30 group-hover/item:text-indigo-700 transition-all duration-300">
                                                {student.cases_count} cases
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
