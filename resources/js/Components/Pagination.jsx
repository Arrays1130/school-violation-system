import React from 'react';
import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    return (
        <div className="flex flex-wrap items-center justify-center gap-1.5 mt-2">
            {links.map((link, key) => (
                link.url === null ? (
                    <div
                        key={key}
                        className="px-3 py-1.5 text-xs font-medium text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg cursor-not-allowed"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <Link
                        key={key}
                        href={link.url}
                        className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors border ${
                            link.active
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                                : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 hover:text-indigo-600 dark:text-indigo-400'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                )
            ))}
        </div>
    );
}
