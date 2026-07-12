import React from 'react';
import { Search, X } from 'lucide-react';

export const filterFieldClass =
    'w-full min-w-0 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500';

export const filterLabelClass =
    'block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2';

export default function FilterBar({
    search,
    onSearchChange,
    onClear,
    children,
    placeholder = 'Search...',
    className = '',
    filtersClassName = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 [&>*]:min-w-0',
}) {
    return (
        <div className={`bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-[0_2px_16px_-4px_rgba(0,0,0,0.45)] p-4 md:p-5 ${className}`}>
            <div className="flex flex-col sm:flex-row gap-4 sm:items-end">
                <div className="flex-1 min-w-0 w-full">
                    <label htmlFor="filter-search" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        Search
                    </label>
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" aria-hidden="true" />
                        <input
                            id="filter-search"
                            type="search"
                            value={search}
                            onChange={(e) => onSearchChange(e.target.value)}
                            placeholder={placeholder}
                            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                    </div>
                </div>
                {onClear && (
                    <button
                        type="button"
                        onClick={onClear}
                        className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shrink-0 w-full sm:w-auto"
                    >
                        <X className="w-4 h-4" aria-hidden="true" />
                        Clear
                    </button>
                )}
            </div>
            {children && (
                <div className="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div className={filtersClassName}>
                        {children}
                    </div>
                </div>
            )}
        </div>
    );
}
