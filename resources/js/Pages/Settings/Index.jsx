import React from 'react';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Sliders, AlertTriangle, Archive } from 'lucide-react';

export default function Index({ auth, currentAcademicYear, academicYears }) {
    const { data, setData, post, processing, errors } = useForm({
        current_academic_year: currentAcademicYear || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('settings.update'));
    };

    const confirmArchive = () => {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to archive ALL Closed cases. This action will remove them from your active dashboard.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1e293b',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, archive them!',
        }).then((result) => {
            if (result.isConfirmed) {
                router.post(route('settings.archive-cases'));
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="System Settings" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="mb-8">
                    <h1 className="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <Sliders className="w-6 h-6 text-indigo-600" />
                        System Settings
                    </h1>
                    <p className="text-slate-500 mt-1 text-sm">
                        Manage global system configurations and defaults.
                    </p>
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div className="p-6">
                        <form onSubmit={handleSubmit}>
                            <div className="max-w-xl">
                                <div className="mb-6">
                                    <label htmlFor="current_academic_year" className="block text-sm font-bold text-slate-700 mb-2">
                                        Current Academic Year
                                    </label>
                                    <p className="text-xs text-slate-500 mb-3">
                                        This sets the default academic year for new student registrations. Existing
                                        students will remain in their originally assigned academic year to preserve
                                        historical accuracy.
                                    </p>

                                    <select
                                        id="current_academic_year"
                                        value={data.current_academic_year}
                                        onChange={(e) => setData('current_academic_year', e.target.value)}
                                        className="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    >
                                        {academicYears.map((year) => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                    {errors.current_academic_year && (
                                        <p className="mt-1 text-sm text-red-600">{errors.current_academic_year}</p>
                                    )}
                                </div>

                                <div className="pt-4 border-t border-slate-100 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 py-2 px-4 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                                    >
                                        Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div className="p-6">
                        <h2 className="text-lg font-bold text-slate-900 mb-2">End-of-Year Processing</h2>
                        <p className="text-sm text-slate-500 mb-6">
                            Archive all currently <strong>Closed</strong> cases to clear out your dashboard for the new
                            academic year. Archived cases will no longer appear in the main dashboard statistics but can
                            always be found in the <strong>Record Retrieval</strong> page.
                        </p>

                        <div className="max-w-xl">
                            <div className="p-4 bg-amber-50 rounded-xl border border-amber-200 mb-6 flex items-start gap-3">
                                <AlertTriangle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                                <div>
                                    <h3 className="text-sm font-bold text-amber-800">Warning</h3>
                                    <p className="text-xs text-amber-700 mt-1">
                                        Make sure you have officially closed all cases for the previous academic year
                                        before archiving. Only cases with a &quot;Closed&quot; status will be archived.
                                    </p>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={confirmArchive}
                                    className="inline-flex items-center gap-2 justify-center rounded-xl border border-transparent bg-slate-800 py-2 px-4 text-sm font-bold text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors"
                                >
                                    <Archive className="w-4 h-4" />
                                    Archive All Closed Cases
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
