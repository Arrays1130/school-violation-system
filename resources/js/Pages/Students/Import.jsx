import React, { useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { UploadCloud, Upload, ChevronRight, FileSpreadsheet } from 'lucide-react';

export default function Import({ auth }) {
    const fileInputRef = useRef(null);
    const { data, setData, post, processing, errors, progress } = useForm({
        file: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('students.import'), { forceFormData: true });
    };

    const handleFileChange = (e) => {
        const file = e.target.files?.[0] || null;
        setData('file', file);
    };

    const handleDrop = (e) => {
        e.preventDefault();
        const file = e.dataTransfer.files?.[0] || null;
        if (file) {
            setData('file', file);
        }
    };

    const handleDragOver = (e) => {
        e.preventDefault();
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Import Students</h2>}
        >
            <Head title="Import Students" />

            <div className="container mx-auto px-4 py-6">
                <div className="flex items-center gap-2 text-sm text-slate-400 mb-6">
                    <Link href={route('dashboard')} className="hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                        Dashboard
                    </Link>
                    <ChevronRight className="w-4 h-4" />
                    <Link href={route('students.index')} className="hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                        Students
                    </Link>
                    <ChevronRight className="w-4 h-4" />
                    <span className="text-slate-100 font-medium">Import Students</span>
                </div>

                <div className="max-w-2xl mx-auto">
                    <div className="vt-content-card overflow-hidden">
                        <div className="p-6 border-b border-gray-200 dark:border-slate-700">
                            <h1 className="text-2xl font-bold text-slate-100">Import Students</h1>
                            <p className="text-gray-600 dark:text-slate-400 mt-1">Upload a CSV or Excel file to bulk import students.</p>
                        </div>

                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-6">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Excel File
                                    </label>
                                    <div
                                        onDrop={handleDrop}
                                        onDragOver={handleDragOver}
                                        onClick={() => fileInputRef.current?.click()}
                                        className="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 dark:border-slate-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <div className="flex flex-col items-center justify-center pt-5 pb-6">
                                            <UploadCloud className="w-10 h-10 mb-3 text-slate-400" />
                                            <p className="mb-2 text-sm text-slate-400">
                                                <span className="font-semibold">Click to upload</span> or drag and drop
                                            </p>
                                            <p className="text-xs text-slate-400">XLSX, XLS or CSV</p>
                                        </div>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept=".xlsx,.xls,.csv"
                                            className="hidden"
                                            onChange={handleFileChange}
                                        />
                                    </div>
                                    {data.file && (
                                        <div className="mt-3 flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-slate-400">
                                            <FileSpreadsheet className="w-4 h-4 text-indigo-500" />
                                            Selected file: <span className="font-semibold text-slate-200">{data.file.name}</span>
                                        </div>
                                    )}
                                    {errors.file && <p className="text-rose-500 text-xs mt-2 font-semibold text-center">{errors.file}</p>}
                                    {progress && (
                                        <div className="mt-3 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                            <div
                                                className="bg-indigo-600 h-2 rounded-full transition-all"
                                                style={{ width: `${progress.percentage}%` }}
                                            />
                                        </div>
                                    )}
                                </div>

                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                                    <h3 className="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Instructions</h3>
                                    <ul className="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
                                        <li>The file must have a header row.</li>
                                        <li>Required columns: <strong>full_name, email, department</strong></li>
                                        <li>Optional columns: <strong>year_level, section, guardian_name, guardian_email, guardian_phone</strong></li>
                                    </ul>
                                </div>

                                <div className="flex items-center justify-end gap-3">
                                    <Link
                                        href={route('students.index')}
                                        className="px-5 py-2.5 text-gray-700 dark:text-slate-300 bg-slate-800 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg font-medium transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={processing || !data.file}
                                        className="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all shadow-sm disabled:opacity-50"
                                    >
                                        <Upload className="w-4 h-4" />
                                        Import Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
