import React, { useState, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    User, Printer, Edit3, MessageCircle, PlusCircle, Mail, Phone, BadgeCheck,
    Calendar, Eye, ShieldCheck, X, Send,
} from 'lucide-react';

function getInitials(fullName) {
    if (!fullName) return '??';
    let initials = '';
    for (const name of fullName.split(' ')) {
        if (name) {
            initials += name[0].toUpperCase();
            if (initials.length >= 2) break;
        }
    }
    return initials || fullName.substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getRiskLevel(total) {
    if (total >= 5) return { level: 'High', color: 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800' };
    if (total >= 2) return { level: 'Medium', color: 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800' };
    return { level: 'Low', color: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' };
}

function getStatusDotColor(status) {
    const map = {
        Pending: 'bg-amber-500 ring-amber-100 dark:ring-amber-900/30',
        'Hearing Scheduled': 'bg-blue-500 ring-blue-100 dark:ring-blue-900/30',
        Closed: 'bg-green-500 ring-green-100 dark:ring-green-900/30',
        'Endorsed to Grievance': 'bg-red-500 ring-red-100 dark:ring-red-900/30',
    };
    return map[status] || 'bg-gray-400 ring-gray-100 dark:ring-gray-800';
}

function getSeverityStyle(severity) {
    const map = {
        Minor: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
        Major: 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-800',
        Critical: 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
    };
    return map[severity] || 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700';
}

function getStatusTextColor(status) {
    const map = {
        Pending: 'text-amber-600 dark:text-amber-400',
        Closed: 'text-green-600 dark:text-green-400',
        'Hearing Scheduled': 'text-blue-600 dark:text-blue-400',
        'Endorsed to Grievance': 'text-red-600 dark:text-red-400',
    };
    return map[status] || 'text-slate-400';
}

export default function Show({ auth, student, offenseSummary, messageTemplates }) {
    const [showMessageModal, setShowMessageModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        message: '',
        delivery_method: ['sms', 'email'],
    });

    const cases = useMemo(() => {
        const list = student.cases || [];
        return [...list].sort((a, b) => new Date(b.occurred_at) - new Date(a.occurred_at));
    }, [student.cases]);

    const risk = getRiskLevel(offenseSummary.total);
    const initials = getInitials(student.full_name);

    const toggleDelivery = (method) => {
        const current = data.delivery_method;
        if (current.includes(method)) {
            setData('delivery_method', current.filter((m) => m !== method));
        } else {
            setData('delivery_method', [...current, method]);
        }
    };

    const handleTemplateChange = (e) => {
        const idx = e.target.value;
        if (idx !== '') {
            setData('message', messageTemplates[idx].content);
        }
    };

    const sendMessage = (e) => {
        e.preventDefault();
        post(route('students.sendCustomMessage', student.id), {
            onSuccess: () => {
                reset();
                setShowMessageModal(false);
            },
        });
    };

    const closeModal = () => {
        setShowMessageModal(false);
        reset();
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Student Profile</h2>}
        >
            <Head title={`${student.full_name} - Profile`} />

            <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div className="vt-page-hero p-8">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl" />

                    <div className="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div className="flex items-center gap-6">
                            <div className="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 p-1 shadow-inner backdrop-blur-md">
                                <div className="w-full h-full rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                                    {initials}
                                </div>
                            </div>
                            <div>
                                <div className="vt-hero-chip mb-2">
                                    <User className="w-3.5 h-3.5" />
                                    Student Profile
                                </div>
                                <h1 className="text-3xl font-bold text-white tracking-tight">{student.full_name}</h1>
                                <p className="text-indigo-100/70 text-sm mt-1 flex flex-wrap items-center gap-2">
                                    <span>{student.department}</span>
                                    <span className="w-1 h-1 rounded-full bg-indigo-400/50" />
                                    <span>{student.year_level}</span>
                                    <span className="w-1 h-1 rounded-full bg-indigo-400/50" />
                                    <span>{student.section}</span>
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <Link href={route('students.print', student.id)} target="_blank" className="vt-hero-btn">
                                <Printer className="w-4 h-4" />
                                Print
                            </Link>
                            <Link href={route('students.edit', student.id)} className="vt-hero-btn">
                                <Edit3 className="w-4 h-4" />
                                Edit
                            </Link>
                            <button
                                type="button"
                                onClick={() => setShowMessageModal(true)}
                                className="px-5 py-2.5 bg-emerald-500 border border-emerald-400 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-400 transition-all flex items-center gap-2"
                            >
                                <MessageCircle className="w-4 h-4" />
                                Message Guardian
                            </button>
                            <Link
                                href={route('cases.create', { student_id: student.id })}
                                className="px-5 py-2.5 bg-indigo-500 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/25 hover:bg-indigo-400 transition-all flex items-center gap-2"
                            >
                                <PlusCircle className="w-4 h-4" />
                                Log Violation
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="space-y-6">
                        <div className="vt-content-card p-6">
                            <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Student Information</h3>
                            <div className="space-y-4">
                                <div className="flex items-start gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <Mail className="w-4 h-4 text-slate-400" />
                                    </div>
                                    <div className="min-w-0">
                                        <p className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</p>
                                        <p className="text-sm font-medium text-slate-800 mt-0.5 break-all">{student.email}</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <Phone className="w-4 h-4 text-slate-400" />
                                    </div>
                                    <div>
                                        <p className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Guardian</p>
                                        <p className="text-sm font-medium text-slate-800 mt-0.5">{student.guardian_name || 'Not Listed'}</p>
                                        {student.guardian_phone && (
                                            <p className="text-xs text-slate-500 mt-0.5">Phone: {student.guardian_phone}</p>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <div className="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <BadgeCheck className="w-4 h-4 text-slate-400" />
                                    </div>
                                    <div>
                                        <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</p>
                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-100 dark:border-green-800 mt-0.5">
                                            <span className="w-1.5 h-1.5 rounded-full bg-green-500" />
                                            Active Enrollment
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="vt-content-card p-6">
                            <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-5">Incident Summary</h3>
                            <div className="grid grid-cols-3 gap-4">
                                <div className="text-center">
                                    <p className={`text-3xl font-bold ${offenseSummary.total > 2 ? 'text-red-600 dark:text-red-400' : offenseSummary.total > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400'}`}>
                                        {offenseSummary.total}
                                    </p>
                                    <p className="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Total</p>
                                </div>
                                <div className="text-center border-x border-slate-200">
                                    <p className="text-3xl font-bold text-amber-600">{offenseSummary.minor}</p>
                                    <p className="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Minor</p>
                                </div>
                                <div className="text-center">
                                    <p className="text-3xl font-bold text-red-600">{offenseSummary.major}</p>
                                    <p className="text-[11px] text-slate-500 font-medium uppercase tracking-wider mt-1">Major</p>
                                </div>
                            </div>
                            <div className="mt-5 pt-4 border-t border-slate-200 flex items-center justify-between">
                                <span className="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Risk Level</span>
                                <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border ${risk.color}`}>
                                    {risk.level}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="lg:col-span-2 space-y-5">
                        <div className="vt-content-card overflow-hidden">
                            <div className="p-5 flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/50">
                                <div className="min-w-0">
                                    <h2 className="text-lg font-semibold text-slate-900 tracking-tight">Violation Timeline</h2>
                                    <p className="text-sm text-slate-500 mt-1">Chronological record of this student&apos;s violation cases.</p>
                                </div>
                                <Link
                                    href={route('cases.create', { student_id: student.id })}
                                    className="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/20 hover:bg-indigo-700 transition-all duration-200 flex-shrink-0"
                                >
                                    <PlusCircle className="w-4 h-4" />
                                    Add Incident
                                </Link>
                            </div>

                            <div className="p-6">
                                {cases.length > 0 ? (
                                    cases.map((caseItem, index) => (
                                        <div key={caseItem.id} className="flex gap-4 pb-6 last:pb-0 group">
                                            <div className="flex flex-col items-center">
                                                <div className={`w-3 h-3 rounded-full ring-4 mt-1.5 flex-shrink-0 z-10 ${getStatusDotColor(caseItem.endorsed_at ? 'Endorsed to Grievance' : caseItem.status)}`} />
                                                {index < cases.length - 1 && (
                                                    <div className="w-0.5 flex-1 bg-gray-200 dark:bg-slate-700 mt-1" />
                                                )}
                                            </div>
                                            <div className="flex-1 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 -mt-0.5">
                                                <div className="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                                    <div className="flex-1">
                                                        <div className="flex items-center gap-2 mb-1 flex-wrap">
                                                            <h4 className="text-sm font-semibold text-slate-900">
                                                                {caseItem.violation?.title || 'Unknown Violation'}
                                                            </h4>
                                                            <span className={`px-1.5 py-0.5 rounded text-[10px] font-bold border ${getSeverityStyle(caseItem.violation?.severity || 'Minor')}`}>
                                                                {caseItem.violation?.severity || 'Minor'}
                                                            </span>
                                                        </div>
                                                        {caseItem.description && (
                                                            <p className="text-xs text-slate-500 line-clamp-2">{caseItem.description}</p>
                                                        )}
                                                        <div className="flex items-center gap-4 mt-2.5 flex-wrap">
                                                            <span className="text-[11px] font-medium text-slate-500 flex items-center gap-1">
                                                                <Calendar className="w-3 h-3" />
                                                                {formatDate(caseItem.occurred_at)}
                                                            </span>
                                                            <span className={`text-[11px] font-bold uppercase tracking-wider ${getStatusTextColor(caseItem.endorsed_at ? 'Endorsed to Grievance' : caseItem.status)}`}>
                                                                {caseItem.endorsed_at ? 'Endorsed' : caseItem.status}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <Link
                                                        href={route('cases.show', caseItem.id)}
                                                        className="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all flex-shrink-0"
                                                    >
                                                        <Eye className="w-4 h-4" />
                                                        View
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-slate-300 p-12 text-center bg-slate-50">
                                        <div className="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-4 border border-green-100">
                                            <ShieldCheck className="w-8 h-8 text-green-500" />
                                        </div>
                                        <h4 className="text-base font-semibold text-slate-900">Exemplary Conduct</h4>
                                        <p className="text-sm text-slate-500 mt-1.5 max-w-xs mx-auto">
                                            No violation records found. This student maintains an excellent behavioral record.
                                        </p>
                                        <Link
                                            href={route('cases.create', { student_id: student.id })}
                                            className="mt-7 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/20 hover:bg-indigo-700 transition-all duration-200"
                                        >
                                            <PlusCircle className="w-4 h-4" />
                                            Log First Incident
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {showMessageModal && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
                    <div
                        className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                        onClick={closeModal}
                    />
                    <div className="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
                        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <MessageCircle className="w-5 h-5 text-indigo-500" />
                                Send Message to Guardian
                            </h3>
                            <button type="button" onClick={closeModal} className="text-slate-400 hover:text-slate-600 transition-colors">
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={sendMessage}>
                            <div className="p-6 space-y-5">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                                        Delivery Method <span className="text-red-500">*</span>
                                    </label>
                                    <div className="flex items-center gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                        <label className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.delivery_method.includes('sms')}
                                                onChange={() => toggleDelivery('sms')}
                                                className="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <span className="text-sm font-medium text-slate-700">Send via SMS</span>
                                        </label>
                                        <label className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.delivery_method.includes('email')}
                                                onChange={() => toggleDelivery('email')}
                                                className="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <span className="text-sm font-medium text-slate-700">Send via Email</span>
                                        </label>
                                    </div>
                                    {errors.delivery_method && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.delivery_method}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-2">Pre-made Templates</label>
                                    <select
                                        onChange={handleTemplateChange}
                                        defaultValue=""
                                        className="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm bg-white"
                                    >
                                        <option value="">-- Create Custom Message --</option>
                                        {messageTemplates?.map((template, idx) => (
                                            <option key={template.id || idx} value={idx}>{template.title}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                                        Message <span className="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        value={data.message}
                                        onChange={(e) => setData('message', e.target.value)}
                                        rows={5}
                                        required
                                        placeholder="Type your message here or select a template above..."
                                        className="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm"
                                    />
                                    {errors.message && <p className="text-rose-500 text-xs mt-1 font-semibold">{errors.message}</p>}
                                    <p className="text-xs text-slate-500 mt-2">
                                        The message will be sent directly to {student.guardian_name || 'the guardian'}.
                                    </p>
                                </div>
                            </div>

                            <div className="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50"
                                >
                                    <Send className="w-4 h-4" />
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

