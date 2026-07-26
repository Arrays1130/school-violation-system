import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Users2, FileText, AlertCircle, Gavel, ArrowUpRight, TrendingUp, Layers, ChevronRight, LayoutDashboard, Zap, X } from 'lucide-react';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import StatusBadge from '@/Components/StatusBadge';
import DashboardHero, { Panel } from '@/Components/Dashboard/DashboardHero';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, BarElement,
    ArcElement, PointElement, LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import axios from 'axios';
import useInertiaLoading from '@/hooks/useInertiaLoading';
import { BentoSkeleton } from '@/Components/ui/Skeleton';
ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Title, Tooltip, Legend, Filler);

function formatTrendBadge(trend) {
    if (!trend) {
        return null;
    }

    const previous = Number(trend.previous ?? 0);
    const change = Number(trend.change ?? 0);

    // Near-zero baselines make huge % swings look broken — show absolute delta / New instead.
    if (previous < 3) {
        if (previous === 0 && change > 0) {
            return { text: 'New', showArrow: false };
        }
        if (change === 0) {
            return { text: 'Flat', showArrow: false };
        }
        return {
            text: `${change > 0 ? '+' : ''}${change}`,
            showArrow: true,
        };
    }

    return {
        text: `${trend.percentage}%`,
        showArrow: true,
    };
}

const barValueLabelsPlugin = {
    id: 'barValueLabels',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            if (meta.hidden) {
                return;
            }
            meta.data.forEach((bar, index) => {
                const value = dataset.data[index];
                if (value == null || value === 0) {
                    return;
                }
                ctx.save();
                ctx.fillStyle = '#64748b';
                ctx.font = "600 11px 'Inter', sans-serif";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillText(String(value), bar.x, bar.y - 4);
                ctx.restore();
            });
        });
    },
};

