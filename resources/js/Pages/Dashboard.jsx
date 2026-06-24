import { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Users2, FileText, AlertCircle, Gavel, ArrowUpRight, TrendingUp, ShieldAlert, Zap, Layers, Globe, ChevronRight, Plus, Activity, X, FilePlus } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, BarElement,
    ArcElement, PointElement, LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import axios from 'axios';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const statusConfig = {
    'Pending': { bg: 'bg-amber-100 dark:bg-amber-500/10', text: 'text-amber-800 dark:text-amber-400', dot: 'bg-amber-500', border: 'border-amber-200 dark:border-amber-500/20' },
    'Open': { bg: 'bg-rose-100 dark:bg-rose-500/10', text: 'text-rose-800 dark:text-rose-400', dot: 'bg-rose-500', border: 'border-rose-200 dark:border-rose-500/20' },
    'Closed': { bg: 'bg-emerald-100 dark:bg-emerald-500/10', text: 'text-emerald-800 dark:text-emerald-400', dot: 'bg-emerald-500', border: 'border-emerald-200 dark:border-emerald-500/20' },
    'Hearing Scheduled': { bg: 'bg-blue-100 dark:bg-blue-500/10', text: 'text-blue-800 dark:text-blue-400', dot: 'bg-blue-500', border: 'border-blue-200 dark:border-blue-500/20' },
    'Endorsed': { bg: 'bg-purple-100 dark:bg-purple-500/10', text: 'text-purple-800 dark:text-purple-400', dot: 'bg-purple-500', border: 'border-purple-200 dark:border-purple-500/20' },
};

