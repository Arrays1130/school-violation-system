import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

// items: [{ label: 'Dashboard', href: route('dashboard') }, { label: 'Current Page' }]
// The last item (or any item without href) renders as plain text.
export default function Breadcrumbs({ items = [], className = '' }) {
    if (!items.length) return null;

    return (
        <nav aria-label="Breadcrumb" className={`flex items-center flex-wrap gap-2 text-sm text-slate-500 dark:text-slate-400 ${className}`}>
            {items.map((item, idx) => (
                <span key={idx} className="flex items-center gap-2">
                    {idx > 0 && <ChevronRight className="w-4 h-4 text-slate-300 dark:text-slate-600" aria-hidden="true" />}
                    {item.href ? (
                        <Link href={item.href} className="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                            {item.label}
                        </Link>
                    ) : (
                        <span className="text-slate-800 dark:text-slate-200 font-bold" aria-current="page">
                            {item.label}
                        </span>
                    )}
                </span>
            ))}
        </nav>
    );
}
