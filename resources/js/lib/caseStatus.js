import { AlertCircle, CheckCircle, Clock } from 'lucide-react';

/** Canonical case status display + colors (used across all pages). */
export const CASE_STATUS_CONFIG = {
    'Pending': {
        label: 'Pending',
        bg: 'bg-amber-50 dark:bg-amber-900/20',
        text: 'text-amber-600 dark:text-amber-400',
        border: 'border-amber-200 dark:border-amber-500/20',
        dot: 'bg-amber-500',
        icon: Clock,
    },
    'Open': {
        label: 'Open',
        bg: 'bg-rose-50 dark:bg-rose-900/20',
        text: 'text-rose-600 dark:text-rose-400',
        border: 'border-rose-200 dark:border-rose-500/20',
        dot: 'bg-rose-500',
        icon: AlertCircle,
    },
    'Hearing Scheduled': {
        label: 'Hearing Scheduled',
        shortLabel: 'Scheduled',
        bg: 'bg-blue-50 dark:bg-blue-900/20',
        text: 'text-blue-600 dark:text-blue-400',
        border: 'border-blue-200 dark:border-blue-500/20',
        dot: 'bg-blue-500',
        icon: AlertCircle,
    },
    'Hearing': {
        label: 'Hearing',
        bg: 'bg-indigo-50 dark:bg-indigo-900/20',
        text: 'text-indigo-600 dark:text-indigo-400',
        border: 'border-indigo-200 dark:border-indigo-500/20',
        dot: 'bg-indigo-500',
        icon: AlertCircle,
    },
    'Endorsed to Grievance': {
        label: 'Endorsed',
        shortLabel: 'Endorsed',
        bg: 'bg-rose-50 dark:bg-rose-900/20',
        text: 'text-rose-600 dark:text-rose-400',
        border: 'border-rose-200 dark:border-rose-500/20',
        dot: 'bg-rose-500',
        icon: AlertCircle,
    },
    'Dismissed': {
        label: 'Dismissed',
        bg: 'bg-slate-100 dark:bg-slate-800',
        text: 'text-slate-500 dark:text-slate-400',
        border: 'border-slate-200 dark:border-slate-700',
        dot: 'bg-slate-400',
        icon: CheckCircle,
    },
    'Closed': {
        label: 'Closed',
        bg: 'bg-emerald-50 dark:bg-emerald-900/20',
        text: 'text-emerald-600 dark:text-emerald-400',
        border: 'border-emerald-200 dark:border-emerald-500/20',
        dot: 'bg-emerald-500',
        icon: CheckCircle,
    },
};

export function resolveCaseStatus(itemOrStatus) {
    if (typeof itemOrStatus === 'string') {
        return itemOrStatus;
    }
    if (itemOrStatus?.endorsed_at) {
        return 'Endorsed to Grievance';
    }
    return itemOrStatus?.status ?? 'Pending';
}

export function getCaseStatusConfig(status) {
    return CASE_STATUS_CONFIG[status] ?? {
        label: status,
        bg: 'bg-slate-50 dark:bg-slate-800',
        text: 'text-slate-600 dark:text-slate-400',
        border: 'border-slate-200 dark:border-slate-700',
        dot: 'bg-slate-400',
        icon: AlertCircle,
    };
}
