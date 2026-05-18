import React, { useEffect, useState, useMemo } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { 
    Bell, 
    Calendar, 
    Clock, 
    MapPin, 
    Users as UsersIcon, 
    CheckCircle, 
    AlertTriangle,
    RefreshCw,
    Eye,
    X,
    User,
    FileText,
    TrendingUp,
    TrendingDown,
    Activity,
    Search,
    BarChart3,
    ShieldAlert
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
} from 'chart.js';
import { Bar, Line, Pie } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
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
    const [showNotifications, setShowNotifications] = useState(false);
    const [selectedCase, setSelectedCase] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");

    const trendChartData = {
        labels: Object.keys(chartData.monthlyTrend),
        datasets: [{
            label: 'Incidents',
            data: Object.values(chartData.monthlyTrend),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.1,
        }]
    };

    const commonViolationsData = {
        labels: chartData.topViolations.map(v => v.title.substring(0, 15) + "..."),
        datasets: [{
            label: 'Cases',
            data: chartData.topViolations.map(v => v.count),
            backgroundColor: '#3b82f6',
        }]
    };

    const severityData = {
        labels: Object.keys(chartData.severityBreakdown),
        datasets: [{
            data: Object.values(chartData.severityBreakdown),
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        }]
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{department} Dashboard</h2>}>
            <Head title={`Dean Dashboard - ${department}`} />

            <div className="space-y-6 pb-12">

                {/* ── MODERN PRISM HEADER ── */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <Activity className="w-3.5 h-3.5" />
                                {department} Access
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">
                                {department} Overview
                            </h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">
                                Monitor department cases, scheduled hearings, and student disciplinary incidents.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-500">Total Cases</p>
                                <p className="text-3xl font-semibold text-gray-900 mt-2">{stats.total}</p>
                                <div className="mt-2 flex items-center gap-1.5">
                                    <span className={`text-xs font-medium ${trends.total.isPositive ? 'text-green-600' : 'text-red-600'}`}>
                                        {trends.total.direction === 'up' ? '↑' : '↓'} {trends.total.percentage}%
                                    </span>
                                    <span className="text-xs text-gray-500">vs last month</span>
                                </div>
                            </div>
                            <div className="p-3 bg-blue-50 rounded-md">
                                <Activity className="w-6 h-6 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-500">Pending Review</p>
                                <p className="text-3xl font-semibold text-gray-900 mt-2">{stats.pending}</p>
                                <div className="mt-2 flex items-center gap-1.5">
                                    <span className="text-xs text-amber-600 font-medium">Action Needed</span>
                                </div>
                            </div>
                            <div className="p-3 bg-amber-50 rounded-md">
                                <Clock className="w-6 h-6 text-amber-600" />
                            </div>
                        </div>
                    </div>

                    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-500">Resolution Rate</p>
                                <p className="text-3xl font-semibold text-gray-900 mt-2">
                                    {stats.total > 0 ? Math.round((stats.closed / stats.total) * 100) : 0}%
                                </p>
                                <div className="mt-2 flex items-center gap-1.5">
                                    <span className="text-xs text-green-600 font-medium">System Healthy</span>
                                </div>
                            </div>
                            <div className="p-3 bg-green-50 rounded-md">
                                <CheckCircle className="w-6 h-6 text-green-600" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Charts Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
                        <div className="mb-4">
                            <h3 className="text-base font-semibold text-gray-900">Monthly Violation Trends</h3>
                            <p className="text-xs text-gray-500 mt-1">6-Month Case Volume</p>
                        </div>
                        <div className="h-64 relative w-full">
                            <Line data={trendChartData} options={{ 
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { 
                                    y: { border: { display: false }, grid: { color: '#f3f4f6' } },
                                    x: { border: { display: false }, grid: { display: false } }
                                }
                            }} />
                        </div>
                    </div>
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
                        <div className="mb-4">
                            <h3 className="text-base font-semibold text-gray-900">Cases by Severity</h3>
                            <p className="text-xs text-gray-500 mt-1">Severity Level Distribution</p>
                        </div>
                        <div className="h-64 relative w-full flex justify-center">
                            <Pie data={severityData} options={{ 
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 12 } } } 
                                }
                            }} />
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent Cases Table */}
                    <div className="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <div>
                                <h3 className="text-base font-semibold text-gray-900">Recent Violations</h3>
                                <p className="text-xs text-gray-500 mt-1">Latest logged disciplinary actions</p>
                            </div>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <input 
                                    type="text" 
                                    placeholder="Search..." 
                                    className="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-48 transition-all"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                />
                            </div>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-white text-gray-500 text-xs font-medium tracking-wider uppercase text-left">
                                    <tr>
                                        <th className="px-6 py-3">Student</th>
                                        <th className="px-6 py-3">Violation</th>
                                        <th className="px-6 py-3">Status</th>
                                        <th className="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {recentCases.map(c => (
                                        <tr key={c.id} onClick={() => { setSelectedCase(c); setIsModalOpen(true); }} className="hover:bg-gray-50 transition-colors cursor-pointer">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-semibold text-gray-600 text-xs">
                                                        {c.student?.full_name?.charAt(0)}
                                                    </div>
                                                    <div>
                                                        <p className="font-medium text-gray-900 text-sm">{c.student?.full_name}</p>
                                                        <p className="text-xs text-gray-500 mt-0.5">{c.student?.year_level}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <p className="text-sm font-medium text-gray-900">{c.violation?.title}</p>
                                                <p className="text-xs text-gray-500 mt-0.5">{new Date(c.created_at).toLocaleDateString()}</p>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${c.status === 'Closed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}`}>
                                                    {c.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">
                                                <button className="text-gray-400 hover:text-blue-600 transition-colors">
                                                    <Eye className="w-5 h-5 ml-auto" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Sidebar: Hearings & Repeaters */}
                    <div className="space-y-6">
                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div className="px-5 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 className="text-base font-semibold text-gray-900">Upcoming Hearings</h3>
                            </div>
                            <div className="p-5 space-y-4">
                                {upcomingHearings.length > 0 ? upcomingHearings.map(h => (
                                    <div key={h.id} className="flex items-start gap-3 p-3 rounded-md hover:bg-gray-50 transition-colors border border-transparent cursor-pointer">
                                        <div className="p-2 bg-blue-50 rounded text-blue-600">
                                            <Calendar className="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">{h.case.student.full_name}</p>
                                            <p className="text-xs text-gray-500 mt-1">{new Date(h.scheduled_at).toLocaleDateString()}</p>
                                            <p className="text-xs text-blue-600 mt-1 font-medium">{h.venue}</p>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="text-center py-6">
                                        <Calendar className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                                        <p className="text-gray-500 text-sm">No scheduled hearings</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div className="px-5 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 className="text-base font-semibold text-gray-900">Frequent Offenders</h3>
                            </div>
                            <div className="p-5 space-y-3">
                                {topRepeaters.map((s, idx) => (
                                    <div key={s.id} className="flex items-center justify-between p-3 rounded-md hover:bg-gray-50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <span className={`w-6 h-6 rounded flex items-center justify-center text-xs font-semibold ${idx === 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'}`}>
                                                {idx + 1}
                                            </span>
                                            <span className="text-sm font-medium text-gray-900">{s.full_name}</span>
                                        </div>
                                        <span className="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {s.cases_count} Cases
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
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
                    <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <h3 className="text-lg font-semibold text-gray-900">Case Details</h3>
                            <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-500 transition-colors">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <div className="p-6 overflow-y-auto">
                            <div className="flex items-center gap-4 mb-8">
                                <div className="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-2xl font-semibold">
                                    {selectedCase.student?.full_name?.charAt(0)}
                                </div>
                                <div>
                                    <h4 className="text-xl font-semibold text-gray-900">{selectedCase.student?.full_name}</h4>
                                    <p className="text-sm text-gray-500 mt-1">{selectedCase.student?.id_number} • {selectedCase.student?.department}</p>
                                    <div className="mt-2">
                                        <span className="px-2.5 py-0.5 bg-gray-100 rounded-md text-xs font-medium text-gray-600">{selectedCase.student?.year_level}</span>
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
                                    <div className="py-6 border-y border-gray-200 mb-6">
                                        <p className="text-xs font-medium text-gray-500 uppercase tracking-wider mb-6 text-center">Case Progress</p>
                                        <div className="relative max-w-md mx-auto">
                                            {/* Background Line */}
                                            <div className="absolute top-4 left-[12%] right-[12%] h-0.5 bg-gray-200 z-0"></div>
                                            {/* Active Line Fill */}
                                            <div className="absolute top-4 left-[12%] h-0.5 bg-blue-600 z-0 transition-all duration-500"
                                                 style={{ width: `${((currentStep - 1) / 3) * 76}%` }}></div>
                                            
                                            <div className="grid grid-cols-4 gap-2 relative z-10">
                                                {steps.map((step, idx) => {
                                                    const Icon = step.icon;
                                                    const isCompleted = idx < currentStep;
                                                    const isCurrent = idx + 1 === currentStep;
                                                    const bg = isCompleted ? 'bg-green-600 text-white' : 
                                                               isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-gray-100 text-gray-400';
                                                    return (
                                                        <div key={step.title} className="flex flex-col items-center">
                                                            <div className={`w-8 h-8 rounded-full flex items-center justify-center transition-colors ${bg}`}>
                                                                <Icon className="w-4 h-4" />
                                                            </div>
                                                            <p className={`text-xs mt-3 font-medium ${isCompleted || isCurrent ? 'text-gray-900' : 'text-gray-400'}`}>
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
                                <div className="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <p className="text-xs font-medium text-gray-500 mb-1">Policy Violation</p>
                                    <p className="font-medium text-gray-900">{selectedCase.violation?.title}</p>
                                </div>
                                <div className="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <p className="text-xs font-medium text-gray-500 mb-1">Case Status</p>
                                    <p className="font-medium text-gray-900">{selectedCase.status}</p>
                                </div>
                            </div>
                            <div>
                                <p className="text-xs font-medium text-gray-500 mb-2">Case Narrative</p>
                                <div className="p-4 bg-gray-50 rounded-lg border border-gray-200 text-gray-700 text-sm whitespace-pre-wrap">
                                    {selectedCase.description || 'No descriptive evidence provided in the initial filing.'}
                                </div>
                            </div>
                        </div>
                        <div className="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 mt-auto">
                            <button onClick={() => setIsModalOpen(false)} className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Close</button>
                            <a href={route('cases.show', selectedCase.id)} className="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                Full Details
                            </a>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
