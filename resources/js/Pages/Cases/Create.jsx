import React, { useState, useMemo, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router, Link } from '@inertiajs/react';
import { 
    UserSearch, AlertCircle, FileWarning, ArrowRight, ArrowLeft, 
    CheckCircle2, FilePlus, ShieldAlert, UploadCloud, X
} from 'lucide-react';

export default function Create({ auth, student, violations, students }) {
    const [step, setStep] = useState(student ? 2 : 1);
    const [searchQuery, setSearchQuery] = useState('');
    const [violationQuery, setViolationQuery] = useState('');
    
    // Dynamic Sanction State
    const [sanctionInfo, setSanctionInfo] = useState(null);
    const [isLoadingSanction, setIsLoadingSanction] = useState(false);

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        student_id: student?.id || '',
        violation_id: '',
        description: '',
        witness: '',
        occurred_at: new Date().toISOString().slice(0, 16),
        attachments: [], // Array of files
    });

    const selectedStudent = useMemo(() => students.find(s => s.id == data.student_id), [students, data.student_id]);
    const selectedViolation = useMemo(() => violations.find(v => v.id == data.violation_id), [violations, data.violation_id]);

    const filteredStudents = useMemo(() => {
        if (!searchQuery) return students;
        const q = searchQuery.toLowerCase();
        return students.filter(s => 
            s.full_name?.toLowerCase().includes(q) || 
            s.department?.toLowerCase().includes(q) ||
            s.student_number?.toLowerCase().includes(q)
        );
    }, [students, searchQuery]);

    const filteredViolations = useMemo(() => {
        if (!violationQuery) return violations;
        const q = violationQuery.toLowerCase();
        return violations.filter(v => 
            v.title?.toLowerCase().includes(q) || 
            v.code?.toLowerCase().includes(q)
        );
    }, [violations, violationQuery]);

    useEffect(() => {
        if (data.violation_id && data.student_id) {
            setIsLoadingSanction(true);
            fetch(route('api.get-sanction-info', { violation_id: data.violation_id, student_id: data.student_id }))
                .then(res => res.json())
                .then(info => {
                    setSanctionInfo(info);
                    setIsLoadingSanction(false);
                })
                .catch(() => {
                    setSanctionInfo(null);
                    setIsLoadingSanction(false);
                });
        } else {
            setSanctionInfo(null);
        }
    }, [data.violation_id, data.student_id]);

    const handleNext = () => setStep(s => Math.min(s + 1, 3));
    const handleBack = () => setStep(s => Math.max(s - 1, 1));

    const handleFileChange = (e) => {
        if (e.target.files) {
            setData('attachments', [...data.attachments, ...Array.from(e.target.files)]);
        }
    };

    const removeFile = (index) => {
        const newFiles = [...data.attachments];
        newFiles.splice(index, 1);
        setData('attachments', newFiles);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('cases.store'), {
            forceFormData: true,
            onSuccess: () => {
                // Flash message will be handled by the backend redirect
            }
        });
    };

    const renderStepIndicators = () => (
        <div className="flex items-center justify-center mb-12">
            <div className="flex items-center w-full max-w-2xl">
                {[
                    { num: 1, title: 'Student', icon: UserSearch },
                    { num: 2, title: 'Violation', icon: AlertCircle },
                    { num: 3, title: 'Details', icon: FilePlus }
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
                                    Log a Violation
                                </h1>
                                <p className="text-rose-200/70 text-sm mt-1">Record a new incident and attach necessary evidence.</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-900 overflow-hidden shadow-xl sm:rounded-3xl border border-slate-100 dark:border-slate-800 p-8">
                        {renderStepIndicators()}

                        <form onSubmit={submit} className="space-y-6">
                            
                            {/* STEP 1: STUDENT */}
                            {step === 1 && (
                                <div className="space-y-4 animate-in fade-in slide-in-from-right-4 duration-500">
                                    <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        <UserSearch className="w-5 h-5 text-rose-500" /> Select Student
                                    </h3>
                                    
                                    <div className="relative">
                                        <input 
                                            type="text" 
                                            placeholder="Search by name, ID, or department..." 
                                            className="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 transition-all"
                                            value={searchQuery}
                                            onChange={e => setSearchQuery(e.target.value)}
                                        />
                                        <UserSearch className="w-5 h-5 text-slate-400 absolute left-3 top-3.5" />
                                    </div>
                                    
                                    {errors.student_id && <p className="text-sm text-red-600 font-medium">{errors.student_id}</p>}

                                    <div className="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                        {filteredStudents.map(s => (
                                            <div 
                                                key={s.id} 
                                                onClick={() => setData('student_id', s.id)}
                                                className={`p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-4 ${data.student_id == s.id ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800'}`}
                                            >
                                                <div className="w-12 h-12 rounded-full bg-slate-200 overflow-hidden flex-shrink-0">
                                                    {s.avatar ? (
                                                        <img src={`/storage/${s.avatar}`} className="w-full h-full object-cover" />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold bg-gradient-to-br from-slate-100 to-slate-200">
                                                            {s.full_name?.charAt(0) || '?'}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <h4 className="font-bold text-slate-800 dark:text-slate-200">{s.full_name}</h4>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">{s.student_number} • {s.department}</p>
                                                </div>
                                                {data.student_id == s.id && <CheckCircle2 className="w-6 h-6 text-rose-500" />}
                                            </div>
                                        ))}
                                        {filteredStudents.length === 0 && (
                                            <div className="p-8 text-center text-slate-500 dark:text-slate-400">No students found.</div>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: VIOLATION */}
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
                                            onChange={e => setViolationQuery(e.target.value)}
                                        />
                                        <AlertCircle className="w-5 h-5 text-slate-400 absolute left-3 top-3.5" />
                                    </div>

                                    {errors.violation_id && <p className="text-sm text-red-600 font-medium">{errors.violation_id}</p>}

                                    <div className="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                        {filteredViolations.map(v => (
                                            <div 
                                                key={v.id} 
                                                onClick={() => setData('violation_id', v.id)}
                                                className={`p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3 ${data.violation_id == v.id ? 'border-rose-500 bg-rose-50 dark:bg-rose-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800'}`}
                                            >
                                                <div className={`mt-1 w-2.5 h-2.5 rounded-full flex-shrink-0 ${
                                                    v.severity === 'Minor' ? 'bg-amber-400' :
                                                    v.severity === 'Major' ? 'bg-orange-500' : 'bg-red-600'
                                                }`} />
                                                <div className="flex-1">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-bold text-slate-800 dark:text-slate-200 text-sm">{v.code} - {v.title}</h4>
                                                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-100 px-2 py-1 rounded-md">{v.severity}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* STEP 3: DETAILS & EVIDENCE */}
                            {step === 3 && (
                                <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                                    <h3 className="text-lg font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-4">
                                        <FilePlus className="w-5 h-5 text-rose-500" /> Incident Details & Evidence
                                    </h3>

                                    {/* Selected Summary */}
                                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 mb-6">
                                        <div className="flex-1">
                                            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offender</p>
                                            <p className="font-bold text-slate-800 dark:text-slate-200">{selectedStudent?.full_name || 'None'}</p>
                                        </div>
                                        <div className="hidden sm:block w-px bg-slate-200"></div>
                                        <div className="flex-1">
                                            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Offense</p>
                                            <p className="font-bold text-slate-800 dark:text-slate-200">{selectedViolation?.title || 'None'}</p>
                                        </div>
                                    </div>

                                    {/* Dynamic Sanction */}
                                    {sanctionInfo && (
                                        <div className="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 shadow-sm flex items-start gap-3">
                                            <AlertCircle className="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" />
                                            <div>
                                                <p className="text-xs font-bold text-rose-800 uppercase tracking-wider mb-1">Expected Sanction (Offense #{sanctionInfo.offense_level})</p>
                                                <p className="text-rose-900 font-medium text-sm">{sanctionInfo.sanction}</p>
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

                                    <div>
                                        <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Incident Description</label>
                                        <textarea 
                                            rows="4"
                                            required
                                            placeholder="Provide details about the incident..."
                                            className="w-full rounded-xl border-slate-200 dark:border-slate-700 focus:border-rose-500 focus:ring-rose-500/20 bg-slate-50 dark:bg-slate-800 resize-none"
                                            value={data.description}
                                            onChange={e => setData('description', e.target.value)}
                                        ></textarea>
                                        {errors.description && <p className="text-xs text-red-600 mt-1">{errors.description}</p>}
                                    </div>


                                </div>
                            )}

                            {/* NAVIGATION BUTTONS */}
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
                                        disabled={(step === 1 && !data.student_id) || (step === 2 && !data.violation_id)}
                                        className={`px-6 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg ${((step === 1 && !data.student_id) || (step === 2 && !data.violation_id)) ? 'bg-slate-200 text-slate-400 shadow-none' : 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-0.5 shadow-rose-600/30'}`}
                                    >
                                        Next <ArrowRight className="w-4 h-4" />
                                    </button>
                                ) : (
                                    <button
                                        type="submit"
                                        disabled={processing || isLoadingSanction}
                                        className={`px-8 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg ${(processing || isLoadingSanction) ? 'bg-slate-200 text-slate-400 shadow-none' : 'bg-rose-600 text-white hover:bg-rose-700 hover:-translate-y-0.5 shadow-rose-600/30'}`}
                                    >
                                        {processing ? 'Submitting...' : 'Submit Violation'}
                                        {!processing && <CheckCircle2 className="w-4 h-4" />}
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