function StatusBadge({ status }) {
    const cfg = statusConfig[status] || { bg: 'bg-slate-100', text: 'text-slate-600 dark:text-slate-400', dot: 'bg-slate-400', border: 'border-slate-200 dark:border-slate-700' };
    return (
        <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase border ${cfg.border} ${cfg.bg} ${cfg.text}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot} shadow-sm`}></span>
            {status}
        </span>
    );
}

export default function Dashboard({ auth, stats, casesPerDept, casesPerSeverity, studentsWithViolations = [], recentCases = [], monthlyTrend = {}, topViolations = [], trends = {}, academicYears = [], selectedAcademicYear, filterAcademicYears = [] }) {
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
            gradient: 'from-blue-500 to-cyan-400',
            iconColor: 'text-blue-600 dark:text-blue-400',
            iconBg: 'bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-100/50',
            href: '/students',
        },
        {
            key: 'total_cases',
            label: 'Violation Cases',
            value: stats.total_cases,
            icon: FileText,
            gradient: 'from-indigo-500 to-violet-500',
            iconColor: 'text-indigo-600 dark:text-indigo-400',
            iconBg: 'bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border-indigo-100/50',
            href: '/cases',
        },
        {
            key: 'open_cases',
            label: 'Active Cases',
            value: stats.open_cases,
            icon: AlertCircle,
            gradient: 'from-rose-500 to-pink-500',
            iconColor: 'text-rose-600 dark:text-rose-400',
            iconBg: 'bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 border-rose-100/50',
            href: '/cases',
        },
        {
            key: 'hearings_this_month',
            label: 'Hearings',
            value: stats.hearings_this_month,
            icon: Gavel,
            gradient: 'from-amber-500 to-orange-400',
            iconColor: 'text-amber-600 dark:text-amber-400',
            iconBg: 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-amber-100/50',
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
            borderColor: '#2563eb', // blue-600
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#2563eb',
            pointBorderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: true,
            backgroundColor: (context) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) return 'rgba(37, 99, 235, 0.1)';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(37, 99, 235, 0.00)');
                gradient.addColorStop(1, 'rgba(37, 99, 235, 0.15)');
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
                if (!chartArea) return '#3b82f6';
                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, '#2563eb'); // blue-600
                gradient.addColorStop(1, '#60a5fa'); // blue-400
                return gradient;
            },
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 8,
            barThickness: 24,
            hoverBackgroundColor: '#1e293b'
        }],
    };

    const severityColors = {
        'Minor': '#38bdf8', // sky-400
        'Major': '#3b82f6', // blue-500
        'Critical': '#f43f5e', // rose-500
    };

    const severityChartData = {
        labels: Object.keys(casesPerSeverity).map(key => `${key} (${casesPerSeverity[key]})`),
        datasets: [{
            data: Object.values(casesPerSeverity),
            backgroundColor: Object.keys(casesPerSeverity).map(key => severityColors[key] || '#94a3b8'),
            borderWidth: 0,
            borderRadius: 5,
            spacing: 5,
            hoverOffset: 8,
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
            <Head title="Premium Dashboard" />

            {/* Sub-header background element */}
            <div className="absolute top-0 left-0 w-full h-[40vh] bg-slate-50 dark:bg-slate-900 -z-10 border-b border-slate-200 dark:border-slate-800"></div>

            <motion.div
                className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >

                {/* ── HEADER SECTION ── */}
                <motion.div variants={itemVariants} className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h1 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">System Dashboard</h1>
                        <p className="text-slate-500 dark:text-slate-400 mt-1 font-medium">Welcome back, <span className="text-slate-800 dark:text-white font-bold">{auth.user.name}</span>. Here is your overview.</p>
                    </div>
                    <div className="flex items-center gap-3 bg-white dark:bg-slate-800 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <span className="text-sm font-bold text-slate-500 dark:text-slate-400 pl-3 uppercase tracking-wider">A.Y.</span>
                        <select
                            className="text-sm border-0 bg-slate-50 dark:bg-slate-900 font-bold text-slate-900 dark:text-white rounded-lg focus:ring-0 py-2 pl-3 pr-8 cursor-pointer"
                            value={selectedAcademicYear}
                            onChange={(e) => window.location.href = route('dashboard', { academic_year: e.target.value })}
                        >
                            <option value="All">All Years</option>
                            {filterAcademicYears.map(year => (
                                <option key={year} value={year}>{year}</option>
                            ))}
                        </select>
                    </div>
                </motion.div>

                {/* ── STATS ROW (SaaS Minimalist Cards) ── */}
                <motion.div variants={itemVariants} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;
                        const trend = trends[card.key];
                        return (
                            <motion.div key={card.label} whileHover={{ y: -2 }} transition={{ duration: 0.2 }}>
                                <Link href={card.href} className="block h-full outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl">
                                    <Card className="relative overflow-hidden rounded-2xl border border-slate-200/60 dark:border-slate-800 bg-gradient-to-b from-white to-slate-50/50 dark:from-slate-900/50 dark:to-slate-900/80 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300 flex flex-col justify-between h-full group">

                                        {/* Subtle top border highlight */}
                                        <div className={`absolute top-0 left-0 w-full h-1 bg-gradient-to-r ${card.gradient} opacity-0 group-hover:opacity-100 transition-all duration-500`}></div>

                                        <CardContent className="p-6 flex flex-col h-full justify-between relative z-10">
                                            <div className="flex items-start justify-between mb-4">
                                                <div className="space-y-1">
                                                    <h3 className="text-[13px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{card.label}</h3>
                                                </div>
                                                <div className={`p-3 rounded-2xl ${card.iconBg} ${card.iconColor} shadow-sm border`}>
                                                    <Icon className="w-5 h-5" strokeWidth={2.5} />
                                                </div>
                                            </div>

                                            <div className="flex items-end justify-between mt-auto">
                                                <span className="text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                                                    {card.value?.toLocaleString()}
                                                </span>
                                                {trend && (
                                                    <div className={`flex items-center px-2 py-1 rounded-full text-xs font-bold shadow-sm border ${trend.direction === 'up'
                                                        ? (trend.isPositive ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20')
                                                        : 'bg-slate-50 text-slate-500 border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
                                                        }`}>
                                                        {trend.direction === 'up' ? <ArrowUpRight className="w-3.5 h-3.5 mr-0.5" /> : trend.direction === 'down' ? <TrendingUp className="w-3.5 h-3.5 mr-0.5 rotate-180" /> : null}
                                                        {trend.percentage}%
                                                    </div>
                                                )}
                                            </div>
                                        </CardContent>
                                        {/* Decorative background blur element */}
                                        <div className={`absolute -bottom-6 -right-6 w-24 h-24 bg-gradient-to-br ${card.gradient} rounded-full blur-3xl opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-500 pointer-events-none`}></div>
                                    </Card>
                                </Link>
                            </motion.div>
                        );
                    })}
                </motion.div>

                <motion.div variants={itemVariants} className="grid grid-cols-1 lg:grid-cols-3 gap-4 pb-12">

                    {/* BENTO ITEM 1: Line Chart (Spans 2 Cols) */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm p-6 flex flex-col h-[400px]">
                            <div className="flex items-center justify-between mb-8">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Violation Trends</h3>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Monthly case logging over time</p>
                                </div>
                            </div>
                            <div className="flex-1 min-h-0 w-full relative">
                                <Line data={trendChartData} options={{ ...chartBaseOptions, maintainAspectRatio: false }} />
                            </div>
                        </Card>
                    </motion.div>

                    {/* BENTO ITEM 2: Recent Activity (Spans 1 Col) */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm flex flex-col h-[400px] overflow-hidden">
                            <CardHeader className="p-6 pb-4 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50 z-10 sticky top-0 space-y-0">
                                <div>
                                    <CardTitle className="text-lg font-black tracking-tight">Activity Feed</CardTitle>
                                    <CardDescription className="text-sm font-medium mt-1">Latest recorded incidents</CardDescription>
                                </div>
                                <Button variant="ghost" size="icon" className="rounded-full bg-slate-50 dark:bg-slate-800 hover:bg-slate-100" asChild>
                                    <Link href="/cases">
                                        <ChevronRight className="w-5 h-5 text-slate-500" />
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent className="flex-1 overflow-y-auto no-scrollbar p-6 pt-2 space-y-4">
                                {(severityFilter ? recentCases.filter(c => c.violation?.severity === severityFilter) : recentCases).map((item, idx) => {
                                    return (
                                        <div key={item.id} className="group relative flex items-start gap-4 p-3 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-2xl transition-all cursor-default">
                                            <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm flex-shrink-0 group-hover:scale-110 transition-transform">
                                                {(item.student?.full_name || 'U').substring(0, 1)}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <p className="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{item.student?.full_name || 'Anonymous'}</p>
                                                <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 truncate">{item.violation?.title}</p>
                                                <div className="flex items-center gap-2 mt-2">
                                                    <StatusBadge status={item.status} />
                                                    <span className="text-[10px] font-semibold text-slate-400 dark:text-slate-500 dark:text-slate-400">{new Date(item.created_at).toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    </motion.div>

                    {/* BENTO ITEM 3: Cases by Department (Spans 2 Cols) */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm p-6 flex flex-col h-[400px]">
                            <div className="flex items-center justify-between mb-8">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Cases by Department</h3>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Distribution across colleges</p>
                                </div>
                                <div className="p-3 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-blue-600 dark:text-blue-400 shadow-sm border border-blue-100/50 dark:border-blue-800/50">
                                    <Layers className="w-5 h-5" />
                                </div>
                            </div>
                            <div className="flex-1 min-h-0 w-full">
                                <Bar data={deptChartData} options={{ ...chartBaseOptions, maintainAspectRatio: false }} />
                            </div>
                        </Card>
                    </motion.div>

                    {/* BENTO ITEM 4: Severity Doughnut (Spans 1 Col) */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm p-6 flex flex-col h-[400px]">
                            <div className="flex items-center justify-between mb-2">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Severity Split</h3>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Cases by severity</p>
                                </div>
                                <div className="p-3 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 text-rose-600 dark:text-rose-400 shadow-sm border border-rose-100/50 dark:border-rose-800/50">
                                    <AlertCircle className="w-5 h-5" />
                                </div>
                            </div>
                            <div className="flex-1 min-h-0 w-full flex items-center justify-center relative mt-4">
                                <Doughnut data={severityChartData} options={{
                                    ...chartBaseOptions,
                                    maintainAspectRatio: false,
                                    cutout: '75%',
                                    scales: { x: { display: false }, y: { display: false } },
                                    onClick: (event, elements) => {
                                        if (elements.length > 0) {
                                            const dataIndex = elements[0].index;
                                            const rawLabel = Object.keys(casesPerSeverity)[dataIndex];
                                            setSeverityFilter(prev => prev === rawLabel ? null : rawLabel);
                                        }
                                    },
                                    plugins: { ...chartBaseOptions.plugins, legend: { display: true, position: 'bottom', labels: { boxWidth: 8, font: { size: 12, weight: '600', family: "'Inter', sans-serif" }, color: '#64748b', usePointStyle: true, padding: 20 } } }
                                }} />
                                <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-30px]">
                                    <p className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">{stats.total_cases}</p>
                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total</p>
                                </div>
                            </div>
                        </Card>
                    </motion.div>

                    {/* BENTO ITEM 5: Top Offenders (Spans 1 Col) */}
                    <motion.div variants={itemVariants} className="h-full">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm flex flex-col h-[400px] overflow-hidden">
                            <CardHeader className="p-6 pb-4 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50 space-y-0">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Top Offenders</h3>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Students with most infractions</p>
                                </div>
                                <div className="p-3 rounded-2xl bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-600 dark:text-violet-400 shadow-sm border border-violet-100/50 dark:border-violet-800/50">
                                    <Users2 className="w-5 h-5" />
                                </div>
                            </CardHeader>
                            <div className="flex-1 overflow-y-auto no-scrollbar p-6 pt-4 space-y-3">
                                {studentsWithViolations.map((student, idx) => {
                                    return (
                                        <div key={student.id} className="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800 transition-colors group">
                                            <div className="w-10 h-10 flex items-center justify-center text-sm font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 shadow-sm transition-all duration-300">
                                                {idx + 1}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <p className="text-sm font-bold text-slate-900 dark:text-white truncate">{student.full_name}</p>
                                                <p className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide truncate">{student.department}</p>
                                            </div>
                                            <div className="text-right flex-shrink-0">
                                                <span className="text-sm font-black text-slate-800 dark:text-white">{student.cases_count}</span>
                                                <p className="text-[10px] font-bold text-slate-400">cases</p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </Card>
                    </motion.div>

                    {/* BENTO ITEM 6: Most Common Offenses (Spans 2 Cols) */}
                    <motion.div variants={itemVariants} className="lg:col-span-2">
                        <Card className="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 shadow-sm p-6 flex flex-col h-[400px]">
                            <div className="flex items-center justify-between mb-8">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white tracking-tight">Common Offenses</h3>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Most frequent violation types</p>
                                </div>
                                <div className="p-3 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 text-amber-600 dark:text-amber-400 shadow-sm border border-amber-100/50 dark:border-amber-800/50">
                                    <Zap className="w-5 h-5" />
                                </div>
                            </div>
                            <div className="flex-1 overflow-y-auto no-scrollbar pr-4 space-y-6">
                                {topViolations.map((v, idx) => {
                                    // Use a monochromatic scale for a cleaner look
                                    const colors = [
                                        { progress: 'bg-indigo-600', bg: 'bg-indigo-100 dark:bg-indigo-500/20' },
                                        { progress: 'bg-indigo-500', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
                                        { progress: 'bg-indigo-400', bg: 'bg-slate-50 dark:bg-slate-800' },
                                        { progress: 'bg-indigo-300', bg: 'bg-slate-50 dark:bg-slate-800' },
                                        { progress: 'bg-indigo-200', bg: 'bg-slate-50 dark:bg-slate-800' }
                                    ];
                                    const c = colors[idx] || colors[4];
                                    const maxCount = topViolations[0]?.count || 1;
                                    const percent = Math.round((v.count / maxCount) * 100);

                                    return (
                                        <div key={v.title} className="space-y-2 group">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-4 min-w-0">
                                                    <span className="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-[11px] font-black text-slate-500 dark:text-slate-400 flex-shrink-0 group-hover:scale-110 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all duration-300">
                                                        {idx + 1}
                                                    </span>
                                                    <span className="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{v.title}</span>
                                                </div>
                                                <span className="text-sm font-black text-slate-900 dark:text-white">{v.count} <span className="text-[10px] text-slate-400 font-semibold uppercase tracking-wider ml-1">Cases</span></span>
                                            </div>
                                            <div className="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden shadow-inner">
                                                <div className="h-full bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full transition-all duration-1000 relative overflow-hidden" style={{ width: `${percent}%` }}>
                                                    <div className="absolute inset-0 bg-white/20 w-full h-full" style={{ animation: 'shimmer 2s infinite linear', transform: 'translateX(-100%)' }}></div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </Card>
                    </motion.div>

                </motion.div>

            </motion.div>
        </AuthenticatedLayout>
    );
}