export default function Dashboard({ auth, stats, casesPerDept, casesPerSeverity, studentsWithViolations = [], recentCases = [], monthlyTrend = {}, topViolations = [], trends = {}, academicYears = [], selectedAcademicYear, filterAcademicYears = [] }) {
    const isLoading = useInertiaLoading();
    const [severityFilter, setSeverityFilter] = useState(null);
    const [selectedYear, setSelectedYear] = useState('');
    const [graduatedStudents, setGraduatedStudents] = useState([]);
    const [loadingGraduated, setLoadingGraduated] = useState(false);

    useEffect(() => {
        if (selectedYear) {
            setLoadingGraduated(true);
            axios.get(route('api.graduated-students'), { params: { academic_year: selectedYear } })
                .then(res => {
                    setGraduatedStudents(res.data);
                })
                .catch(err => console.error(err))
                .finally(() => setLoadingGraduated(false));
        } else {
            setGraduatedStudents([]);
        }
    }, [selectedYear]);

    const handlePrint = () => {
        if (graduatedStudents.length === 0) return;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Graduated Students - ${selectedYear}</title>
                    <style>
                        body { font-family: sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                        th { background-color: #f4f4f4; }
                        h2 { text-align: center; }
                    </style>
                </head>
                <body>
                    <h2>Graduated Students (${selectedYear})</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Course/Program</th>
                                <th>Section</th>
                                <th>Year Level</th>
                                <th>Date Graduated</th>
                                <th>Academic Year Graduated</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${graduatedStudents.map(student => `
                                <tr>
                                    <td>${student.id}</td>
                                    <td>${student.full_name}</td>
                                    <td>${student.department}</td>
                                    <td>${student.section}</td>
                                    <td>${student.year_level}</td>
                                    <td>${new Date(student.deleted_at).toLocaleDateString()}</td>
                                    <td>${student.academic_year_graduated}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    <script>
                        window.print();
                        setTimeout(() => window.close(), 500);
                    </script>
                </body>
            </html>
        `);
        printWindow.document.close();
    };

    const statCards = [
        {
            key: 'total_students',
            label: 'Total Students',
            value: stats.total_students,
            icon: Users2,
            iconColor: 'text-blue-700 dark:text-blue-300',
            iconBg: 'bg-white/80 dark:bg-blue-500/15 border-blue-200/70 dark:border-blue-500/25 shadow-sm',
            surface: 'from-blue-50/90 via-white to-white dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-900',
            border: 'border-blue-100 dark:border-blue-900/40 hover:border-blue-300 dark:hover:border-blue-700',
            glow: 'bg-blue-400/20',
            accent: 'bg-blue-600',
            href: '/students',
        },
        {
            key: 'total_cases',
            label: 'Violation Cases',
            value: stats.total_cases,
            icon: FileText,
            iconColor: 'text-slate-700 dark:text-slate-200',
            iconBg: 'bg-white/80 dark:bg-slate-800 border-slate-200 dark:border-slate-700 shadow-sm',
            surface: 'from-slate-50 via-white to-white dark:from-slate-800/50 dark:via-slate-900 dark:to-slate-900',
            border: 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-600',
            glow: 'bg-slate-400/15',
            accent: 'bg-slate-700 dark:bg-slate-400',
            href: '/cases',
        },
        {
            key: 'open_cases',
            label: 'Active Cases',
            value: stats.open_cases,
            icon: AlertCircle,
            iconColor: 'text-rose-700 dark:text-rose-300',
            iconBg: 'bg-white/80 dark:bg-rose-500/15 border-rose-200/70 dark:border-rose-500/25 shadow-sm',
            surface: 'from-rose-50/90 via-white to-white dark:from-rose-950/35 dark:via-slate-900 dark:to-slate-900',
            border: 'border-rose-100 dark:border-rose-900/40 hover:border-rose-300 dark:hover:border-rose-700',
            glow: 'bg-rose-400/20',
            accent: 'bg-rose-500',
            href: '/cases',
        },
        {
            key: 'hearings_this_month',
            label: 'Hearings',
            value: stats.hearings_this_month,
            icon: Gavel,
            iconColor: 'text-amber-700 dark:text-amber-300',
            iconBg: 'bg-white/80 dark:bg-amber-500/15 border-amber-200/70 dark:border-amber-500/25 shadow-sm',
            surface: 'from-amber-50/90 via-white to-white dark:from-amber-950/30 dark:via-slate-900 dark:to-slate-900',
            border: 'border-amber-100 dark:border-amber-900/40 hover:border-amber-300 dark:hover:border-amber-700',
            glow: 'bg-amber-400/20',
            accent: 'bg-amber-500',
            href: '/reports',
        },
    ];

    const chartBaseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: { top: 15, right: 15, left: 5, bottom: 5 }
        },
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
                    label: function (context) {
                        const value = context.parsed?.y ?? context.parsed ?? context.raw;
                        const label = context.dataset.label || context.label || '';
                        const count = typeof value === 'number' ? value : 0;
                        const prefix = label ? `${label}: ` : '';
                        return `${prefix}${count}${count === 1 ? ' case' : ' cases'}`;
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
                grid: { color: 'rgba(148, 163, 184, 0.2)', drawBorder: false, borderDash: [5, 5] },
                ticks: { stepSize: 1, font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, color: '#94a3b8' },
                beginAtZero: true,
                border: { display: false },
                grace: 1
            },
        },
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
            borderColor: '#1d4ed8', // blue-700 brand
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#1d4ed8',
            pointBorderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: true,
            backgroundColor: (context) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) return 'rgba(29, 78, 216, 0.1)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(29, 78, 216, 0.00)');
                gradient.addColorStop(1, 'rgba(29, 78, 216, 0.12)');
                return gradient;
            },
        }],
    };

    const deptChartData = {
        labels: Object.keys(casesPerDept),
        datasets: [{
            label: 'Cases',
            data: Object.values(casesPerDept),
            backgroundColor: (context) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) return '#1d4ed8';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, '#1e3a8a'); // blue-900
                gradient.addColorStop(1, '#3b82f6'); // blue-500
                return gradient;
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            barThickness: 32,
            hoverBackgroundColor: '#0f172a'
        }],
    };

    const severityColors = {
        'Minor': '#1d4ed8', // blue-700
        'Major': '#FC2847',
    };

    const severityEntries = Object.entries(casesPerSeverity || {});
    const severityTotal = severityEntries.reduce((sum, [, count]) => sum + Number(count || 0), 0) || stats.total_cases || 0;

    const severityChartData = {
        labels: severityEntries.map(([key]) => key),
        datasets: [{
            data: severityEntries.map(([, count]) => count),
            backgroundColor: severityEntries.map(([key]) => severityColors[key] || '#94a3b8'),
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverBorderWidth: 3,
            hoverOffset: 4,
        }],
    };

    const containerVariants = {
        hidden: { opacity: 0 },
        show: {
            opacity: 1,
            transition: {
                staggerChildren: 0.1
            }
        }
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 20 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            {isLoading ? (
                <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <BentoSkeleton />
                </div>
            ) : (
            <motion.div
                className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >

                {/* ── HERO ── */}
                <motion.div variants={itemVariants}>
                    <DashboardHero
                        badge="Overview"
                        badgeIcon={LayoutDashboard}
                        title="System Dashboard"
                        description={`Welcome back, ${auth.user.name}. Live overview of students, cases, and hearings.`}
                    >
                        <div className="flex items-center gap-3 bg-white/10 p-1.5 rounded-xl border border-white/15 backdrop-blur-md">
                            <span className="text-xs font-bold text-white/70 pl-3 uppercase tracking-wider">A.Y.</span>
                            <select
                                className="text-sm border-0 bg-white/10 font-bold text-white rounded-lg focus:ring-0 py-2 pl-3 pr-8 cursor-pointer"
                                value={selectedAcademicYear}
                                onChange={(e) => window.location.href = route('dashboard', { academic_year: e.target.value })}
                            >
                                <option value="All" className="text-slate-900">All Years</option>
                                {filterAcademicYears.map(year => (
                                    <option key={year} value={year} className="text-slate-900">{year}</option>
                                ))}
                            </select>
                        </div>
                    </DashboardHero>
                </motion.div>

                {/* ── STATS ROW ── */}
                <motion.div variants={itemVariants} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;
                        const trend = trends[card.key];
                        return (
                            <motion.div key={card.label} whileHover={{ y: -3 }} transition={{ duration: 0.2 }}>
                                <Link href={card.href} className="block h-full outline-none focus-visible:ring-2 focus-visible:ring-blue-600 rounded-2xl">
                                    <Card className={`relative overflow-hidden rounded-2xl border bg-gradient-to-br ${card.surface} ${card.border} shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between h-full group min-h-[148px]`}>
                                        <div className={`absolute -right-6 -top-6 w-24 h-24 rounded-full blur-2xl ${card.glow} pointer-events-none group-hover:scale-125 transition-transform duration-500`} />

                                        <CardContent className="relative z-10 p-6 flex flex-col h-full justify-between">
                                            <div className="flex items-start justify-between mb-5">
                                                <div>
                                                    <h3 className="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{card.label}</h3>
                                                    <div className={`mt-2 h-0.5 w-8 rounded-full ${card.accent} opacity-70 group-hover:w-12 transition-all duration-300`} />
                                                </div>
                                                <div className={`p-3 rounded-2xl ${card.iconBg} ${card.iconColor} border group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`}>
                                                    <Icon className="w-5 h-5" strokeWidth={2.25} />
                                                </div>
                                            </div>

                                            <div className="flex items-end justify-between mt-auto gap-3">
                                                <span className="text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                                                    {card.value?.toLocaleString()}
                                                </span>
                                                {(() => {
                                                    const badge = formatTrendBadge(trend);
                                                    if (!badge) {
                                                        return null;
                                                    }
                                                    return (
                                                        <div className={`flex items-center px-2 py-1 rounded-lg text-xs font-bold border backdrop-blur-sm shrink-0 ${trend.direction === 'up'
                                                            ? (trend.isPositive ? 'bg-emerald-50/90 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50/90 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20')
                                                            : trend.direction === 'down'
                                                                ? (trend.isPositive ? 'bg-emerald-50/90 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50/90 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20')
                                                                : 'bg-white/80 text-slate-500 border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
                                                            }`}>
                                                            {badge.showArrow && trend.direction === 'up' ? <ArrowUpRight className="w-3.5 h-3.5 mr-0.5" /> : null}
                                                            {badge.showArrow && trend.direction === 'down' ? <TrendingUp className="w-3.5 h-3.5 mr-0.5 rotate-180" /> : null}
                                                            {badge.text}
                                                        </div>
                                                    );
                                                })()}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>
                            </motion.div>
                        );
                    })}
                </motion.div>

                <motion.div variants={itemVariants} className="grid grid-cols-1 lg:grid-cols-3 gap-4 pb-12">

                    {/* Line Chart */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Panel title="Violation Trends" subtitle="Monthly case logging over time" className="h-[400px]" contentClassName="p-6 pt-4 flex flex-col min-h-0">
                            <div className="flex-1 min-h-0 w-full relative">
                                <Line data={trendChartData} options={{ ...chartBaseOptions, maintainAspectRatio: false }} />
                            </div>
                        </Panel>
                    </motion.div>

                    {/* Activity Feed */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Panel
                            title="Activity Feed"
                            subtitle="Latest recorded incidents"
                            className="h-[400px]"
                            contentClassName="p-4 pt-2 flex flex-col min-h-0 overflow-hidden"
                            action={
                                <div className="flex items-center gap-1.5">
                                    {severityFilter && (
                                        <button
                                            type="button"
                                            onClick={() => setSeverityFilter(null)}
                                            className="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-blue-700 dark:border-blue-700 dark:bg-blue-500/10 dark:text-blue-300"
                                        >
                                            {severityFilter}
                                            <X className="w-3 h-3" />
                                        </button>
                                    )}
                                    <Button variant="ghost" size="icon" className="rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700" asChild>
                                        <Link href="/cases">
                                            <ChevronRight className="w-5 h-5 text-slate-500" />
                                        </Link>
                                    </Button>
                                </div>
                            }
                        >
                            <div className="flex-1 overflow-y-auto no-scrollbar space-y-1.5 pr-1">
                                {(() => {
                                    const feedItems = severityFilter
                                        ? recentCases.filter((c) => c.violation?.severity === severityFilter)
                                        : recentCases;

                                    if (feedItems.length === 0) {
                                        return (
                                            <div className="flex h-full min-h-[200px] flex-col items-center justify-center px-4 text-center">
                                                <p className="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                    {severityFilter ? `No ${severityFilter.toLowerCase()} incidents` : 'No recent incidents'}
                                                </p>
                                                <p className="mt-1 text-xs text-slate-400">
                                                    {severityFilter ? 'Clear the filter to see all activity.' : 'New cases will show up here.'}
                                                </p>
                                                {severityFilter && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setSeverityFilter(null)}
                                                        className="mt-3 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                                    >
                                                        Clear filter
                                                    </button>
                                                )}
                                            </div>
                                        );
                                    }

                                    return feedItems.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={route('cases.show', item.id)}
                                            className="group relative flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800/80 rounded-xl transition-all"
                                        >
                                            <div className="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm flex-shrink-0 border border-slate-200/80 dark:border-slate-700">
                                                {(item.student?.full_name || 'U').substring(0, 1)}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <p className="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{item.student?.full_name || 'Anonymous'}</p>
                                                <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 truncate">{item.violation?.title}</p>
                                                <div className="flex items-center gap-2 mt-2">
                                                    <StatusBadge status={item.status} />
                                                    <span className="text-[10px] font-semibold text-slate-400 dark:text-slate-500">{new Date(item.created_at).toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                        </Link>
                                    ));
                                })()}
                            </div>
                        </Panel>
                    </motion.div>

                    {/* Cases by Department */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Panel
                            title="Cases by Department"
                            subtitle="Distribution across colleges"
                            icon={Layers}
                            className="h-[400px]"
                            contentClassName="p-6 pt-4 flex flex-col min-h-0"
                        >
                            <div className="flex-1 min-h-0 w-full">
                                <Bar
                                    data={deptChartData}
                                    plugins={[barValueLabelsPlugin]}
                                    options={{
                                        ...chartBaseOptions,
                                        maintainAspectRatio: false,
                                        layout: {
                                            padding: { top: 24, right: 15, left: 5, bottom: 5 },
                                        },
                                    }}
                                />
                            </div>
                        </Panel>
                    </motion.div>

                    {/* Severity Doughnut */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Panel
                            title="Severity Split"
                            subtitle="Minor vs Major cases"
                            icon={AlertCircle}
                            iconClassName="bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300"
                            className="h-[400px]"
                            contentClassName="p-6 pt-4 flex flex-col min-h-0"
                        >
                            <div className="flex-1 min-h-0 w-full relative">
                                <Doughnut data={severityChartData} options={{
                                    ...chartBaseOptions,
                                    maintainAspectRatio: false,
                                    cutout: '68%',
                                    scales: { x: { display: false }, y: { display: false } },
                                    onClick: (event, elements) => {
                                        if (elements.length > 0) {
                                            const dataIndex = elements[0].index;
                                            const rawLabel = severityEntries[dataIndex]?.[0];
                                            if (rawLabel) {
                                                setSeverityFilter(prev => prev === rawLabel ? null : rawLabel);
                                            }
                                        }
                                    },
                                    plugins: {
                                        ...chartBaseOptions.plugins,
                                        legend: { display: false },
                                    },
                                }} />
                                <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">{severityTotal}</p>
                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Total</p>
                                </div>
                            </div>

                            <div className="mt-4 grid grid-cols-2 gap-2 shrink-0">
                                {severityEntries.map(([key, count]) => {
                                    const pct = severityTotal ? Math.round((Number(count) / severityTotal) * 100) : 0;
                                    const active = severityFilter === key;
                                    return (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() => setSeverityFilter(prev => prev === key ? null : key)}
                                            className={`flex items-center gap-2.5 rounded-xl border px-3 py-2.5 text-left transition-all ${
                                                active
                                                    ? 'border-blue-300 bg-blue-50 dark:border-blue-700 dark:bg-blue-500/10'
                                                    : 'border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50 hover:border-slate-200 dark:hover:border-slate-700'
                                            }`}
                                        >
                                            <span
                                                className="w-2.5 h-2.5 rounded-full shrink-0"
                                                style={{ backgroundColor: severityColors[key] || '#94a3b8' }}
                                            />
                                            <span className="min-w-0 flex-1">
                                                <span className="block text-xs font-bold text-slate-800 dark:text-slate-200">{key}</span>
                                                <span className="block text-[10px] font-semibold text-slate-400">{count} · {pct}%</span>
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </Panel>
                    </motion.div>

                    {/* Top Offenders */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Panel
                            title="Top Offenders"
                            subtitle="Students with most infractions"
                            icon={Users2}
                            iconClassName="bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300"
                            className="h-[400px]"
                            contentClassName="p-4 pt-2 flex flex-col min-h-0 overflow-hidden"
                        >
                            <div className="flex-1 overflow-y-auto no-scrollbar space-y-1.5 pr-1">
                                {studentsWithViolations.length === 0 ? (
                                    <div className="flex h-full min-h-[200px] items-center justify-center text-sm text-slate-400">
                                        No repeat offenders yet
                                    </div>
                                ) : (
                                    studentsWithViolations.map((student, idx) => (
                                        <Link
                                            key={student.id}
                                            href={route('students.show', student.id)}
                                            className="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group"
                                        >
                                            <div className="w-10 h-10 flex items-center justify-center text-sm font-black text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all duration-300 shrink-0">
                                                {idx + 1}
                                            </div>
                                            <div className="min-w-0 flex-1 pr-2">
                                                <p className="text-sm font-bold text-slate-900 dark:text-white truncate" title={student.full_name}>
                                                    {student.full_name}
                                                </p>
                                                <p
                                                    className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide line-clamp-2 leading-snug mt-0.5"
                                                    title={student.department}
                                                >
                                                    {student.department}
                                                </p>
                                            </div>
                                            <div className="text-right shrink-0 w-12">
                                                <span className="text-sm font-black text-slate-800 dark:text-white">{student.cases_count}</span>
                                                <p className="text-[10px] font-bold text-slate-400">cases</p>
                                            </div>
                                        </Link>
                                    ))
                                )}
                            </div>
                        </Panel>
                    </motion.div>

                    {/* Common Offenses */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Panel
                            title="Common Offenses"
                            subtitle="Most frequent violation types"
                            icon={Zap}
                            iconClassName="bg-amber-50 dark:bg-amber-500/10 border-amber-100 dark:border-amber-500/20 text-amber-700 dark:text-amber-300"
                            className="h-[400px]"
                            contentClassName="p-6 pt-4 flex flex-col min-h-0 overflow-hidden"
                        >
                            <div className="flex-1 overflow-y-auto no-scrollbar pr-2 space-y-5">
                                {topViolations.length === 0 ? (
                                    <div className="flex h-full min-h-[200px] items-center justify-center text-sm text-slate-400">
                                        No offense data yet
                                    </div>
                                ) : (
                                    topViolations.map((v, idx) => {
                                        const maxCount = Math.max(...topViolations.map((item) => Number(item.count) || 0), 1);
                                        const rawPercent = (Number(v.count) / maxCount) * 100;
                                        const percent = Number(v.count) > 0 ? Math.max(rawPercent, 8) : 0;

                                        return (
                                            <div key={v.title} className="space-y-2 group">
                                                <div className="flex items-center justify-between gap-3">
                                                    <div className="flex items-center gap-3 min-w-0">
                                                        <span className="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[11px] font-black text-slate-500 dark:text-slate-400 flex-shrink-0 border border-slate-200/80 dark:border-slate-700">
                                                            {idx + 1}
                                                        </span>
                                                        <span className="text-sm font-bold text-slate-800 dark:text-slate-200 truncate" title={v.title}>{v.title}</span>
                                                    </div>
                                                    <span className="text-sm font-black text-slate-900 dark:text-white shrink-0">
                                                        {v.count}{' '}
                                                        <span className="text-[10px] text-slate-400 font-semibold lowercase tracking-wider">
                                                            {Number(v.count) === 1 ? 'case' : 'cases'}
                                                        </span>
                                                    </span>
                                                </div>
                                                <div className="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                                                    <div
                                                        className="h-full bg-blue-600 rounded-full transition-all duration-700"
                                                        style={{ width: `${percent}%` }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                            </div>
                        </Panel>
                    </motion.div>

                </motion.div>

            </motion.div>
            )}
        </AuthenticatedLayout>
    );
}
