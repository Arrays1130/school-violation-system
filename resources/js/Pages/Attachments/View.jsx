import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { ArrowLeft, Eye, File, Download, FileX } from 'lucide-react';

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

export default function View({ auth, attachment }) {
    const ext = (attachment.file_ext || '').toLowerCase();
    const isPdf = ext === 'pdf';
    const isImage = IMAGE_EXTENSIONS.includes(ext);
    const displayName = attachment.label || attachment.file_name;
    const downloadUrl = attachment.signed_download_url || attachment.download_url;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={displayName} />

            <div className="max-w-5xl mx-auto py-8 px-4">
                <div className="vt-page-hero mb-8">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl" />

                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="flex items-center gap-5">
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="w-10 h-10 rounded-xl bg-slate-900/10 border border-slate-600/80 flex items-center justify-center text-white/80 hover:text-white hover:bg-slate-700/80 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5 shrink-0"
                            >
                                <ArrowLeft className="w-4 h-4" />
                            </button>
                            <div>
                                <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900/10 border border-slate-600/80 text-white/80 text-[10px] font-bold uppercase tracking-wider mb-2 backdrop-blur-md">
                                    <Eye className="w-3.5 h-3.5 text-indigo-400" />
                                    Document Viewer
                                </div>
                                <h2 className="text-2xl font-bold text-white tracking-tight leading-tight max-w-xl">
                                    {displayName}
                                </h2>
                                <div className="flex items-center gap-4 text-xs text-indigo-200/70 mt-2 font-medium">
                                    <span className="flex items-center gap-1.5">
                                        <File className="w-4 h-4 text-indigo-400" />
                                        Format: .{ext}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-3 shrink-0">
                            <a
                                href={downloadUrl}
                                className="inline-flex items-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-sm shadow-indigo-500/20 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5"
                            >
                                <Download className="w-4 h-4 mr-1.5" />
                                Download Document
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    className="vt-content-card overflow-hidden relative border-l-4 border-indigo-600"
                    style={{ minHeight: '600px' }}
                >
                    {isPdf && (
                        <iframe
                            src={downloadUrl}
                            title={displayName}
                            className="w-full"
                            style={{ height: '80vh', border: 'none' }}
                        />
                    )}

                    {isImage && (
                        <div className="p-8 flex items-center justify-center bg-slate-50">
                            <img
                                src={downloadUrl}
                                alt={displayName}
                                className="max-w-full rounded-2xl shadow-sm border border-gray-200/85 dark:border-slate-700"
                            />
                        </div>
                    )}

                    {!isPdf && !isImage && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center p-8 text-center bg-slate-900">
                            <div className="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                                <FileX className="w-7 h-7 text-indigo-500" />
                            </div>
                            <h3 className="text-base font-bold text-slate-200 mb-1">
                                Preview unavailable
                            </h3>
                            <p className="text-sm text-slate-400 mb-6 font-medium">
                                Preview is not available for .{ext} files. Please download the file instead.
                            </p>
                            <a
                                href={downloadUrl}
                                className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl transition-all active:scale-95 shadow-sm"
                            >
                                Download File Instead
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
