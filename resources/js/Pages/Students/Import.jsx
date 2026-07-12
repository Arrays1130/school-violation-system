import React, { useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { UploadCloud, Upload, FileSpreadsheet, Loader2 } from 'lucide-react';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { showErrorAlert } from '@/lib/sweetAlert';

export default function Import({ auth }) {
    const fileInputRef = useRef(null);
    const { data, setData, post, processing, errors, progress, reset } = useForm({
        file: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('students.import'), {
            forceFormData: true,
            preserveScroll: true,
            onError: (formErrors) => {
                if (formErrors.file) {
                    showErrorAlert(formErrors.file, 'Import Failed');
                }
                reset('file');
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
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

    const uploadPercent = progress?.percentage ?? 0;
    const isUploading = processing && uploadPercent < 100;
    const isProcessing = processing && uploadPercent >= 100;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Import Students</h2>}
        >
            <Head title="Import Students" />

            <div className="container mx-auto px-4 py-6">
                <div className="mb-6">
                    <Breadcrumbs items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'Students', href: route('students.index') },
                        { label: 'Import Students' },
                    ]} />
                </div>

                <div className="max-w-2xl mx-auto">
                    <div className="vt-content-card overflow-hidden">
                        <div className="p-6 border-b border-gray-200 dark:border-slate-700">
                            <h1 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Import Students</h1>
                            <p className="text-gray-600 dark:text-slate-400 mt-1">Upload a CSV or Excel file to bulk import students.</p>
                        </div>

                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-6">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Student File
                                    </label>
                                    <div
                                        onDrop={handleDrop}
                                        onDragOver={handleDragOver}
                                        onClick={() => !processing && fileInputRef.current?.click()}
                                        className={`flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 dark:border-slate-600 border-dashed rounded-lg bg-gray-50 dark:bg-slate-800 transition-colors ${
                                            processing ? 'cursor-wait opacity-70' : 'cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700'
                                        }`}
                                    >
                                        <div className="flex flex-col items-center justify-center pt-5 pb-6">
                                            {processing ? (
                                                <Loader2 className="w-10 h-10 mb-3 text-indigo-500 animate-spin" />
                                            ) : (
                                                <UploadCloud className="w-10 h-10 mb-3 text-slate-400" />
                                            )}
                                            <p className="mb-2 text-sm text-slate-600 dark:text-slate-400">
                                                <span className="font-semibold">Click to upload</span> or drag and drop
                                            </p>
                                            <p className="text-xs text-slate-500 dark:text-slate-500">XLSX, XLS or CSV (max 10 MB)</p>
                                        </div>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept=".xlsx,.xls,.csv,.txt"
                                            className="hidden"
                                            disabled={processing}
                                            onChange={handleFileChange}
                                        />
                                    </div>

                                    {data.file && (
                                        <div className="mt-3 flex items-center justify-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                                            <FileSpreadsheet className="w-4 h-4 text-indigo-500 shrink-0" />
                                            <span>Selected file:</span>
                                            <span className="font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[16rem]">
                                                {data.file.name}
                                            </span>
                                        </div>
                                    )}

                                    {errors.file && (
                                        <p className="text-rose-500 text-xs mt-2 font-semibold text-center">{errors.file}</p>
                                    )}

                                    {processing && (
                                        <div className="mt-4 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 px-4 py-3">
                                            <div className="flex items-center gap-3 text-sm text-indigo-800 dark:text-indigo-200">
                                                <Loader2 className="w-4 h-4 animate-spin shrink-0" />
                                                <span>
                                                    {isUploading && `Uploading file… ${uploadPercent}%`}
                                                    {isProcessing && 'File uploaded. Processing students on the server — please wait…'}
                                                    {!isUploading && !isProcessing && processing && 'Starting import…'}
                                                </span>
                                            </div>
                                            {progress && (
                                                <div className="mt-3 w-full bg-indigo-100 dark:bg-indigo-950 rounded-full h-2 overflow-hidden">
                                                    <div
                                                        className="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                        style={{ width: `${uploadPercent}%` }}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>

                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                                    <h3 className="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Instructions</h3>
                                    <ul className="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
                                        <li>The file must have a header row.</li>
                                        <li>
                                            <strong>Required columns:</strong>{' '}
                                            <strong>Email Address</strong> (or <code className="text-xs">email</code>),{' '}
                                            <strong>Department</strong>
                                        </li>
                                        <li>
                                            <strong>Include these columns:</strong>{' '}
                                            <strong>Section</strong>, <strong>Year Level</strong>{' '}
                                            (<code className="text-xs">1st Year</code>, <code className="text-xs">2nd Year</code>, etc. — <code className="text-xs">1</code> also works)
                                        </li>
                                        <li>
                                            <strong>Name (optional):</strong>{' '}
                                            <code className="text-xs">full_name</code> or First Name + Last Name.
                                            If omitted, the name is taken from the email address.
                                        </li>
                                        <li>
                                            <strong>Optional:</strong>{' '}
                                            <code className="text-xs">academic_year</code>{' '}
                                            (defaults to current school year),{' '}
                                            <code className="text-xs">guardian_name</code>,{' '}
                                            <code className="text-xs">guardian_email</code>,{' '}
                                            <code className="text-xs">guardian_phone</code>
                                        </li>
                                    </ul>
                                    <p className="mt-3 text-xs text-blue-600 dark:text-blue-400 font-medium">
                                        Example headers: <code>Department, Email Address, Section, Year Level</code>
                                    </p>
                                </div>

                                <div className="flex items-center justify-end gap-3">
                                    <Link
                                        href={route('students.index')}
                                        className="px-5 py-2.5 text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg font-medium transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={processing || !data.file}
                                        className="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {processing ? (
                                            <>
                                                <Loader2 className="w-4 h-4 animate-spin" />
                                                Importing…
                                            </>
                                        ) : (
                                            <>
                                                <Upload className="w-4 h-4" />
                                                Import Students
                                            </>
                                        )}
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
