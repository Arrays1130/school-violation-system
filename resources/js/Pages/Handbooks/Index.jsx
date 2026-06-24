import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { BookOpen, Plus, Search, FileText, Clock, Calendar, Eye, Edit3, Trash2 } from 'lucide-react';
import Pagination from '@/Components/Pagination';

export default function Index({ auth, handbooks, filters, flash }) {
    const [searchQuery, setSearchQuery] = useState(filters?.search || '');
    const isFirstRender = React.useRef(true);

    React.useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        const timeout = setTimeout(() => {
            router.get(route('handbooks.index'), { search: searchQuery }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
        return () => clearTimeout(timeout);
    }, [searchQuery]);

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('handbooks.index'), { search: searchQuery }, { preserveState: true, preserveScroll: true });
    };

    const deleteHandbook = (id) => {
        if (confirm('Are you sure you want to delete this handbook document?')) {
            router.delete(route('handbooks.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Handbooks & Policies</h2>}
        >
            <Head title="Handbooks" />

            <div className="space-y-8 max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                
                {flash?.success && (
                    <div className="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/60 p-4 rounded-xl flex items-center gap-3 shadow-sm shadow-emerald-500/5">
                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <BookOpen className="w-5 h-5" />
                        </div>
                        <p className="text-sm font-semibold text-emerald-800">{flash.success}</p>
                    </div>
                )}

                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                <BookOpen className="w-3.5 h-3.5 text-indigo-400 animate-pulse" />
                                University Guidelines
                            </div>
                            <h2 className="text-2xl font-bold text-white tracking-tight">Handbooks & Policies</h2>
                            <p className="text-indigo-100/70 text-xs mt-1.5 leading-relaxed">Manage institutional guidelines and student conduct protocols.</p>
                        </div>
                        <Link href={route('handbooks.create')} className="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 rounded-xl text-sm font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all duration-200 self-start md:self-auto shrink-0">
                            <Plus className="w-4.5 h-4.5" />
                            <span>Add Document</span>
                        </Link>
                    </div>
                </div>

                {/* Upgraded Search Bar */}
                <div className="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-gray-200/80 dark:border-slate-700/80">
                    <form onSubmit={handleSearch} className="flex flex-col md:flex-row gap-3 items-center">
                        <div className="flex-1 relative w-full">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={e => setSearchQuery(e.target.value)}
                                placeholder="Search regulation titles, codes or contents..."
                                className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                            />
                        </div>
                        <button type="submit" className="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all duration-200 w-full md:w-auto shrink-0">
                            Search Policies
                        </button>
                    </form>
                </div>

                {/* Premium Card Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {handbooks.data.length > 0 ? (
                        handbooks.data.map(handbook => {
                            const updatedDate = new Date(handbook.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            return (
                                <div key={handbook.id} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden hover:shadow-md hover:border-indigo-200 transition-all duration-250 group">
                                    <div className="p-6 space-y-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-inner">
                                                <BookOpen className="w-5 h-5" />
                                            </div>
                                            
                                            {handbook.attachment ? (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 rounded-xl text-[10px] font-bold border border-emerald-100 uppercase tracking-wide">
                                                    <FileText className="w-3.5 h-3.5" />
                                                    PDF Attachment
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-xl text-[10px] font-bold border border-gray-200 dark:border-slate-700 uppercase tracking-wide">
                                                    Text Only
                                                </span>
                                            )}
                                        </div>

                                        <div className="space-y-1.5">
                                            <h3 className="text-sm font-bold text-slate-850 group-hover:text-indigo-600 dark:text-indigo-400 transition-all duration-200 line-clamp-2">
                                                <Link href={route('handbooks.show', handbook.id)}>{handbook.title}</Link>
                                            </h3>
                                            <p className="text-[10px] font-bold text-slate-400 flex items-center gap-1 uppercase tracking-wider">
                                                <Clock className="w-3.5 h-3.5 text-gray-300" />
                                                Updated {updatedDate}
                                            </p>
                                        </div>

                                        <p className="text-xs text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50/50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800 line-clamp-3 font-medium">
                                            "{handbook.content ? handbook.content.substring(0, 120) : 'No content available...'}"
                                        </p>
                                    </div>

                                    <div className="px-6 py-4.5 bg-slate-50/70 dark:bg-slate-800/70 border-t border-gray-150 flex items-center justify-between">
                                        <div className="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            <Calendar className="w-4 h-4 text-slate-450" />
                                            <span>{updatedDate}</span>
                                        </div>

                                        <div className="flex items-center gap-1.5">
                                            <Link href={route('handbooks.show', handbook.id)} className="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 border border-transparent hover:border-indigo-100 flex items-center justify-center transition-all" title="View">
                                                <Eye className="w-4 h-4" />
                                            </Link>
                                            <Link href={route('handbooks.edit', handbook.id)} className="w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 border border-transparent hover:border-indigo-100 flex items-center justify-center transition-all" title="Edit">
                                                <Edit3 className="w-4 h-4" />
                                            </Link>
                                            <button 
                                                onClick={() => deleteHandbook(handbook.id)} 
                                                className="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:bg-rose-900/20 border border-transparent hover:border-rose-100 flex items-center justify-center transition-all"
                                                title="Delete"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    ) : (
                        <div className="col-span-full py-16 bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-700 flex flex-col items-center justify-center text-center">
                            <div className="w-14 h-14 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-center mb-4 shadow-sm text-slate-400">
                                <BookOpen className="w-6.5 h-6.5 text-indigo-500 animate-bounce" />
                            </div>
                            <h4 className="text-sm font-bold text-slate-800 dark:text-slate-200">No Handbooks Found</h4>
                            <p className="text-xs text-slate-400 mt-1 max-w-sm leading-relaxed">We couldn't find any policies matching your search criteria. Try a different query or add a new handbook.</p>
                            <Link href={route('handbooks.create')} className="mt-5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/25 transition-all duration-200">
                                Add Document
                            </Link>
                        </div>
                    )}
                </div>

                {/* Pagination */}
                {handbooks.links && handbooks.links.length > 3 && (
                    <div className="bg-white dark:bg-slate-900 border border-gray-150 rounded-2xl px-6 py-4.5 shadow-sm">
                        <Pagination links={handbooks.links} />
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
