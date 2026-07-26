import React, { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ConfirmDialog from '@/Components/ConfirmDialog';
import FilterBar from '@/Components/FilterBar';
import { 
    ShieldCheck, Plus, 
    Edit3, Trash2, ShieldQuestion
} from 'lucide-react';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

export default function Index({ auth, violations, categories, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [category, setCategory] = useState(filters?.category || '');
    const searchInputRef = useRef(null);

    // Debounced search
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters?.search || '') || category !== (filters?.category || '')) {
                const el = searchInputRef.current;
                const hadFocus = el && document.activeElement === el;
                const caret = el ? el.selectionStart : null;

                router.get(route('violations.index'), { search, category }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ['violations', 'filters'],
                    onFinish: () => {
                        if (hadFocus && searchInputRef.current) {
                            const input = searchInputRef.current;
                            input.focus();
                            if (caret !== null) {
                                try { input.setSelectionRange(caret, caret); } catch (_) {}
                            }
                        }
                    },
                });
            }
        }, 600);
        return () => clearTimeout(timer);
    }, [search, category]);

    const handleClear = () => {
        setSearch('');
        setCategory('');
        router.get(route('violations.index'));
    };

    const [deleteRuleId, setDeleteRuleId] = useState(null);

    const handleDelete = (id) => {
        setDeleteRuleId(id);
    };

    const confirmDelete = () => {
        if (deleteRuleId) {
            router.delete(route('violations.destroy', deleteRuleId));
            setDeleteRuleId(null);
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Rules & Regulations</h2>}
        >
            <Head title="Rules & Regulations" />

            <ConfirmDialog
                open={!!deleteRuleId}
                onClose={() => setDeleteRuleId(null)}
                onConfirm={confirmDelete}
                title="Delete Rule?"
                description="Are you sure you want to delete this rule? This cannot be undone."
                confirmLabel="Yes, delete it"
                destructive
            />

            <PageMotion>
                
                {/* MODERN PRISM HEADER */}
                <MotionItem className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <ShieldCheck className="w-3.5 h-3.5" />
                                Policy Management
                            </div>
                            <h1 className="text-3xl font-extrabold text-white tracking-tight">Rules & Regulations</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage the school violation rulebook — add, edit, or remove policy entries.</p>
                        </div>
                        
                        <div className="flex flex-wrap items-center gap-3">
                            <a href={route('violations.create')} className="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                <Plus className="w-4.5 h-4.5" />
                                Add Rule Category
                            </a>
                        </div>
                    </div>
                </MotionItem>

                {/* Search & Filters */}
                <MotionItem>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search rules, keywords, or codes..."
                    >
                        <div className="sm:max-w-xs">
                            <label htmlFor="violation-category" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Category</label>
                            <select id="violation-category" value={category} onChange={(e) => setCategory(e.target.value)} className="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Categories</option>
                                {categories.map((cat, index) => <option key={index} value={cat}>{cat}</option>)}
                            </select>
                        </div>
                    </FilterBar>
                </MotionItem>

                {/* Categories List */}
                <MotionItem className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden relative">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                                    <th className="px-6 py-5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Violation Rule / Code</th>
                                    <th className="px-6 py-5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center whitespace-nowrap">Category Class</th>
                                    <th className="px-6 py-5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center whitespace-nowrap">Severity</th>
                                    <th className="px-6 py-5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                {violations.data.length > 0 ? (
                                    violations.data.map((violation) => (
                                        <tr
                                            key={violation.id}
                                            className="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors duration-200"
                                        >
                                            <td className="px-6 py-5">
                                                <div className="flex items-center gap-4">
                                                    <div className="flex-shrink-0">
                                                        <span className="inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-xs border border-slate-300/50 uppercase tracking-wider shadow-sm">
                                                            {violation.code}
                                                        </span>
                                                    </div>
                                                    <div className="min-w-0">
                                                        <p className="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{violation.title}</p>
                                                        <div className="flex items-center gap-2 mt-0.5">
                                                            <p className="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Identifier: <span className="text-slate-400">#{String(violation.id).padStart(4, '0')}</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-5 text-center">
                                                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                                                    {violation.category}
                                                </span>
                                            </td>
                                            <td className="px-6 py-5 text-center">
                                                {violation.severity === 'Major' ? (
                                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-900/20 border border-rose-200 text-rose-700 shadow-sm">
                                                        <span className="relative flex h-2 w-2">
                                                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                            <span className="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                                        </span>
                                                        Major
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 text-emerald-700 shadow-sm">
                                                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Minor
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-5 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <a 
                                                        href={route('violations.edit', violation.id)} 
                                                        className="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:shadow-sm transition-all duration-200"
                                                        title="Edit Rule"
                                                        aria-label={`Edit rule: ${violation.title}`}
                                                    >
                                                        <Edit3 className="w-4 h-4" />
                                                    </a>
                                                    <button 
                                                        onClick={() => handleDelete(violation.id)} 
                                                        className="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:shadow-sm transition-all duration-200"
                                                        title="Delete Rule"
                                                        aria-label={`Delete rule: ${violation.title}`}
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4" className="px-6 py-16 text-center">
                                            <div className="flex flex-col items-center justify-center text-slate-400 max-w-sm mx-auto">
                                                <div className="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                                                    <ShieldQuestion className="w-7 h-7" />
                                                </div>
                                                <h3 className="text-base font-bold text-slate-800 dark:text-slate-200">No Guidelines Found</h3>
                                                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">We couldn't find any rules matching your search. Create a rule type to instantiate the standard handbook registry.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {violations.links && violations.links.length > 3 && (
                        <div className="px-6 py-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                            <div className="text-sm text-slate-500 dark:text-slate-400">
                                Showing <span className="font-medium text-slate-900 dark:text-white">{violations.from || 0}</span> to <span className="font-medium text-slate-900 dark:text-white">{violations.to || 0}</span> of <span className="font-medium text-slate-900 dark:text-white">{violations.total}</span> results
                            </div>
                            <div className="flex gap-1">
                                {violations.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url || '#'}
                                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                                            link.active 
                                                ? 'bg-indigo-600 text-white' 
                                                : link.url 
                                                    ? 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800' 
                                                    : 'bg-transparent text-slate-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </MotionItem>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
