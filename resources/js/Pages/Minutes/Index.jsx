import React, { useState, useRef, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { FolderGit, UploadCloud, Plus, Files, FileText, Database, X, Tag, ChevronDown, Search, SlidersHorizontal, Trash2, Eye, UserSearch, CheckCircle2 } from 'lucide-react';
import Pagination from '@/Components/Pagination';

export default function Index({ auth, records, totalFiles, pdfFiles, totalSizeMB, cases, flash }) {
    const [uploadModal, setUploadModal] = useState(false);
    const [searchQuery, setSearchQuery] = useState(new URLSearchParams(window.location.search).get('search') || '');
    const [caseSearch, setCaseSearch] = useState('');
    const [caseDropdownOpen, setCaseDropdownOpen] = useState(false);
    const caseDropdownRef = useRef(null);

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (caseDropdownRef.current && !caseDropdownRef.current.contains(e.target)) {
                setCaseDropdownOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const { data, setData, post, processing, errors, reset } = useForm({
        case_id: '',
        file: null,
        label: ''
    });

    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        const timeout = setTimeout(() => {
            router.get(route('meeting-minutes.index'), { search: searchQuery }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
        return () => clearTimeout(timeout);
    }, [searchQuery]);

    const handleUploadSubmit = (e) => {
        e.preventDefault();
        post(route('meeting-minutes.upload'), {
            onSuccess: () => {
                setUploadModal(false);
                reset();
            }
        });
    };

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('meeting-minutes.index'), { search: searchQuery }, { preserveState: true, preserveScroll: true });
    };

    const deleteRecord = (url) => {
        if (confirm('Are you sure you want to delete this record?')) {
            router.delete(url);
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Document Repository</h2>}
        >
            <Head title="Minutes & Documents" />

            <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                {flash?.success && (
                    <div className="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/60 p-4 rounded-xl flex items-center gap-3 shadow-sm shadow-emerald-500/5">
                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <Files className="w-5 h-5" />
                        </div>
                        <p className="text-sm font-semibold text-emerald-800">{flash.success}</p>
                    </div>
                )}

                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    
                    <div className="relative flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-1.5 backdrop-blur-md">
                                <FolderGit className="w-3.5 h-3.5 text-indigo-400" />
                                Minutes & Documents
                            </div>
                            <h2 className="text-xl font-bold text-white tracking-tight">Document Repository</h2>
                            <p className="text-indigo-100/70 text-xs mt-0.5 leading-relaxed">Manage external attachments, documentary evidence, and recorded minutes.</p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2.5">
                            <button onClick={() => setUploadModal(true)} className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 rounded-xl text-[13px] font-bold text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all duration-200">
                                <UploadCloud className="w-4 h-4" />
                                <span>Upload Document</span>
                            </button>
                            <Link href={route('meeting-minutes.create')} className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white/10 border border-white/10 rounded-xl text-[13px] font-bold text-white hover:bg-white/20 shadow-sm backdrop-blur-md transition-all duration-200">
                                <Plus className="w-4 h-4" />
                                <span>Record Minutes</span>
                            </Link>
                        </div>
                    </div>

                    {/* Stats Summary */}
                    <div className="mt-4 pt-4 border-t border-white/10">
                        <div className="grid grid-cols-3 gap-3">
                            <div className="rounded-xl border border-white/5 bg-white/5 px-4 py-3 backdrop-blur-md flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] font-bold text-indigo-200/60 uppercase tracking-widest">Total Files</span>
                                    <div className="text-xl font-black text-white">{totalFiles}</div>
                                </div>
                                <div className="w-9 h-9 rounded-lg bg-white/10 text-indigo-300 flex items-center justify-center">
                                    <Files className="w-4.5 h-4.5" />
                                </div>
                            </div>
                            <div className="rounded-xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 to-indigo-950/30 px-4 py-3 flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] font-bold text-indigo-300/80 uppercase tracking-widest">PDF Documents</span>
                                    <div className="text-xl font-black text-white">{pdfFiles}</div>
                                </div>
                                <div className="w-9 h-9 rounded-lg bg-indigo-500/20 text-indigo-200 flex items-center justify-center">
                                    <FileText className="w-4.5 h-4.5" />
                                </div>
                            </div>
                            <div className="rounded-xl border border-white/5 bg-white/5 px-4 py-3 backdrop-blur-md flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] font-bold text-indigo-200/60 uppercase tracking-widest">Total Storage</span>
                                    <div className="text-xl font-black text-white">{totalSizeMB} <span className="text-xs font-semibold text-indigo-300">MB</span></div>
                                </div>
                                <div className="w-9 h-9 rounded-lg bg-white/10 text-indigo-300 flex items-center justify-center">
                                    <Database className="w-4.5 h-4.5" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Repository Container */}
                <div className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden p-8 space-y-6">
                    {/* Search & Filter */}
                    <div className="flex flex-col md:flex-row gap-4">
                        <form onSubmit={handleSearch} className="w-full flex flex-col sm:flex-row gap-3">
                            <div className="relative flex-1">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <Search className="w-4.5 h-4.5" />
                                </div>
                                <input 
                                    type="text" 
                                    value={searchQuery}
                                    onChange={e => setSearchQuery(e.target.value)}
                                    placeholder="Search documents or student names..." 
                                    className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                                />
                            </div>
                            <button type="submit" className="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2 shrink-0">
                                <SlidersHorizontal className="w-4 h-4" />
                                Search Records
                            </button>
                        </form>
                    </div>

                    {/* Files Table */}
                    <div className="overflow-hidden border border-gray-150 rounded-2xl">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100 dark:divide-slate-800 text-left">
                                <thead className="bg-slate-50/75 dark:bg-slate-800/75">
                                    <tr>
                                        <th className="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">File Name</th>
                                        <th className="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Linked Case</th>
                                        <th className="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Uploaded By</th>
                                        <th className="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Size</th>
                                        <th className="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-transparent divide-y divide-slate-50">
                                    {records.data && records.data.length > 0 ? (
                                        records.data.map((record, i) => {
                                            const recordDate = new Date(record.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                            return (
                                                <tr key={i} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-all duration-150">
                                                    <td className="px-6 py-4.5 whitespace-nowrap">
                                                        <div className="flex items-center gap-3.5">
                                                            <div className="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-100/50">
                                                                {record.type === 'text' ? <FileText className="w-4.5 h-4.5" /> : <Files className="w-4.5 h-4.5" />}
                                                            </div>
                                                            <div>
                                                                <p className="text-sm font-bold text-slate-800 dark:text-slate-200 leading-tight">{record.label}</p>
                                                                <p className="text-xs text-slate-400 mt-1 font-medium">{recordDate}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4.5 whitespace-nowrap">
                                                        {record.case ? (
                                                            <div className="flex flex-col">
                                                                <span className="text-sm font-semibold text-slate-700 dark:text-slate-300 capitalize">{record.case.student?.full_name || record.case.student?.first_name + ' ' + record.case.student?.last_name || 'N/A'}</span>
                                                                <span className="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mt-1">
                                                                    Case #{record.case.id}
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs font-bold text-slate-400 bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded-md">General Record</span>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4.5 whitespace-nowrap text-sm font-medium text-slate-600 dark:text-slate-400">
                                                        {record.uploader}
                                                    </td>
                                                    <td className="px-6 py-4.5 whitespace-nowrap text-sm font-medium text-slate-600 dark:text-slate-400">
                                                        {record.size}
                                                    </td>
                                                    <td className="px-6 py-4.5 whitespace-nowrap text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <a href={record.view_url} className="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-indigo-50 dark:bg-indigo-900/20 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 flex items-center justify-center transition-colors">
                                                                <Eye className="w-4 h-4" />
                                                            </a>
                                                            <button onClick={() => deleteRecord(record.delete_url)} className="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-rose-50 dark:bg-rose-900/20 text-slate-400 hover:text-rose-600 dark:text-rose-400 flex items-center justify-center transition-colors">
                                                                <Trash2 className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    ) : (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-12 text-center">
                                                <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-50 dark:bg-slate-800 mb-3 border border-gray-100 dark:border-slate-800">
                                                    <Files className="w-6 h-6 text-slate-300" />
                                                </div>
                                                <p className="text-sm font-semibold text-slate-600 dark:text-slate-400">No records found</p>
                                                <p className="text-xs font-medium text-slate-400 mt-1">Try adjusting your search criteria or upload a new document.</p>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        {records.links && records.links.length > 3 && (
                            <div className="px-6 py-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-150">
                                <Pagination links={records.links} />
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Upload Modal */}
            {uploadModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div className="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" onClick={() => setUploadModal(false)}></div>
                    
                    <div className="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-200 dark:border-slate-700">
                        <form onSubmit={handleUploadSubmit} encType="multipart/form-data">
                            <div className="p-8 text-left">
                                <div className="flex items-center justify-between mb-6">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner">
                                            <UploadCloud className="w-4 h-4" />
                                        </div>
                                        <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 tracking-tight">Upload Document</h3>
                                    </div>
                                    <button type="button" onClick={() => setUploadModal(false)} className="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 text-slate-400 hover:text-gray-600 dark:text-slate-400 flex items-center justify-center transition-colors">
                                        <X className="w-5 h-5" />
                                    </button>
                                </div>
                                <div className="space-y-5">
                                    <div ref={caseDropdownRef}>
                                        <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Link to Case (Optional)</label>
                                        {/* Searchable Case Dropdown */}
                                        <div className="relative">
                                            {/* Trigger button */}
                                            <button
                                                type="button"
                                                onClick={() => { setCaseDropdownOpen(o => !o); setCaseSearch(''); }}
                                                className="w-full flex items-center justify-between pl-4 pr-3 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-left focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                            >
                                                {data.case_id ? (() => {
                                                    const selected = cases.find(c => String(c.id) === String(data.case_id));
                                                    return selected ? (
                                                        <span className="flex items-center gap-2 text-slate-800 dark:text-slate-200 min-w-0">
                                                            <CheckCircle2 className="w-4 h-4 text-indigo-500 shrink-0" />
                                                            <span className="truncate">{selected.student?.full_name || `${selected.student?.first_name} ${selected.student?.last_name}`}</span>
                                                            <span className="text-xs text-indigo-500 font-bold shrink-0">Case #{selected.id}</span>
                                                            {selected.violation?.title && (
                                                                <span className="text-xs text-slate-400 font-medium shrink-0 hidden sm:inline">· {selected.violation.title}</span>
                                                            )}
                                                        </span>
                                                    ) : null;
                                                })() : (
                                                    <span className="text-slate-400">No Link — General Attachment</span>
                                                )}
                                                <ChevronDown className={`w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0 ${caseDropdownOpen ? 'rotate-180' : ''}`} />
                                            </button>

                                            {/* Dropdown panel */}
                                            {caseDropdownOpen && (
                                                <div className="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl overflow-hidden">
                                                    {/* Search input inside dropdown */}
                                                    <div className="p-2.5 border-b border-slate-100 dark:border-slate-800">
                                                        <div className="relative">
                                                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                                            <input
                                                                autoFocus
                                                                type="text"
                                                                value={caseSearch}
                                                                onChange={e => setCaseSearch(e.target.value)}
                                                                placeholder="Type student name to search..."
                                                                className="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all"
                                                            />
                                                        </div>
                                                    </div>
                                                    {/* Options list */}
                                                    <ul className="max-h-52 overflow-y-auto">
                                                        {/* No link option */}
                                                        <li>
                                                            <button
                                                                type="button"
                                                                onClick={() => { setData('case_id', ''); setCaseDropdownOpen(false); }}
                                                                className={`w-full text-left px-4 py-2.5 text-sm flex items-center gap-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors ${
                                                                    !data.case_id ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 font-bold' : 'text-slate-600 dark:text-slate-400'
                                                                }`}
                                                            >
                                                                <span className="w-5 h-5 rounded-full border-2 border-slate-300 shrink-0" />
                                                                No Link — General Attachment
                                                            </button>
                                                        </li>
                                                        {cases
                                                            .filter(c => {
                                                                const name = (c.student?.full_name || `${c.student?.first_name} ${c.student?.last_name}`).toLowerCase();
                                                                const violation = (c.violation?.title || '').toLowerCase();
                                                                const q = caseSearch.toLowerCase();
                                                                return !q || name.includes(q) || String(c.id).includes(q) || violation.includes(q);
                                                            })
                                                            .map(c => {
                                                                const name = c.student?.full_name || `${c.student?.first_name} ${c.student?.last_name}`;
                                                                const isSelected = String(data.case_id) === String(c.id);
                                                                const statusColor = {
                                                                    'Pending': 'bg-amber-100 text-amber-700',
                                                                    'Hearing Scheduled': 'bg-blue-100 text-blue-700',
                                                                    'Hearing': 'bg-indigo-100 text-indigo-700',
                                                                    'Closed': 'bg-emerald-100 text-emerald-700',
                                                                    'Dismissed': 'bg-slate-100 text-slate-500',
                                                                }[c.status] || 'bg-slate-100 text-slate-500';
                                                                return (
                                                                    <li key={c.id}>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => { setData('case_id', c.id); setCaseDropdownOpen(false); }}
                                                                            className={`w-full text-left px-4 py-3 text-sm flex items-center justify-between gap-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors ${
                                                                                isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''
                                                                            }`}
                                                                        >
                                                                            <div className="flex flex-col min-w-0 gap-1">
                                                                                <div className="flex items-center gap-2">
                                                                                    <span className="text-slate-800 dark:text-slate-200 font-semibold truncate">{name}</span>
                                                                                    <span className="text-[10px] text-indigo-500 font-bold shrink-0">#{c.id}</span>
                                                                                </div>
                                                                                <div className="flex items-center gap-2">
                                                                                    {c.violation?.title && (
                                                                                        <span className="text-xs text-slate-500 dark:text-slate-400 truncate">{c.violation.title}</span>
                                                                                    )}
                                                                                    <span className={`text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0 ${statusColor}`}>{c.status}</span>
                                                                                </div>
                                                                            </div>
                                                                            {isSelected && <CheckCircle2 className="w-4 h-4 text-indigo-500 shrink-0" />}
                                                                        </button>
                                                                    </li>
                                                                );
                                                            })
                                                        }
                                                        {cases.filter(c => {
                                                            const name = (c.student?.full_name || `${c.student?.first_name} ${c.student?.last_name}`).toLowerCase();
                                                            const violation = (c.violation?.title || '').toLowerCase();
                                                            const q = caseSearch.toLowerCase();
                                                            return !q || name.includes(q) || String(c.id).includes(q) || violation.includes(q);
                                                        }).length === 0 && (
                                                            <li className="px-4 py-6 text-center">
                                                                <UserSearch className="w-6 h-6 text-slate-300 mx-auto mb-1" />
                                                                <p className="text-sm text-slate-400 font-medium">No matching cases found</p>
                                                            </li>
                                                        )}
                                                    </ul>
                                                </div>
                                            )}
                                        </div>
                                        {errors.case_id && <p className="text-sm text-red-600 mt-1">{errors.case_id}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Select Document</label>
                                        <div 
                                            className={`relative border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-200 ease-in-out group ${
                                                data.file 
                                                    ? 'border-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/10' 
                                                    : 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/30 dark:bg-indigo-900/10 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-400 dark:hover:border-indigo-500'
                                            }`}
                                        >
                                            <input 
                                                type="file" 
                                                onChange={e => {
                                                    const file = e.target.files[0];
                                                    if (file) {
                                                        setData(prev => ({
                                                            ...prev, 
                                                            file: file, 
                                                            label: prev.label || file.name.split('.')[0]
                                                        }));
                                                    }
                                                }}
                                                className="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                                required={!data.file}
                                            />
                                            {data.file ? (
                                                <div className="flex flex-col items-center gap-2">
                                                    <div className="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-1 shadow-sm">
                                                        <FileText className="w-6 h-6" />
                                                    </div>
                                                    <p className="text-sm font-bold text-emerald-700 dark:text-emerald-400 truncate max-w-[250px]">{data.file.name}</p>
                                                    <p className="text-xs font-semibold text-emerald-600/70 dark:text-emerald-500/70">
                                                        {(data.file.size / 1024 / 1024).toFixed(2)} MB • Click or drag to replace
                                                    </p>
                                                </div>
                                            ) : (
                                                <div className="flex flex-col items-center gap-2">
                                                    <div className="w-12 h-12 rounded-full bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-1 shadow-sm group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                                        <UploadCloud className="w-6 h-6" />
                                                    </div>
                                                    <p className="text-sm font-bold text-slate-700 dark:text-slate-300 mt-2">Click to upload or drag and drop</p>
                                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">PDF, DOCX, XLSX, PNG, JPG (max. 10MB)</p>
                                                </div>
                                            )}
                                        </div>
                                        {errors.file && <p className="text-sm font-semibold text-rose-500 mt-2">{errors.file}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Display Name / Label</label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <Tag className="w-4.5 h-4.5" />
                                            </div>
                                            <input 
                                                type="text" 
                                                value={data.label}
                                                onChange={e => setData('label', e.target.value)}
                                                placeholder="e.g. Sworn Statement - June 2024" 
                                                className="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-gray-950 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                            />
                                        </div>
                                        {errors.label && <p className="text-sm text-red-600 mt-1">{errors.label}</p>}
                                    </div>
                                </div>
                            </div>
                            <div className="bg-gray-50 dark:bg-slate-800 px-8 py-5 border-t border-gray-150 flex flex-col sm:flex-row-reverse gap-3">
                                <button disabled={processing} type="submit" className="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-indigo-600 rounded-xl font-bold text-sm text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/25 transition-all duration-200">
                                    {processing ? 'Uploading...' : 'Upload'}
                                </button>
                                <button type="button" onClick={() => setUploadModal(false)} className="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl font-bold text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 dark:bg-slate-800 transition-all duration-200">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
