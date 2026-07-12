import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Sliders, AlertTriangle, Archive, Building2 } from 'lucide-react';
import ConfirmDialog from '@/Components/ConfirmDialog';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

export default function Index({
    auth,
    currentAcademicYear,
    schoolName,
    closedCasesToArchive,
    canArchive,
    academicYears,
}) {
    const { data, setData, post, processing, errors } = useForm({
        current_academic_year: currentAcademicYear || '',
        school_name: schoolName || '',
    });
    const [showArchiveConfirm, setShowArchiveConfirm] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('settings.update'));
    };

    const confirmArchive = () => {
        router.post(route('settings.archive-cases'));
        setShowArchiveConfirm(false);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="System Settings" />

            <ConfirmDialog
                open={showArchiveConfirm}
                onClose={() => setShowArchiveConfirm(false)}
                onConfirm={confirmArchive}
                title={`Archive ${closedCasesToArchive} closed case${closedCasesToArchive === 1 ? '' : 's'}?`}
                description={`This will remove ${closedCasesToArchive} closed case${closedCasesToArchive === 1 ? '' : 's'} from your active dashboard. You can still find them anytime in Record Retrieval.`}
                confirmLabel="Yes, archive them"
                destructive
            />

            <PageMotion className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <MotionItem>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <Sliders className="w-6 h-6 text-indigo-600" />
                        System Settings
                    </h1>
                    <p className="text-slate-500 dark:text-slate-400 mt-1 text-sm">
                        Manage global system configurations and defaults.
                    </p>
                </MotionItem>

                <MotionItem className="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div className="p-6">
                        <form onSubmit={handleSubmit} className="max-w-xl space-y-6">
                            <div>
                                <label htmlFor="school_name" className="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-2">
                                    <Building2 className="w-4 h-4" /> School Name
                                </label>
                                <input
                                    id="school_name"
                                    type="text"
                                    value={data.school_name}
                                    onChange={(e) => setData('school_name', e.target.value)}
                                    className="form-input"
                                    required
                                />
                                {errors.school_name && <p className="mt-1 text-sm text-red-600">{errors.school_name}</p>}
                            </div>

                            <div>
                                <label htmlFor="current_academic_year" className="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Current Academic Year
                                </label>
                                <p className="text-xs text-slate-500 dark:text-slate-400 mb-3">
                                    Default academic year for new student registrations and imports. Existing students keep their assigned year.
                                </p>
                                <select
                                    id="current_academic_year"
                                    value={data.current_academic_year}
                                    onChange={(e) => setData('current_academic_year', e.target.value)}
                                    className="form-input"
                                    required
                                >
                                    {academicYears.map((year) => (
                                        <option key={year} value={year}>{year}</option>
                                    ))}
                                </select>
                                {errors.current_academic_year && <p className="mt-1 text-sm text-red-600">{errors.current_academic_year}</p>}
                            </div>

                            <div className="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 py-2.5 px-5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors disabled:opacity-50"
                                >
                                    {processing ? 'Saving...' : 'Save Settings'}
                                </button>
                            </div>
                        </form>
                    </div>
                </MotionItem>

                {canArchive && (
                    <MotionItem className="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div className="p-6">
                            <h2 className="text-lg font-bold text-slate-900 dark:text-white mb-2">End-of-Year Processing</h2>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mb-6">
                                Archive all currently <strong>Closed</strong> cases to clear your dashboard for the new academic year.
                                Archived cases remain available in <strong>Record Retrieval</strong>.
                            </p>

                            <div className="max-w-xl">
                                <div className="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 mb-4 flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-bold text-slate-800 dark:text-slate-200">Cases ready to archive</p>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Closed and not yet archived</p>
                                    </div>
                                    <span className="text-2xl font-black text-indigo-600 dark:text-indigo-400">{closedCasesToArchive}</span>
                                </div>

                                <div className="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 mb-6 flex items-start gap-3">
                                    <AlertTriangle className="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <h3 className="text-sm font-bold text-amber-800 dark:text-amber-300">Warning</h3>
                                        <p className="text-xs text-amber-700 dark:text-amber-400 mt-1">
                                            Only cases with a &quot;Closed&quot; status will be archived. Super admin access required.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setShowArchiveConfirm(true)}
                                    disabled={closedCasesToArchive === 0}
                                    className="inline-flex items-center gap-2 justify-center rounded-xl border border-transparent bg-slate-800 py-2 px-4 text-sm font-bold text-white shadow-sm hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors disabled:opacity-40"
                                >
                                    <Archive className="w-4 h-4" />
                                    Archive All Closed Cases
                                </button>
                            </div>
                        </div>
                    </MotionItem>
                )}
            </PageMotion>
        </AuthenticatedLayout>
    );
}
