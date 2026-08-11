import React, { useEffect, useRef } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    LineChart, FileSpreadsheet, FileDown, Printer, ClipboardX, Tag
} from 'lucide-react';
import dayjs from 'dayjs';
import PageMotion, { MotionItem } from '@/Components/PageMotion';
import PageHero from '@/Components/PageHero';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import FilterBar, { filterFieldClass, filterLabelClass } from '@/Components/FilterBar';
import useInertiaLoading from '@/hooks/useInertiaLoading';

export default function Index({ cases, departments, filters }) {
    const isLoading = useInertiaLoading();
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

    const clearFilters = () => {
        router.get(route('reports.index'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Violation Reports</h2>}
        >
            <Head title="Reports & Analytics" />

            <PageMotion className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    <MotionItem>
                        <PageHero
                            badge="Administrative Insights"
                            badgeIcon={LineChart}
                            title="Reports & Analytics"
                            description="Generate offense summaries, export PDF or CSV, and review the violation ledger."
                        >
                            <a href={route('reports.csv', filters)} className="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold hover:bg-white/20 transition-all flex items-center gap-2">
                                <FileSpreadsheet className="w-4 h-4 text-emerald-300" />
                                Export CSV
                            </a>
                            <a href={route('reports.pdf', filters)} className="px-5 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-bold hover:bg-white/20 transition-all flex items-center gap-2">
                                <FileDown className="w-4 h-4 text-rose-300" />
                                Export PDF
                            </a>
                            <a href={route('reports.print', filters)} target="_blank" className="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                                <Printer className="w-4 h-4" />
                                Print Report
                            </a>
                        </PageHero>
                    </MotionItem>

                    <MotionItem>
                        <FilterBar
                            search={data.student_search}
                            onSearchChange={(value) => setData('student_search', value)}
                            onClear={clearFilters}
                            placeholder="Name, course or student ID..."
                            filtersClassName="grid grid-cols-1 sm:grid-cols-2 gap-4 [&>*]:min-w-0"
                        >
                            <div className="min-w-0">
                                <label htmlFor="report-department" className={filterLabelClass}>Department</label>
                                <select
                                    id="report-department"
                                    value={data.department}
                                    onChange={(e) => setData('department', e.target.value)}
                                    className={filterFieldClass}
                                >
                                    <option value="">All Departments</option>
                                    {departments.map((dept, idx) => (
                                        <option key={idx} value={dept}>{dept}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="min-w-0">
                                <label htmlFor="report-status" className={filterLabelClass}>Current Status</label>
                                <select
                                    id="report-status"
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className={filterFieldClass}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Hearing Scheduled">Hearing Scheduled</option>
                                    <option value="endorsed">Endorsed</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </FilterBar>
                    </MotionItem>

                    <MotionItem className={`bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl rounded-3xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden ${isLoading ? 'opacity-70' : ''}`}>
                        <div className="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                            {cases.data.length === 0 ? (
                                <EmptyState
                                    icon={ClipboardX}
                                    title="No Violation Records Found"
                                    message="Adjust filters or search keywords to generate ledger results."
                                />
                            ) : (
                                cases.data.map((item) => (
                                    <div key={item.id} className="p-4 space-y-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="text-sm font-bold text-slate-900 dark:text-white">{item.student?.full_name || 'Anonymous'}</p>
                                                <p className="text-xs text-slate-500">{item.student?.department || 'N/A'}</p>
                                            </div>
                                            <StatusBadge item={item} variant="compact" />
                                        </div>
                                        <p className="text-sm font-semibold text-slate-800 dark:text-slate-200">{item.violation?.title || 'Undefined Infraction'}</p>
                                        <div className="flex items-center justify-between text-xs text-slate-500">
                                            <span>{dayjs(item.occurred_at).format('MMM DD, YYYY')}</span>
                                            <a href={route('cases.print', item.id)} target="_blank" className="inline-flex items-center gap-1 font-bold text-indigo-600">
                                                <Printer className="w-3.5 h-3.5" />
                                                Print
                                            </a>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                        <div className="hidden md:block overflow-x-auto">
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
                                                <StatusBadge item={item} variant="compact" />
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
                                            <td colSpan="5">
                                                <EmptyState
                                                    icon={ClipboardX}
                                                    title="No Violation Records Found"
                                                    message="Adjust filters, search keywords, or specify different departments to generate custom ledger results."
                                                />
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
                    </MotionItem>
                </div>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
