import React, { useState, useMemo, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import {
    UserSearch, AlertCircle, FileWarning, ArrowRight, ArrowLeft,
    CheckCircle2, FilePlus, ShieldAlert, Users, X,
} from 'lucide-react';
import Breadcrumbs from '@/Components/Breadcrumbs';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

export default function Create({ auth, student, violations, students }) {
    const initialStudentIds = student?.id ? [student.id] : [];
    const [step, setStep] = useState(student ? 2 : 1);
    const [searchQuery, setSearchQuery] = useState('');
    const [violationQuery, setViolationQuery] = useState('');

    const [sanctionInfo, setSanctionInfo] = useState(null);
    const [isLoadingSanction, setIsLoadingSanction] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        student_ids: initialStudentIds,
        violation_id: '',
        description: '',
        witness: '',
        occurred_at: new Date().toISOString().slice(0, 16),
        attachments: [],
    });

    const selectedStudents = useMemo(
        () => students.filter((s) => data.student_ids.includes(s.id) || data.student_ids.includes(String(s.id))),
        [students, data.student_ids],
    );
    const selectedViolation = useMemo(
        () => violations.find((v) => v.id == data.violation_id),
        [violations, data.violation_id],
    );

    const filteredStudents = useMemo(() => {
        if (!searchQuery) return students;
        const q = searchQuery.toLowerCase();
        return students.filter((s) =>
            s.full_name?.toLowerCase().includes(q) ||
            s.department?.toLowerCase().includes(q) ||
            s.student_number?.toLowerCase().includes(q),
        );
    }, [students, searchQuery]);

    const filteredViolations = useMemo(() => {
        if (!violationQuery) return violations;
        const q = violationQuery.toLowerCase();
        return violations.filter((v) =>
            v.title?.toLowerCase().includes(q) ||
            v.code?.toLowerCase().includes(q),
        );
    }, [violations, violationQuery]);

    const isStudentSelected = (id) =>
        data.student_ids.some((selectedId) => String(selectedId) === String(id));

    const toggleStudent = (id) => {
        if (isStudentSelected(id)) {
            setData(
                'student_ids',
                data.student_ids.filter((selectedId) => String(selectedId) !== String(id)),
            );
            return;
        }
        setData('student_ids', [...data.student_ids, id]);
    };

    const removeStudent = (id) => {
        setData(
            'student_ids',
            data.student_ids.filter((selectedId) => String(selectedId) !== String(id)),
        );
    };

    useEffect(() => {
        if (data.violation_id && data.student_ids.length === 1) {
            setIsLoadingSanction(true);
            fetch(route('api.get-sanction-info', {
                violation_id: data.violation_id,
                student_id: data.student_ids[0],
            }))
                .then((res) => res.json())
                .then((info) => {
                    setSanctionInfo(info);
                    setIsLoadingSanction(false);
                })
                .catch(() => {
                    setSanctionInfo(null);
                    setIsLoadingSanction(false);
                });
        } else {
            setSanctionInfo(null);
            setIsLoadingSanction(false);
        }
    }, [data.violation_id, data.student_ids]);

    const handleNext = () => setStep((s) => Math.min(s + 1, 3));
    const handleBack = () => setStep((s) => Math.max(s - 1, 1));

    const submit = (e) => {
        e.preventDefault();
        post(route('cases.store'), {
            forceFormData: true,
            onError: () => {
                requestAnimationFrame(() => {
                    const firstError = document.querySelector('[data-error="true"], .form-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            },
        });
    };

    const renderStepIndicators = () => (
        <div className="flex items-center justify-center mb-12">
            <div className="flex items-center w-full max-w-2xl">
                {[
                    { num: 1, title: 'Student', icon: UserSearch },
                    { num: 2, title: 'Violation', icon: AlertCircle },
                    { num: 3, title: 'Details', icon: FilePlus },
                ].map((s, idx) => (
                    <React.Fragment key={s.num}>
                        <div className="relative flex flex-col items-center">
                            <div className={`relative z-10 w-10 h-10 rounded-full flex items-center justify-center border-2 bg-white dark:bg-slate-900 transition-all duration-300 ${step >= s.num ? 'border-rose-600 shadow-[0_0_15px_rgba(244,63,94,0.3)]' : 'border-slate-300'}`}>
                                <s.icon className={`w-5 h-5 ${step >= s.num ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400'}`} />
                            </div>
                            <span className={`absolute top-12 text-[11px] font-bold uppercase whitespace-nowrap transition-colors duration-300 ${step >= s.num ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400'}`}>
                                {s.title}
                            </span>
                        </div>
                        {idx < 2 && (
                            <div className={`flex-1 h-0.5 mx-4 transition-all duration-500 ${step > s.num ? 'bg-rose-600' : 'bg-slate-200'}`} />
                        )}
                    </React.Fragment>
                ))}
            </div>
        </div>
    );

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Log Violation" />

            <PageMotion className="py-8">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <MotionItem>
                        <Breadcrumbs
                            className="mb-6"
                            items={[
                                { label: 'Dashboard', href: route('dashboard') },
                                { label: 'Violation Cases', href: route('cases.index') },
                                { label: 'Record Violation' },
                            ]}
                        />
                    </MotionItem>

                    <MotionItem className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-rose-950 to-slate-900 p-8 mb-8 shadow-2xl border border-white/5">
                        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_0%,rgba(244,63,94,0.25),transparent_65%)]" />
                        <div className="relative flex items-center gap-4">
                            <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
                                <ShieldAlert className="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70">
                                    Log a Violation
                                </h1>
                                <p className="text-rose-200/70 text-sm mt-1">
                                    Select one or more students — each gets their own case code.
                                </p>
                            </div>
                        </div>
                    </MotionItem>

                    <MotionItem className="bg-white dark:bg-slate-900 overflow-hidden shadow-xl sm:rounded-3xl border border-slate-100 dark:border-slate-800 p-8">
                        {renderStepIndicators()}

                        <form onSubmit={submit} className="space-y-6">
                            {step === 1 && (
                                <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-500">
                                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                            <UserSearch className="w-5 h-5 text-rose-500" /> Select Students
                                        </h3>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 px-3 py-1.5 rounded-full">
                                            <Users className="w-3.5 h-3.5" />
                                            {data.student_ids.length} selected
                                        </span>
                                    </div>

                                    <p className="text-sm text-slate-500 dark:text-slate-400">
                                        Tap students to select multiple (2, 3, 5…). Each will receive a separate violation case.
                                    </p>

                                    {selectedStudents.length > 0 && (
                                        <div className="flex flex-wrap gap-2 p-3 rounded-xl bg-rose-50/70 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-900/30">
                                            {selectedStudents.map((s) => (
                                                <button
                                                    key={s.id}
                                                    type="button"
                                                    onClick={() => removeStudent(s.id)}
                                                    className="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-white dark:bg-slate-800 border border-rose-200 dark:border-rose-800 text-xs font-bold text-slate-800 dark:text-slate-200 hover:border-rose-400 transition-colors"
                                                    title="Remove"
                                                >
                                                    {s.full_name}
                                                    <X className="w-3.5 h-3.5 text-rose-500" />
                                                </button>
                                            ))}
                                        </div>
                                    )}

                                    <div className="relative">
                                        <input
                                            type="text"
                                            placeholder="Search by name, ID, or department..."
                                            className="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 transition-all"
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                        />
                                        <UserSearch className="w-5 h-5 text-slate-400 absolute left-3 top-3.5" />
                                    </div>

                                    {(errors.student_ids || errors['student_ids.0']) && (
                                        <p data-error="true" className="text-sm text-red-600 font-medium">
                                            {errors.student_ids || errors['student_ids.0']}
                                        </p>
                                    )}

                                    <div className="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                        {filteredStudents.map((s) => {
                                            const selected = isStudentSelected(s.id);
                                            return (
                                                <button
                                                    type="button"
                                                    key={s.id}
                                                    onClick={() => toggleStudent(s.id)}
                                                    className={`w-full text-left p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-4 ${selected ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'}`}
                                                >
                                                    <div className={`w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 ${selected ? 'border-rose-500 bg-rose-500' : 'border-slate-300 dark:border-slate-600'}`}>
                                                        {selected && <CheckCircle2 className="w-3.5 h-3.5 text-white" />}
                                                    </div>
                                                    <div className="w-12 h-12 rounded-full bg-slate-200 overflow-hidden flex-shrink-0">
                                                        {s.avatar ? (
                                                            <img src={`/storage/${s.avatar}`} alt="" className="w-full h-full object-cover" />
                                                        ) : (
                                                            <div className="w-full h-full flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold bg-gradient-to-br from-slate-100 to-slate-200">
                                                                {s.full_name?.charAt(0) || '?'}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div className="flex-1 min-w-0">
                                                        <h4 className="font-bold text-slate-800 dark:text-slate-200">{s.full_name}</h4>
                                                        <p className="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                            {s.student_number ? `${s.student_number} • ` : ''}{s.department}
                                                        </p>
                                                    </div>
                                                    {selected && <CheckCircle2 className="w-6 h-6 text-rose-500 flex-shrink-0" />}
                                                </button>
                                            );
                                        })}
                                        {filteredStudents.length === 0 && (
                                            <div className="p-8 text-center text-slate-500 dark:text-slate-400">No students found.</div>
                                        )}
                                    </div>
                                </div>
                            )}

                            {step === 2 && (
                                <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-500">
                                    <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        <FileWarning className="w-5 h-5 text-rose-500" /> Select Violation
                                    </h3>

                                    <div className="relative">
                                        <input
                                            type="text"
                                            placeholder="Search violation code or title..."
                                            className="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 transition-all"
                                            value={violationQuery}
                                            onChange={(e) => setViolationQuery(e.target.value)}
                                        />
                                        <AlertCircle className="w-5 h-5 text-slate-400 absolute left-3 top-3.5" />
                                    </div>

                                    {errors.violation_id && <p data-error="true" className="text-sm text-red-600 font-medium">{errors.violation_id}</p>}

                                    <div className="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                        {filteredViolations.map((v) => (
                                            <button
                                                type="button"
                                                key={v.id}
                                                onClick={() => setData('violation_id', v.id)}
                                                className={`w-full text-left p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3 ${data.violation_id == v.id ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'}`}
                                            >
                                                <div className={`mt-1 w-2.5 h-2.5 rounded-full flex-shrink-0 ${
                                                    v.severity === 'Minor' ? 'bg-blue-500' : 'bg-rose-600'
                                                }`}
                                                />
                                                <div className="flex-1">
                                                    <div className="flex items-center justify-between gap-2">
                                                        <h4 className="font-bold text-slate-800 dark:text-slate-200 text-sm">{v.code} - {v.title}</h4>
                                                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100 px-2 py-1 rounded-md">{v.severity}</span>
                                                    </div>
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {step === 3 && (
                                <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                                    <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-4">
                                        <FilePlus className="w-5 h-5 text-rose-500" /> Incident Details & Evidence
                                    </h3>

                                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 mb-6">
                                        <div className="flex-1 min-w-0">
                                            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                Offender{selectedStudents.length > 1 ? 's' : ''} ({selectedStudents.length})
                                            </p>
                                            <p className="font-bold text-slate-800 dark:text-slate-200">
                                                {selectedStudents.length === 0
                                                    ? 'None'
                                                    : selectedStudents.length <= 3
                                                        ? selectedStudents.map((s) => s.full_name).join(', ')
                                                        : `${selectedStudents.slice(0, 2).map((s) => s.full_name).join(', ')} +${selectedStudents.length - 2} more`}
                                            </p>
                                        </div>
                                        <div className="hidden sm:block w-px bg-slate-200" />
                                        <div className="flex-1">
                                            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offense</p>
                                            <p className="font-bold text-slate-800 dark:text-slate-200">{selectedViolation?.title || 'None'}</p>
                                        </div>
                                    </div>

                                    {selectedStudents.length > 1 && (
                                        <div className="p-4 rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 flex items-start gap-3">
                                            <Users className="w-5 h-5 text-sky-600 dark:text-sky-400 flex-shrink-0 mt-0.5" />
                                            <div>
                                                <p className="text-xs font-bold text-sky-800 dark:text-sky-300 uppercase tracking-wider mb-1">
                                                    Separate cases will be created
                                                </p>
                                                <p className="text-sky-900 dark:text-sky-200 font-medium text-sm">
                                                    Each of the {selectedStudents.length} students gets their own case code and sanction based on their offense history.
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {sanctionInfo && selectedStudents.length === 1 && (
                                        <div className="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 shadow-sm flex items-start gap-3">
                                            <AlertCircle className="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" />
                                            <div>
                                                <p className="text-xs font-bold text-rose-800 uppercase tracking-wider mb-1">Expected Sanction (Offense #{sanctionInfo.offense_level || sanctionInfo.current_offense_level})</p>
                                                <p className="text-rose-900 font-medium text-sm">{sanctionInfo.sanction}</p>
                                            </div>
                                        </div>
                                    )}

                                    {(selectedViolation?.severity === 'Major' || sanctionInfo?.auto_endorse) && (
                                        <div className="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-start gap-3">
                                            <ShieldAlert className="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                                            <div>
                                                <p className="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider mb-1">
                                                    Auto-endorsed to Grievance
                                                </p>
                                                <p className="text-amber-900 dark:text-amber-200 font-medium text-sm">
                                                    Major offenses are endorsed to the Grievance Committee as soon as the case is recorded — even on the first offense.
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Date & Time of Incident</label>
                                            <input
                                                type="datetime-local"
                                                required
                                                className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800"
                                                value={data.occurred_at}
                                                onChange={(e) => setData('occurred_at', e.target.value)}
                                            />
                                            {errors.occurred_at && <p data-error="true" className="text-xs text-red-600 mt-1">{errors.occurred_at}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Witness (Optional)</label>
                                            <input
                                                type="text"
                                                placeholder="Person who witnessed the incident"
                                                className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800"
                                                value={data.witness}
                                                onChange={(e) => setData('witness', e.target.value)}
                                            />
                                            <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Who saw the incident — not the staff who filed this report.</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Incident Description</label>
                                        <textarea
                                            rows="4"
                                            required
                                            placeholder="Provide details about the incident..."
                                            className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 resize-none"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                        />
                                        {errors.description && <p data-error="true" className="text-xs text-red-600 mt-1">{errors.description}</p>}
                                    </div>
                                </div>
                            )}

                            <div className="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800 mt-8">
                                {step === 1 ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200"
                                    >
                                        <ArrowLeft className="w-4 h-4" /> Cancel
                                    </Link>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={handleBack}
                                        className="px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all text-slate-600 dark:text-slate-400 bg-slate-100 hover:bg-slate-200"
                                    >
                                        <ArrowLeft className="w-4 h-4" /> Back
                                    </button>
                                )}

                                {step < 3 ? (
                                    <button
                                        type="button"
                                        onClick={handleNext}
                                        disabled={(step === 1 && data.student_ids.length === 0) || (step === 2 && !data.violation_id)}
                                        className={`px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg ${((step === 1 && data.student_ids.length === 0) || (step === 2 && !data.violation_id)) ? 'bg-slate-200 text-slate-400 shadow-none' : 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-0.5 shadow-rose-600/30'}`}
                                    >
                                        Next <ArrowRight className="w-4 h-4" />
                                    </button>
                                ) : (
                                    <button
                                        type="submit"
                                        disabled={processing || isLoadingSanction}
                                        className={`px-8 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg ${(processing || isLoadingSanction) ? 'bg-slate-200 text-slate-400 shadow-none' : 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-0.5 shadow-rose-600/30'}`}
                                    >
                                        {processing
                                            ? 'Submitting...'
                                            : data.student_ids.length > 1
                                                ? `Submit ${data.student_ids.length} Cases`
                                                : 'Submit Violation'}
                                        {!processing && <CheckCircle2 className="w-4 h-4" />}
                                    </button>
                                )}
                            </div>
                        </form>
                    </MotionItem>
                </div>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
