import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft, Eye, Edit, Users, FolderOpen, Paperclip, Download,
} from 'lucide-react';
import EmptyState from '@/Components/EmptyState';
import Pagination from '@/Components/Pagination';
import StatusBadge from '@/Components/StatusBadge';
import useInertiaLoading from '@/hooks/useInertiaLoading';

const MAX_VISIBLE_FILES = 3;

function truncateText(text, max = 160) {
    if (!text) return '';
    const cleaned = String(text).trim();
    if (cleaned.length <= max) return cleaned;
    return `${cleaned.slice(0, max).trimEnd()}…`;
}

function formatDay(dateStr) {
    if (!dateStr) return '—';
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}

function EvidenceList({ attachments = [], caseId }) {
    if (!attachments.length) {
        return (
            <p className="text-xs text-slate-400 dark:text-slate-500 italic">No evidence files</p>
        );
    }

    const visible = attachments.slice(0, MAX_VISIBLE_FILES);
    const remaining = attachments.length - visible.length;

    return (
        <ul className="space-y-1.5">
            {visible.map((file) => {
                const label = file.label || file.file_name || `File #${file.id}`;
                return (
                    <li key={file.id} className="flex items-center gap-1.5 min-w-0">
                        <Paperclip className="w-3.5 h-3.5 shrink-0 text-slate-400" aria-hidden="true" />
                        <a
                            href={route('attachments.view', file.id)}
                            className="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline truncate"
                            title={label}
                        >
                            {label}
                        </a>
                        <a
                            href={route('attachments.download', file.id)}
                            className="shrink-0 p-0.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded"
                            title={`Download ${label}`}
                            aria-label={`Download ${label}`}
                        >
                            <Download className="w-3 h-3" />
                        </a>
                    </li>
                );
            })}
            {remaining > 0 && (
                <li>
                    <Link
                        href={route('cases.show', caseId)}
                        className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        +{remaining} more
                    </Link>
                </li>
            )}
        </ul>
    );
}

