import React, { useState } from 'react';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ConfirmDialog from '@/Components/ConfirmDialog';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { Head, Link, router } from '@inertiajs/react';
import { 
    ArrowLeft, FolderOpen, Edit3, Printer, ArrowUpRight, 
    Check, FileWarning, Calendar, UserCheck, Gavel, 
    ChevronRight, CalendarX, CalendarPlus, CheckCircle2, 
    ExternalLink, Trash2, ClipboardList, Plus, Scale
} from 'lucide-react';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

const OSA_LABELS = {
    letter_sent: 'Letter Sent',
    counseling: 'Counseling',
    parent_conference: 'Parent Conference',
    verbal_warning: 'Verbal Warning',
    written_warning: 'Written Warning',
    other: 'Other',
};

export default function Show({ auth, caseRecord, offenseHistory, offenseSummary, workflow = {} }) {
    const [confirmAction, setConfirmAction] = useState(null);
    const [hearingSanction, setHearingSanction] = useState('');
    const [osaType, setOsaType] = useState('letter_sent');
    const [osaDescription, setOsaDescription] = useState('');
    const [osaError, setOsaError] = useState('');
    const stages = ['Pending', 'Hearing Scheduled', 'Hearing', 'Closed'];
    let currentIndex = stages.indexOf(caseRecord.status);
    if (currentIndex === -1) currentIndex = 0;
    const isEndorsed = Boolean(caseRecord.endorsed_at);
    const latestHearing = caseRecord.hearings?.length ? caseRecord.hearings[caseRecord.hearings.length - 1] : null;
    const osaActions = (caseRecord.actions || []).filter((a) => !a.endorsed_to_grievance);

    const handleRecordOsa = () => {
        setOsaType('letter_sent');
        setOsaDescription('');
        setOsaError('');
        setConfirmAction('osa');
    };

    const handleStartHearing = () => {
        if (!latestHearing) return;
        setConfirmAction('start');
    };

    const handleCompleteHearing = () => {
        if (!latestHearing) return;
        setHearingSanction('');
        setConfirmAction('complete');
    };

    const handleEndorse = (e) => {
        e.preventDefault();
        if (workflow.endorse_block_reason && !workflow.can_endorse) {
            Swal.fire({ icon: 'warning', title: 'Cannot Endorse', text: workflow.endorse_block_reason });
            return;
        }
        setConfirmAction('endorse');
    };

    const handleClose = (e) => {
        e.preventDefault();
        if (workflow.close_block_reason && !workflow.can_close) {
            Swal.fire({ icon: 'warning', title: 'Cannot Close Case', text: workflow.close_block_reason });
            return;
        }
        setConfirmAction('close');
    };

    const handleDelete = (e) => {
        e.preventDefault();
        setConfirmAction('trash');
    };

    const runConfirmAction = () => {
        switch (confirmAction) {
            case 'start':
                router.post(route('hearings.start', latestHearing.id));
                break;
            case 'complete':
                router.post(route('hearings.complete', latestHearing.id), { sanction: hearingSanction });
                break;
            case 'endorse':
                router.post(route('cases.endorse', caseRecord.id));
                break;
            case 'close':
                router.post(route('cases.close', caseRecord.id));
                break;
            case 'trash':
                router.delete(route('cases.destroy', caseRecord.id));
                break;
            case 'osa':
                if (!osaDescription.trim()) {
                    setOsaError('Description is required.');
                    return;
                }
                router.post(route('cases.actions.store', caseRecord.id), {
                    action_type: osaType,
                    description: osaDescription,
                });
                break;
            default:
                break;
        }
        setConfirmAction(null);
    };

    const getStatusStyle = (status) => {
        const map = {
            'Pending':                'bg-amber-50 dark:bg-amber-900/20 text-amber-700 border-amber-200',
            'Hearing Scheduled':      'bg-blue-50 dark:bg-blue-900/20 text-blue-700 border-blue-200',
            'Hearing':                'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 border-indigo-200',
            'Endorsed to Grievance':  'bg-rose-50 dark:bg-rose-900/20 text-rose-700 border-rose-200',
            'Dismissed':              'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700',
            'Closed':                 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 border-emerald-200',
        };
        return map[status] || 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
    };

    const getInitials = (name) => {
        if (!name) return '??';
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Case Details</h2>}
        >
            <Head title={`Case #${String(caseRecord.id).padStart(4, '0')} - Details`} />

            <PageMotion>

                <MotionItem>
                <Breadcrumbs
                    items={[
                        { label: 'Dashboard', href: route('dashboard') },
                        { label: 'All Cases', href: route('cases.index') },
                        { label: `Case #${String(caseRecord.id).padStart(4, '0')}` },
                    ]}
                />
                </MotionItem>

                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div className="flex items-center gap-5">
                            <Link href={route('cases.index')} className="w-12 h-12 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                                <ArrowLeft className="w-5.5 h-5.5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                    <FolderOpen className="w-3.5 h-3.5" />
                                    Violation Record
                                </div>
                                <h2 className="text-3xl font-bold text-white tracking-tight">Violation Case #{String(caseRecord.id).padStart(4, '0')}</h2>
                                <p className="text-indigo-100/70 text-sm mt-1">Student Respondent: <span className="text-white font-medium">{caseRecord.student?.full_name}</span></p>
                            </div>
                        </div>
                        
                        <div className="flex items-center gap-3">
                            {caseRecord.status !== 'Closed' && (
                                <Link href={route('cases.edit', caseRecord.id)} className="px-5 py-2.5 bg-white/10 dark:bg-slate-900/10 border border-white/20 text-white rounded-xl text-sm font-bold shadow-sm backdrop-blur-md hover:bg-white/20 dark:bg-slate-900/20 transition-all flex items-center gap-2">
                                    <Edit3 className="w-4.5 h-4.5" />
                                    Edit
                                </Link>
                            )}
                            <a href={route('cases.print', caseRecord.id)} target="_blank" className="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/30 hover:bg-indigo-400 transition-all flex items-center gap-2">
                                <Printer className="w-4.5 h-4.5" />
                                Print
                            </a>
                        </div>
                    </div>
                </div>

                {/* ═══ CASE LIFECYCLE PIPELINE ═══ */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-8 mb-8 relative overflow-hidden">
                    {isEndorsed ? (
                        <div className="flex items-center justify-center gap-4 py-4">
                            <div className="w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-900/20 border-4 border-rose-100 flex items-center justify-center shadow-inner">
                                <ArrowUpRight className="w-6 h-6 text-rose-600 dark:text-rose-400" />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold text-rose-700">Endorsed to Grievance Committee</h3>
                                <p className="text-sm font-medium text-rose-500 mt-0.5">This case has been escalated and is no longer in standard processing.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="flex items-center justify-between relative">
                            <div className="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full"></div>
                            <div className="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-emerald-400 rounded-full transition-all duration-1000" style={{ width: `${stages.length > 1 ? (currentIndex / (stages.length - 1)) * 100 : 0}%` }}></div>
                            
                            {stages.map((stage, i) => (
                                <div key={i} className="flex flex-col items-center relative z-10 group">
                                    {i < currentIndex ? (
                                        <div className="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border-4 border-emerald-400 flex items-center justify-center shadow-md shadow-emerald-500/10">
                                            <Check className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                    ) : i === currentIndex ? (
                                        <div className="w-12 h-12 rounded-full bg-indigo-600 border-4 border-white flex items-center justify-center shadow-lg shadow-indigo-600/30">
                                            <span className="w-3.5 h-3.5 rounded-full bg-white dark:bg-slate-900 animate-pulse"></span>
                                        </div>
                                    ) : (
                                        <div className="w-12 h-12 rounded-full bg-white dark:bg-slate-900 border-4 border-slate-200 dark:border-slate-700 flex items-center justify-center group-hover:border-slate-300 transition-colors">
                                            <span className="w-3.5 h-3.5 rounded-full bg-slate-200 group-hover:bg-slate-300 transition-colors"></span>
                                        </div>
                                    )}
                                    <p className={`text-xs font-bold mt-3 text-center whitespace-nowrap ${i <= currentIndex ? (i === currentIndex ? 'text-indigo-700' : 'text-emerald-700') : 'text-slate-400'}`}>
                                        {stage}
                                    </p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Column: Summary */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Status & Violation Card */}
                        <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden">
                            <div className="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                                <div className="flex items-center gap-4">
                                    <div className="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-sm">
                                        <FileWarning className="w-5 h-5 text-rose-500" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-slate-900 dark:text-white">{caseRecord.violation?.title}</h3>
                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{caseRecord.violation?.category} — Code: {caseRecord.violation?.code}</p>
                                    </div>
                                </div>
                                <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${getStatusStyle(caseRecord.status)}`}>
                                    <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {caseRecord.status}
                                </span>
                            </div>
                            
                            <div className="p-6 space-y-6">
                                <div>
                                    <h4 className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Incident Description</h4>
                                    <div className="bg-slate-50 dark:bg-slate-800 rounded-xl p-5 text-slate-700 dark:text-slate-300 text-sm leading-relaxed border border-slate-100 dark:border-slate-800">
                                        {caseRecord.description}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                    <div>
                                        <h4 className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date & Time</h4>
                                        <div className="flex items-center gap-2 text-slate-900 dark:text-white text-sm font-medium">
                                            <Calendar className="w-4 h-4 text-slate-400" />
                                            {new Date(caseRecord.occurred_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                                        </div>
                                        <div className="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-sm font-medium mt-1 ml-6">
                                            {new Date(caseRecord.occurred_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Witnesses</h4>
                                        <div className="flex items-center gap-2 text-slate-900 dark:text-white text-sm font-medium">
                                            <UserCheck className="w-4 h-4 text-slate-400" />
                                            {caseRecord.witness || 'No Witness Logged'}
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/20 p-5">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                                            <Scale className="w-5 h-5" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex flex-wrap items-center gap-2 mb-2">
                                                <h4 className="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider">Assigned Sanction</h4>
                                                {caseRecord.offense_level && (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-200/60 dark:bg-amber-800/40 text-amber-900 dark:text-amber-200 text-[10px] font-bold uppercase tracking-wide">
                                                        Offense #{caseRecord.offense_level}
                                                    </span>
                                                )}
                                                {caseRecord.status === 'Closed' && caseRecord.sanction && (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold uppercase tracking-wide">
                                                        <CheckCircle2 className="w-3 h-3" />
                                                        Served
                                                    </span>
                                                )}
                                            </div>
                                            <p className="text-sm font-semibold text-amber-950 dark:text-amber-100 leading-relaxed">
                                                {caseRecord.sanction || 'Sanction pending determination.'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Hearing Protocol Hub */}
                        <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6">
                            <div className="flex items-center justify-between mb-6">
                                <h3 className="text-lg font-bold text-slate-900 dark:text-white">Case Hearings</h3>
                                {caseRecord.status !== 'Closed' && (
                                    <Link href={route('hearings.create', { case: caseRecord.id })} className="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-800 transition-colors flex items-center gap-2">
                                        <Gavel className="w-4 h-4" />
                                        Schedule Hearing
                                    </Link>
                                )}
                            </div>

                            <div className="space-y-3">
                                {caseRecord.hearings && caseRecord.hearings.length > 0 ? (
                                    caseRecord.hearings.map((hearing, index) => (
                                        <div key={hearing.id} className="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-indigo-200 transition-colors">
                                            <div className="flex items-center gap-4">
                                                <div className="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <p className="text-sm font-bold text-slate-900 dark:text-white">Hearing #{index + 1}</p>
                                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {new Date(hearing.scheduled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })} at {new Date(hearing.scheduled_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <span className="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400 shadow-sm">
                                                    {hearing.venue || "Dean's Office"}
                                                </span>
                                                <a href={route('hearings.show', hearing.id)} className="p-2 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-lg transition-all" title="View Hearing" aria-label="View hearing details">
                                                    <ChevronRight className="w-5 h-5" />
                                                </a>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="bg-slate-50 dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 p-10 text-center">
                                        <div className="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center mx-auto mb-3 shadow-sm">
                                            <CalendarX className="w-6 h-6 text-slate-400" />
                                        </div>
                                        <p className="text-slate-600 dark:text-slate-400 text-sm font-bold">No Hearings Scheduled</p>
                                        <p className="text-slate-400 text-xs font-medium mt-1">Schedule a hearing to begin the violation process.</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* OSA Interventions */}
                        {caseRecord.status !== 'Closed' && (
                            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <ClipboardList className="w-5 h-5 text-indigo-500" />
                                        OSA Interventions
                                    </h3>
                                    <button type="button" onClick={handleRecordOsa} className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">
                                        <Plus className="w-4 h-4" />
                                        Record Action
                                    </button>
                                </div>
                                {workflow.needs_osa_action && (
                                    <p className="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-4">
                                        This is a major offense. Record at least one OSA intervention before endorsing or closing without a hearing.
                                    </p>
                                )}
                                {osaActions.length > 0 ? (
                                    <div className="space-y-3">
                                        {osaActions.map((action) => (
                                            <div key={action.id} className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                                <div className="flex items-center justify-between gap-2 mb-1">
                                                    <span className="text-sm font-bold text-slate-900">{OSA_LABELS[action.action_type] || action.action_type}</span>
                                                    <span className="text-xs text-slate-500">{action.user?.name || 'Staff'}</span>
                                                </div>
                                                <p className="text-sm text-slate-600">{action.description}</p>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-slate-500">No OSA interventions recorded yet.</p>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Right Column: Case Info & Quick Actions */}
                    <div className="space-y-6">
                        {/* Quick Actions */}
                        {caseRecord.status !== 'Closed' && (
                            <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 relative overflow-hidden group">
                                <div className="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20/50 rounded-full blur-3xl -z-10"></div>
                                <h3 className="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-5">Quick Actions</h3>
                                <div className="space-y-3">
                                    {caseRecord.status === 'Hearing Scheduled' && latestHearing && (
                                        <button type="button" onClick={handleStartHearing} className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-sm font-bold shadow-sm hover:bg-indigo-100 hover:-translate-y-0.5 transition-all duration-200">
                                            <Gavel className="w-4.5 h-4.5" />
                                            Start Hearing
                                        </button>
                                    )}
                                    {caseRecord.status === 'Hearing' && latestHearing && (
                                        <button type="button" onClick={handleCompleteHearing} className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-100 hover:-translate-y-0.5 transition-all duration-200">
                                            <CheckCircle2 className="w-4.5 h-4.5" />
                                            Complete Hearing
                                        </button>
                                    )}
                                    {caseRecord.status === 'Pending' && (
                                        <Link href={route('hearings.create', { case: caseRecord.id })} className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl text-sm font-bold shadow-sm hover:bg-blue-100 hover:-translate-y-0.5 transition-all duration-200">
                                            <CalendarPlus className="w-4.5 h-4.5" />
                                            Schedule Hearing
                                        </Link>
                                    )}
                                    {!isEndorsed && (
                                    <button
                                        type="button"
                                        onClick={handleEndorse}
                                        disabled={workflow.can_endorse === false}
                                        title={workflow.endorse_block_reason || ''}
                                        className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 text-amber-700 rounded-xl text-sm font-bold shadow-sm hover:bg-amber-100 hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <ArrowUpRight className="w-4.5 h-4.5" />
                                        Endorse to Grievance
                                    </button>
                                    )}
                                    <button
                                        type="button"
                                        onClick={handleClose}
                                        disabled={workflow.can_close === false}
                                        title={workflow.close_block_reason || ''}
                                        className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-100 hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <CheckCircle2 className="w-4.5 h-4.5" />
                                        Close Case
                                    </button>
                                </div>
                                
                                {/* Destructive Actions - Admin Only usually, but let's place it below */}
                                <div className="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <button onClick={handleDelete} className="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/50 text-rose-600 dark:text-rose-400 rounded-xl text-sm font-bold shadow-sm hover:bg-rose-100 dark:hover:bg-rose-900/30 hover:-translate-y-0.5 transition-all duration-200">
                                        <Trash2 className="w-4.5 h-4.5" />
                                        Move to Trash Bin
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Student Info */}
                        <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6">
                            <h3 className="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-5">Student Information</h3>
                            <div className="flex items-center gap-4 mb-6">
                                <div className="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg border border-indigo-100">
                                    {getInitials(caseRecord.student?.full_name)}
                                </div>
                                <div>
                                    <p className="text-sm font-bold text-slate-900 dark:text-white">{caseRecord.student?.full_name}</p>
                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{caseRecord.student?.id_number}</p>
                                </div>
                            </div>
                            
                            <div className="space-y-3">
                                <div className="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-800">
                                    <span className="text-sm font-medium text-slate-500 dark:text-slate-400">Department</span>
                                    <span className="text-sm font-bold text-slate-900 dark:text-white">{caseRecord.student?.department}</span>
                                </div>
                                <div className="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-800">
                                    <span className="text-sm font-medium text-slate-500 dark:text-slate-400">Previous Incidents</span>
                                    <span className={`text-sm font-bold ${Math.max(0, offenseSummary.total - 1) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'}`}>
                                        {Math.max(0, offenseSummary.total - 1)} Cases
                                    </span>
                                </div>
                                <div className="flex items-center justify-between py-2.5">
                                    <span className="text-sm font-medium text-slate-500 dark:text-slate-400">Status</span>
                                    <span className="text-sm font-bold text-emerald-600 dark:text-emerald-400">Enrolled</span>
                                </div>
                            </div>

                            <a href={route('students.show', caseRecord.student?.id)} className="mt-6 w-full py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                View Student Profile
                                <ExternalLink className="w-4 h-4" />
                            </a>
                        </div>

                        {/* Severity */}
                        <div className="bg-rose-50 dark:bg-rose-900/20 rounded-2xl border border-rose-100 p-6 shadow-sm">
                            <h3 className="text-[11px] font-bold text-rose-800 uppercase tracking-widest mb-4">Violation Severity</h3>
                            <div className="space-y-4">
                                <div>
                                    <p className="text-xs font-bold text-rose-600 dark:text-rose-400 mb-1">Severity Level</p>
                                    <p className="text-lg font-black text-rose-900">{caseRecord.violation?.severity || 'Major'} Offense</p>
                                </div>
                                <div className="pt-4 border-t border-rose-200/50">
                                    <p className="text-xs font-bold text-rose-600 dark:text-rose-400 mb-1">Offense Count for Student</p>
                                    <div className="flex items-baseline gap-2">
                                        <p className="text-2xl font-black text-rose-900">{offenseSummary.total}</p>
                                        <p className="text-xs font-medium text-rose-700">total ({offenseSummary.minor} minor, {offenseSummary.major} major)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </PageMotion>

            <ConfirmDialog
                open={confirmAction === 'start'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Start Hearing?"
                description="This will mark the case as currently in hearing."
                confirmLabel="Start Hearing"
            />
            <ConfirmDialog
                open={confirmAction === 'complete'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Complete Hearing"
                description="Enter the sanction or resolution for this hearing."
                confirmLabel="Close Case"
                destructive={false}
            >
                <label htmlFor="hearing-sanction" className="block text-xs font-bold text-slate-500 uppercase mb-2">
                    Sanction / Resolution
                </label>
                <input
                    id="hearing-sanction"
                    type="text"
                    value={hearingSanction}
                    onChange={(e) => setHearingSanction(e.target.value)}
                    className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                    placeholder="Enter the sanction or resolution..."
                />
            </ConfirmDialog>
            <ConfirmDialog
                open={confirmAction === 'endorse'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Endorse to Grievance?"
                description="Are you sure you want to endorse this case?"
                confirmLabel="Yes, endorse it"
            />
            <ConfirmDialog
                open={confirmAction === 'close'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Close this case?"
                description="This action marks the case as resolved."
                confirmLabel="Yes, close case"
            />
            <ConfirmDialog
                open={confirmAction === 'trash'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Move to Trash Bin?"
                description="Are you sure you want to move this violation record to the trash?"
                confirmLabel="Yes, move to trash"
                destructive
            />
            <ConfirmDialog
                open={confirmAction === 'osa'}
                onClose={() => setConfirmAction(null)}
                onConfirm={runConfirmAction}
                title="Record OSA Intervention"
                description="Log an action taken by the Office of Student Affairs for this case."
                confirmLabel="Save Action"
            >
                <div className="space-y-4">
                    <div>
                        <label htmlFor="osa-type" className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Intervention Type
                        </label>
                        <select
                            id="osa-type"
                            value={osaType}
                            onChange={(e) => setOsaType(e.target.value)}
                            className="form-input"
                        >
                            {Object.entries(OSA_LABELS).map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label htmlFor="osa-desc" className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Description <span className="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="osa-desc"
                            value={osaDescription}
                            onChange={(e) => { setOsaDescription(e.target.value); setOsaError(''); }}
                            rows={4}
                            placeholder="Describe the intervention..."
                            className="form-input"
                        />
                        {osaError && <p className="text-rose-500 text-xs mt-1.5 font-semibold">{osaError}</p>}
                    </div>
                </div>
            </ConfirmDialog>
        </AuthenticatedLayout>
    );
}
