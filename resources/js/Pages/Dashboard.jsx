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

export default function Dashboard({ auth, stats, casesPerDept, casesPerSeverity, studentsWithViolations, recentCases, monthlyTrend = {}, topViolations = [] }) {

    const statCards = [
        {
            label: 'Total Students',
            value: stats.total_students,
            icon: Users2,
            color: 'text-blue-600',
            bg: 'bg-blue-50',
            href: '/students',
        },
        {
            label: 'Disciplinary Cases',
            value: stats.total_cases,
            icon: FileText,
            color: 'text-indigo-600',
            bg: 'bg-indigo-50',
            href: '/cases',
        },
        {
            label: 'Active Cases',
            value: stats.open_cases,
            icon: ShieldAlert,
            color: 'text-red-600',
            bg: 'bg-red-50',
            href: '/cases',
        },
        {
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
                backgroundColor: '#ffffff',
                titleColor: '#111827',
                bodyColor: '#4b5563',
                titleFont: { size: 12, weight: '600', family: "'Inter', sans-serif" },
                bodyFont: { size: 12, family: "'Inter', sans-serif" },
                padding: 12,
                borderColor: '#e5e7eb',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
            }
        },
        scales: {
            x: { 
                grid: { display: false }, 
                ticks: { font: { size: 11, family: "'Inter', sans-serif" }, color: '#6b7280' } 
            },
            y: { 
                grid: { color: '#f3f4f6', drawBorder: false }, 
                ticks: { stepSize: 1, font: { size: 11, family: "'Inter', sans-serif" }, color: '#6b7280' },
                beginAtZero: true 
            },
        },
    };

    const deptChartData = {
        labels: Object.keys(casesPerDept),
        datasets: [{
            data: Object.values(casesPerDept),
            backgroundColor: '#3b82f6',
            borderRadius: 4,
            barThickness: 32,
            hoverBackgroundColor: '#2563eb',
        }],
    };

    const severityChartData = {
        labels: Object.keys(casesPerSeverity),
        datasets: [{
            data: Object.values(casesPerSeverity),
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
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
            data: Object.values(monthlyTrend || {}),
            borderColor: '#3b82f6',
            borderWidth: 2,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#3b82f6',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.1,
            fill: true,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
        }],
    };

    return (
        <AuthenticatedLayout user={auth.user} header="Disciplinary Dashboard">
            <Head title="Dashboard" />

            <div className="max-w-7xl mx-auto space-y-6">
                
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
                        return (
                            <a
                                key={card.label}
                                href={card.href}
                                className="group bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-all"
                            >
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">{card.label}</p>
                                        <div className="flex items-center gap-2 mt-2">
                                            <p className="text-3xl font-semibold text-gray-900">{card.value?.toLocaleString()}</p>
                                        </div>
                                    </div>
                                    <div className={`p-3 rounded-md ${card.bg} ${card.color}`}>
                                        <Icon className="w-6 h-6" />
                                    </div>
                                </div>
                            </a>
                        );
                    })}
                </div>

                {/* ── CHARTS ROW ── */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <div className="flex items-center justify-between mb-6">
                            <div>
                                <h3 className="text-base font-semibold text-gray-900">Violation Trends</h3>
                                <p className="text-sm text-gray-500 mt-1">Monthly Case Logging</p>
                            </div>
                            <div className="flex items-center gap-2 px-3 py-1 bg-gray-50 rounded-md border border-gray-200">
                                <div className="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span className="text-xs font-medium text-gray-600">Active Cases</span>
                            </div>
                        </div>
                        <div className="h-64 w-full">
                            <Line data={trendChartData} options={chartBaseOptions} />
                        </div>
                    </div>

                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <h3 className="text-base font-semibold text-gray-900">Severity Level</h3>
                        <p className="text-sm text-gray-500 mt-1 mb-6">Cases by Severity Class</p>
                        <div className="h-64 w-full flex items-center justify-center relative">
                            <Doughnut data={severityChartData} options={{ ...chartBaseOptions, cutout: '75%', plugins: { ...chartBaseOptions.plugins, legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 12, family: "'Inter', sans-serif" }, color: '#4b5563', usePointStyle: true, padding: 20 } } } }} />
                            <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-30px]">
                                <p className="text-xs font-medium text-gray-500">Total</p>
                                <p className="text-2xl font-semibold text-gray-900">{stats.total_cases}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── BOTTOM SECTIONS ── */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-8">
                    {/* Activity Feed */}
                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div className="p-5 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                            <div>
                                <h3 className="text-base font-semibold text-gray-900">Recent Incidents</h3>
                            </div>
                            <a href="/cases" className="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                View All
                            </a>
                        </div>
                        <div className="divide-y divide-gray-200">
                            {recentCases.map(item => (
                                <div key={item.id} className="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors group">
                                    <div className="flex items-center gap-4 min-w-0">
                                        <div className="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm">
                                            {(item.student?.full_name || 'U').substring(0, 1)}
                                        </div>
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-gray-900 truncate group-hover:text-blue-600 transition-colors">{item.student?.full_name || 'Anonymous'}</p>
                                            <div className="flex items-center gap-2 mt-1">
                                                <p className="text-xs text-gray-500 truncate">{item.violation?.title}</p>
                                                <span className="w-1 h-1 rounded-full bg-gray-300"></span>
                                                <p className="text-xs text-gray-500 whitespace-nowrap">{new Date(item.created_at).toLocaleDateString()}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <StatusBadge status={item.status} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Department Distribution */}
                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6 flex flex-col">
                        <div className="mb-6">
                            <h3 className="text-base font-semibold text-gray-900">Department Distribution</h3>
                            <p className="text-sm text-gray-500 mt-1">Cases by College / Department</p>
                        </div>
                        <div className="flex-1 h-64">
                            <Bar data={deptChartData} options={chartBaseOptions} />
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