export default function ByViolationDay({
    auth,
    violation,
    dayGroup,
    cases,
    filters = {},
}) {
    const isLoading = useInertiaLoading();

    const backHref = (() => {
        const params = Object.fromEntries(
            Object.entries(filters || {}).filter(([, v]) => v !== '' && v != null),
        );
        return route('cases.by-violation', { violation: violation.id, ...params });
    })();

    const containerVariants = {
        hidden: { opacity: 0 },
        show: { opacity: 1, transition: { staggerChildren: 0.08 } },
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 16 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } },
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Violation Cases</h2>}
        >
            <Head title={`${dayGroup.display_label} — Students`} />

            <motion.div
                className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >
                <motion.div variants={itemVariants} className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />

                    <div className="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <Link
                                href={backHref}
                                className="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all"
                            >
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2">
                                    <Users className="w-3.5 h-3.5" />
                                    {violation.code} · {dayGroup.sequence_label}
                                </div>
                                <h1 className="text-2xl font-extrabold text-white tracking-tight">{dayGroup.display_label}</h1>
                                <p className="text-indigo-100/70 text-sm mt-1.5">
                                    {formatDay(dayGroup.date)} · {dayGroup.student_count} student{dayGroup.student_count === 1 ? '' : 's'} involved
                                </p>
                            </div>
                        </div>
                    </div>
                </motion.div>

                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    {isLoading && (
                        <div className="h-0.5 w-full bg-indigo-500/70 animate-pulse" aria-hidden="true" />
                    )}
                    <div className={`overflow-x-auto no-scrollbar transition-opacity duration-200 ${isLoading ? 'opacity-50' : 'opacity-100'}`}>
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left block md:table">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80 hidden md:table-header-group">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Student</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest min-w-[12rem]">Details</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest min-w-[10rem]">Evidence</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800 block md:table-row-group">
                                {cases.data.length > 0 ? (
                                    cases.data.map((item) => {
                                        const attachments = item.attachments ?? [];
                                        const studentMeta = [
                                            item.student?.year_level,
                                            item.student?.department,
                                            item.student?.section,
                                        ].filter(Boolean).join(' · ');

                                        return (
                                            <motion.tr
                                                variants={itemVariants}
                                                key={item.id}
                                                className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors duration-150 group block md:table-row border-b border-slate-100 dark:border-slate-800 md:border-none p-4 md:p-0"
                                            >
                                                <td className="px-2 md:px-6 py-2 md:py-4 block md:table-cell">
                                                    <span className="md:hidden text-[10px] font-bold uppercase tracking-wider text-slate-400">Student</span>
                                                    <div className="flex flex-col min-w-0">
                                                        <span className="text-sm font-bold text-slate-900 dark:text-white">
                                                            {item.student?.full_name || 'Unknown student'}
                                                        </span>
                                                        {studentMeta && (
                                                            <span className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                                {studentMeta}
                                                            </span>
                                                        )}
                                                        {item.case_code && (
                                                            <span className="text-[11px] font-semibold text-sky-600 dark:text-sky-400 mt-1">
                                                                Record {item.case_code}
                                                            </span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                    <span className="md:hidden text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</span>
                                                    <StatusBadge item={item} variant="compact" />
                                                </td>
                                                <td className="px-2 md:px-6 py-2 md:py-4 block md:table-cell max-w-xs">
                                                    <span className="md:hidden text-[10px] font-bold uppercase tracking-wider text-slate-400">Details</span>
                                                    <div className="flex flex-col gap-1.5">
                                                        <p className="text-sm text-slate-700 dark:text-slate-300 leading-snug line-clamp-2">
                                                            {item.description
                                                                ? truncateText(item.description)
                                                                : (
                                                                    <span className="text-slate-400 dark:text-slate-500 italic">No description</span>
                                                                )}
                                                        </p>
                                                        {item.sanction && (
                                                            <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                                                <span className="font-semibold">Sanction:</span> {item.sanction}
                                                            </p>
                                                        )}
                                                        <Link
                                                            href={route('cases.show', item.id)}
                                                            className="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline w-fit"
                                                        >
                                                            View full case
                                                        </Link>
                                                    </div>
                                                </td>
                                                <td className="px-2 md:px-6 py-2 md:py-4 block md:table-cell">
                                                    <span className="md:hidden text-[10px] font-bold uppercase tracking-wider text-slate-400">Evidence</span>
                                                    <EvidenceList attachments={attachments} caseId={item.id} />
                                                </td>
                                                <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell text-left md:text-right text-sm font-medium mt-4 md:mt-0 border-t border-slate-100 dark:border-slate-800 md:border-none pt-4 md:pt-4">
                                                    <div className="flex items-center justify-start md:justify-end gap-1">
                                                        <Link
                                                            href={route('cases.show', item.id)}
                                                            className="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all duration-150"
                                                            title="View Case Details"
                                                            aria-label={`View case for ${item.student?.full_name ?? 'student'}`}
                                                        >
                                                            <Eye className="w-4 h-4" />
                                                        </Link>
                                                        {item.status !== 'Closed' && (
                                                            <Link
                                                                href={route('cases.edit', item.id)}
                                                                className="p-2 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-all duration-150"
                                                                title="Edit Case"
                                                                aria-label={`Edit case for ${item.student?.full_name ?? 'student'}`}
                                                            >
                                                                <Edit className="w-4 h-4" />
                                                            </Link>
                                                        )}
                                                    </div>
                                                </td>
                                            </motion.tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="5">
                                            <EmptyState
                                                icon={FolderOpen}
                                                title="No students found."
                                                message="No students match your filters for this day case."
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {cases.links && cases.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={cases.links} />
                        </div>
                    )}
                </motion.div>
            </motion.div>
        </AuthenticatedLayout>
    );
}
