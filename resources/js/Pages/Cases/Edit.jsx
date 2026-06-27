import React from 'react';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { FilePlus, ArrowLeft, CheckCircle2, ShieldAlert, Trash2 } from 'lucide-react';

export default function Edit({ auth, caseRecord }) {
    const { data, setData, put, processing, errors } = useForm({
        description: caseRecord.description || '',
        witness: caseRecord.witness || '',
        occurred_at: caseRecord.occurred_at ? new Date(caseRecord.occurred_at).toISOString().slice(0, 16) : '',
        status: caseRecord.status || 'Pending',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('cases.update', caseRecord.id));
    };

    const handleDelete = (e) => {
        e.preventDefault();
        Swal.fire({
            title: 'Move to Trash Bin?',
            text: "Are you sure you want to move this violation record to the trash?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, move to trash'
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('cases.destroy', caseRecord.id));
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Edit Violation Case" />

            <div className="py-8">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Header Banner */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-rose-950 to-slate-900 p-8 mb-8 shadow-2xl border border-white/5">
                        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_0%,rgba(244,63,94,0.25),transparent_65%)]"></div>
                        <div className="relative flex items-center gap-4">
                            <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
                                <ShieldAlert className="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70">
                                    Edit Case #{caseRecord.id}
                                </h1>
                                <p className="text-rose-200/70 text-sm mt-1">Update incident details and status.</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-900 overflow-hidden shadow-xl sm:rounded-3xl border border-slate-100 dark:border-slate-800 p-8">
                        <form onSubmit={submit} className="space-y-6">
                            
                            <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-4">
                                <FilePlus className="w-5 h-5 text-rose-500" /> Incident Details
                            </h3>

                            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 mb-6">
                                <div className="flex-1">
                                    <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offender</p>
                                    <p className="font-bold text-slate-800 dark:text-slate-200">{caseRecord.student?.full_name || 'None'}</p>
                                </div>
                                <div className="hidden sm:block w-px bg-slate-200"></div>
                                <div className="flex-1">
                                    <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offense</p>
                                    <p className="font-bold text-slate-800 dark:text-slate-200">{caseRecord.violation?.title || 'None'}</p>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Date & Time of Incident</label>
                                    <input 
                                        type="datetime-local" 
                                        className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800"
                                        value={data.occurred_at}
                                        onChange={e => setData('occurred_at', e.target.value)}
                                    />
                                    {errors.occurred_at && <p className="text-xs text-red-600 mt-1">{errors.occurred_at}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Witness (Optional)</label>
                                    <input 
                                        type="text" 
                                        placeholder="Name of witness"
                                        className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800"
                                        value={data.witness}
                                        onChange={e => setData('witness', e.target.value)}
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                    <select 
                                        className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800"
                                        value={data.status}
                                        onChange={e => setData('status', e.target.value)}
                                    >
                                        <option value="Pending">Pending</option>
                                        <option value="Hearing Scheduled">Hearing Scheduled</option>
                                        <option value="Hearing">Hearing</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                    {errors.status && <p className="text-xs text-red-600 mt-1">{errors.status}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Incident Description</label>
                                <textarea 
                                    rows="4"
                                    placeholder="Provide details about the incident..."
                                    className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 resize-none"
                                    value={data.description}
                                    onChange={e => setData('description', e.target.value)}
                                ></textarea>
                                {errors.description && <p className="text-xs text-red-600 mt-1">{errors.description}</p>}
                            </div>

                            <div className="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800 mt-8">
                                <div className="flex gap-3">
                                    <Link
                                        href={route('cases.show', caseRecord.id)}
                                        className="px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200"
                                    >
                                        <ArrowLeft className="w-4 h-4" /> Cancel
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={handleDelete}
                                        className="px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 border border-rose-200"
                                    >
                                        <Trash2 className="w-4 h-4" /> Move to Trash
                                    </button>
                                </div>
                                
                                <button
                                    type="submit"
                                    disabled={processing || !data.description || !data.occurred_at}
                                    className={`px-8 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg ${(processing || !data.description || !data.occurred_at) ? 'bg-slate-200 text-slate-400 shadow-none' : 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-0.5 shadow-rose-600/30'}`}
                                >
                                    {processing ? 'Saving...' : 'Save Changes'}
                                    {!processing && <CheckCircle2 className="w-4 h-4" />}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
