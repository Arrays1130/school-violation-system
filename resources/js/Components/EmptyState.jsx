import React from 'react';

export default function EmptyState({
    icon: Icon,
    title,
    message,
    action = null,
    className = '',
}) {
    return (
        <div className={`flex flex-col items-center justify-center text-center py-16 px-6 ${className}`}>
            {Icon && (
                <div className="w-16 h-16 mb-5 rounded-3xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center border border-slate-100 dark:border-slate-800 shadow-inner">
                    <Icon className="w-8 h-8 text-slate-300 dark:text-slate-500" />
                </div>
            )}
            <p className="text-slate-900 dark:text-white font-black text-base mb-1">{title}</p>
            {message && (
                <p className="text-slate-500 dark:text-slate-400 text-sm font-medium max-w-md">{message}</p>
            )}
            {action && <div className="mt-6">{action}</div>}
        </div>
    );
}
