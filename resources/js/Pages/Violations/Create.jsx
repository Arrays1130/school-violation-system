import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FilePlus2, Hash, Layers, Type, Save, Info, AlertTriangle, ChevronDown } from 'lucide-react';

const CATEGORIES = ['Appearance', 'Attendance', 'Conduct', 'Academic', 'Other'];

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        title: '',
        category: '',
        severity: 'Minor',
        default_description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('violations.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Add Violation Type</h2>}
        >
            <Head title="New Violation Type" />

            <div className="max-w-3xl mx-auto space-y-6 py-8 px-4 sm:px-6 lg:px-8">
                <div className="vt-page-hero">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="relative flex items-center gap-5">
                        <Link href={route('violations.index')} className="w-12 h-12 rounded-xl bg-slate-900/5 hover:bg-slate-700/80 border border-slate-600/80 flex items-center justify-center text-white/70 hover:text-white transition-all backdrop-blur-md">
                            <ArrowLeft className="w-5 h-5" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/10 border border-slate-600/80 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <FilePlus2 className="w-3.5 h-3.5" />
                                Creation Wizard
                            </div>
                            <h1 className="text-3xl font-extrabold text-white tracking-tight">New Violation Type</h1>
                            <p className="text-slate-400 text-sm mt-2">Define a new handbook rule and standard sanction.</p>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm overflow-hidden">
                    <form onSubmit={submit}>
                        <div className="p-8 space-y-8">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Violation Code</label>
                                    <div className="relative">
                                        <Hash className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <input
                                            type="text"
                                            value={data.code}
                                            onChange={(e) => setData('code', e.target.value)}
                                            placeholder="e.g. V-001"
                                            required
                                            className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-200 rounded-xl text-sm focus:bg-slate-900 dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                        />
                                    </div>
                                    {errors.code && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.code}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category</label>
                                    <div className="relative">
                                        <Layers className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <select
                                            value={data.category}
                                            onChange={(e) => setData('category', e.target.value)}
                                            required
                                            className="w-full pl-10 pr-10 py-3 bg-gray-50/50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-200 rounded-xl text-sm focus:bg-slate-900 dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none"
                                        >
                                            <option value="">Select Category...</option>
                                            {CATEGORIES.map((cat) => (
                                                <option key={cat} value={cat}>{cat}</option>
                                            ))}
                                        </select>
                                        <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    {errors.category && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.category}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Violation Title</label>
                                <div className="relative">
                                    <Type className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    <input
                                        type="text"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="Official violation title..."
                                        required
                                        className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-200 rounded-xl text-sm focus:bg-slate-900 dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    />
                                </div>
                                {errors.title && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.title}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Standard Sanction / Description</label>
                                <textarea
                                    value={data.default_description}
                                    onChange={(e) => setData('default_description', e.target.value)}
                                    rows={4}
                                    placeholder="Define the standard sanction for this violation..."
                                    className="w-full px-4 py-3 bg-gray-50/50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-200 rounded-xl text-sm focus:bg-slate-900 dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"
                                />
                                {errors.default_description && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.default_description}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Severity Level</label>
                                <div className="grid grid-cols-2 gap-4">
                                    {['Minor', 'Major'].map((level) => (
                                        <label key={level} className="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="severity"
                                                value={level}
                                                checked={data.severity === level}
                                                onChange={() => setData('severity', level)}
                                                className="sr-only peer"
                                            />
                                            <div className={`px-5 py-4 border rounded-xl text-center transition-all ${data.severity === level ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500 shadow-sm' : 'bg-gray-50/50 dark:bg-slate-800 border-gray-200 dark:border-slate-700'}`}>
                                                <div className="flex items-center justify-center gap-2">
                                                    {level === 'Major' ? <AlertTriangle className="w-4 h-4 text-rose-500" /> : <Info className="w-4 h-4 text-emerald-500" />}
                                                    <p className="text-sm font-bold text-gray-600 dark:text-slate-300 uppercase tracking-wider">{level}</p>
                                                </div>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                                {errors.severity && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.severity}</p>}
                            </div>
                        </div>

                        <div className="px-8 py-5 bg-gray-50 dark:bg-slate-800/50 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between">
                            <Link href={route('violations.index')} className="text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Cancel</Link>
                            <button type="submit" disabled={processing} className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2 disabled:opacity-50">
                                <Save className="w-4 h-4" />
                                Add Violation Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

