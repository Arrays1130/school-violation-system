import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Edit2, Bookmark, Type, Link as LinkIcon, Save, UploadCloud, FileText, Trash2 } from 'lucide-react';
import Breadcrumbs from '@/Components/Breadcrumbs';

export default function Edit({ auth, handbook }) {
    const { data, setData, post, processing, errors } = useForm({
        title: handbook.title || '',
        content: handbook.content || '',
        attachment: handbook.attachment || '',
        document: null,
        remove_document: false,
        _method: 'put',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('handbooks.update', handbook.id), { forceFormData: true });
    };

    const currentFileLabel = handbook.file_name || (handbook.file_path ? handbook.file_path.split('/').pop() : null);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Edit Handbook Entry</h2>}
        >
            <Head title="Edit Handbook Entry" />

            <div className="max-w-3xl mx-auto space-y-8 py-8 px-4 sm:px-6 lg:px-8">
                <Breadcrumbs items={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Handbook', href: route('handbooks.index') },
                    { label: handbook.title, href: route('handbooks.show', handbook.id) },
                    { label: 'Edit' },
                ]} />

                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="relative flex items-center gap-5">
                        <Link href={route('handbooks.show', handbook.id)} className="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                <Edit2 className="w-3.5 h-3.5 text-indigo-400" />
                                Edit Handbook Entry
                            </div>
                            <h2 className="text-2xl font-bold text-white tracking-tight">Edit Policy Entry</h2>
                            <p className="text-indigo-100/70 text-xs mt-1">Updating Entry: <span className="text-white font-medium">{handbook.title}</span></p>
                        </div>
                    </div>
                </div>

                <div className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
                    <form onSubmit={submit}>
                        <div className="p-8 space-y-8">
                            <div className="space-y-6">
                                <div className="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-slate-800">
                                    <Bookmark className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    <h3 className="text-sm font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Policy Details</h3>
                                </div>

                                <div>
                                    <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Title *</label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <Type className="w-4.5 h-4.5" />
                                        </div>
                                        <input
                                            type="text"
                                            value={data.title}
                                            onChange={e => setData('title', e.target.value)}
                                            placeholder="e.g. Student Code of Conduct"
                                            required
                                            autoFocus
                                            className="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-gray-950 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                                        />
                                    </div>
                                    {errors.title && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.title}</p>}
                                </div>

                                <div>
                                    <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Content & Description</label>
                                    <textarea
                                        value={data.content}
                                        onChange={e => setData('content', e.target.value)}
                                        rows="12"
                                        placeholder="Describe the rule in detail..."
                                        className="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-gray-950 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 leading-relaxed font-mono text-xs"
                                    />
                                    {errors.content && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.content}</p>}
                                </div>

                                <div>
                                    <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Upload PDF / Document</label>

                                    {currentFileLabel && !data.remove_document && !data.document && (
                                        <div className="mb-3 flex items-center justify-between gap-3 rounded-xl border border-emerald-100 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/20 px-4 py-3">
                                            <div className="flex items-center gap-2 min-w-0">
                                                <FileText className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                                <a href={route('handbooks.download', handbook.id)} className="text-sm font-bold text-emerald-800 dark:text-emerald-200 truncate hover:underline">
                                                    {currentFileLabel}
                                                </a>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => setData('remove_document', true)}
                                                className="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-700"
                                            >
                                                <Trash2 className="w-3.5 h-3.5" />
                                                Remove
                                            </button>
                                        </div>
                                    )}

                                    {data.remove_document && !data.document && (
                                        <p className="mb-3 text-xs font-semibold text-amber-700 dark:text-amber-300">Current file will be removed on save. Upload a replacement if needed.</p>
                                    )}

                                    <label className="flex flex-col items-center justify-center gap-2 w-full px-4 py-8 bg-slate-50 dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-all">
                                        <UploadCloud className="w-8 h-8 text-indigo-500" />
                                        <span className="text-sm font-bold text-slate-700 dark:text-slate-200">
                                            {data.document ? data.document.name : 'Click to upload or replace PDF / DOC'}
                                        </span>
                                        <span className="text-[11px] text-slate-400 font-medium">Max 10MB</span>
                                        <input
                                            type="file"
                                            accept=".pdf,.doc,.docx,application/pdf"
                                            className="hidden"
                                            onChange={e => {
                                                setData({
                                                    ...data,
                                                    document: e.target.files?.[0] || null,
                                                    remove_document: false,
                                                });
                                            }}
                                        />
                                    </label>
                                    {errors.document && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.document}</p>}
                                </div>

                                <div>
                                    <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">External Document Link (URL)</label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <LinkIcon className="w-4.5 h-4.5" />
                                        </div>
                                        <input
                                            type="url"
                                            value={data.attachment}
                                            onChange={e => setData('attachment', e.target.value)}
                                            placeholder="https://example.com/policy.pdf"
                                            className="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-gray-950 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                                        />
                                    </div>
                                    {errors.attachment && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.attachment}</p>}
                                </div>
                            </div>
                        </div>

                        <div className="px-8 py-5 bg-gray-50 dark:bg-slate-800 border-t border-gray-150 flex items-center justify-end gap-3">
                            <Link href={route('handbooks.show', handbook.id)} className="px-4 py-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-all">
                                Cancel
                            </Link>
                            <button disabled={processing} type="submit" className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50">
                                <Save className="w-4.5 h-4.5" />
                                {processing ? 'Saving...' : 'Update Policy'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
