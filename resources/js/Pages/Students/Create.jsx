import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft, UserPlus, User, Type, GraduationCap, Users, Calendar,
    ChevronDown, Building2, Mail, AtSign, Phone, ShieldAlert, UserCheck, Save,
} from 'lucide-react';
import Breadcrumbs from '@/Components/Breadcrumbs';
import { YEAR_LEVELS, SECTIONS, DEPARTMENTS, buildAcademicYears } from '@/constants/studentForm';

const academicYears = buildAcademicYears();

const inputClass =
    'w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-slate-400 dark:placeholder-slate-500';

const selectClass =
    'w-full pl-10 pr-10 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer';

const labelClass = 'block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2';

export default function Create({ auth, currentAcademicYear }) {
    const { data, setData, post, processing, errors } = useForm({
        full_name: '',
        year_level: '',
        section: '',
        academic_year: currentAcademicYear || '',
        department: '',
        email: '',
        phone: '',
        guardian_name: '',
        guardian_phone: '',
        guardian_email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('students.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Student Registration</h2>}
        >
            <Head title="Student Registration" />

            <div className="max-w-4xl mx-auto space-y-8 py-8 px-4 sm:px-6 lg:px-8">
                <Breadcrumbs items={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Students', href: route('students.index') },
                    { label: 'Add Student' },
                ]} />
                <div className="vt-page-hero">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]" />
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl" />
                    <div className="relative flex items-center gap-5">
                        <Link
                            href={route('students.index')}
                            className="w-10 h-10 rounded-xl bg-slate-900/10 border border-slate-600/80 flex items-center justify-center text-white/80 hover:text-white hover:bg-slate-700/80 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900/10 border border-slate-600/80 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                <UserPlus className="w-3.5 h-3.5" />
                                New Enrollment
                            </div>
                            <h2 className="text-2xl font-bold text-white tracking-tight">Student Registration</h2>
                            <p className="text-slate-400 text-xs mt-1.5">Register a new student profile in the system.</p>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl ring-1 ring-slate-100 shadow-sm overflow-hidden">
                    <form onSubmit={submit}>
                        <div className="p-8 space-y-10">
                            <div className="space-y-6">
                                <div className="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-slate-800">
                                    <div className="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-900">
                                        <User className="w-4 h-4" />
                                    </div>
                                    <h3 className="text-sm font-bold text-slate-200 uppercase tracking-wider">Student Identification</h3>
                                </div>

                                <div>
                                    <label className={labelClass}>Full Name *</label>
                                    <div className="relative">
                                        <Type className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <input
                                            type="text"
                                            value={data.full_name}
                                            onChange={(e) => setData('full_name', e.target.value)}
                                            placeholder="Last Name, First Name M.I."
                                            required
                                            autoFocus
                                            className={inputClass}
                                        />
                                    </div>
                                    {errors.full_name && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.full_name}</p>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className={labelClass}>Year Level *</label>
                                        <div className="relative">
                                            <GraduationCap className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <select
                                                value={data.year_level}
                                                onChange={(e) => setData('year_level', e.target.value)}
                                                required
                                                className={selectClass}
                                            >
                                                <option value="">Select Level...</option>
                                                {YEAR_LEVELS.map((year) => (
                                                    <option key={year} value={year}>{year}</option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                        </div>
                                        {errors.year_level && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.year_level}</p>}
                                    </div>

                                    <div>
                                        <label className={labelClass}>Section *</label>
                                        <div className="relative">
                                            <Users className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <select
                                                value={data.section}
                                                onChange={(e) => setData('section', e.target.value)}
                                                required
                                                className={selectClass}
                                            >
                                                <option value="">Select Section...</option>
                                                {SECTIONS.map((sec) => (
                                                    <option key={sec} value={sec}>Section {sec}</option>
                                                ))}
                                            </select>
                                            <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                        </div>
                                        {errors.section && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.section}</p>}
                                    </div>
                                </div>

                                <div>
                                    <label className={labelClass}>Academic Year</label>
                                    <div className="relative">
                                        <Calendar className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <select
                                            value={data.academic_year}
                                            onChange={(e) => setData('academic_year', e.target.value)}
                                            className={selectClass}
                                        >
                                            {academicYears.map((year) => (
                                                <option key={year} value={year}>{year}</option>
                                            ))}
                                        </select>
                                        <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    {errors.academic_year && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.academic_year}</p>}
                                    <p className="text-xs text-slate-500 mt-1.5">Defaults to the global system setting.</p>
                                </div>

                                <div>
                                    <label className={labelClass}>Department *</label>
                                    <div className="relative">
                                        <Building2 className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <select
                                            value={data.department}
                                            onChange={(e) => setData('department', e.target.value)}
                                            required
                                            className={selectClass}
                                        >
                                            <option value="">Select Department...</option>
                                            {DEPARTMENTS.map((dept) => (
                                                <option key={dept.value} value={dept.value}>{dept.label}</option>
                                            ))}
                                        </select>
                                        <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                    </div>
                                    {errors.department && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.department}</p>}
                                </div>
                            </div>

                            <div className="space-y-6 pt-8 border-t border-gray-100 dark:border-slate-800">
                                <div className="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-slate-800">
                                    <div className="w-8 h-8 rounded-lg bg-indigo-50  text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-900">
                                        <Mail className="w-4 h-4" />
                                    </div>
                                    <h3 className="text-sm font-bold text-slate-200 uppercase tracking-wider">Contact Information</h3>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className={labelClass}>Email Address *</label>
                                        <div className="relative">
                                            <AtSign className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <input
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                placeholder="student@link.edu.ph"
                                                required
                                                className={inputClass}
                                            />
                                        </div>
                                        {errors.email && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.email}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>Phone Number</label>
                                        <div className="relative">
                                            <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <input
                                                type="text"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                placeholder="09XX XXX XXXX"
                                                className={inputClass}
                                            />
                                        </div>
                                        {errors.phone && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.phone}</p>}
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-6 pt-8 border-t border-gray-100 dark:border-slate-800">
                                <div className="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-slate-800">
                                    <div className="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-100 dark:border-rose-800">
                                        <ShieldAlert className="w-4 h-4" />
                                    </div>
                                    <h3 className="text-sm font-bold text-slate-200 uppercase tracking-wider">Emergency Contact</h3>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className={labelClass}>Guardian Name</label>
                                        <div className="relative">
                                            <UserCheck className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <input
                                                type="text"
                                                value={data.guardian_name}
                                                onChange={(e) => setData('guardian_name', e.target.value)}
                                                placeholder="Legal Guardian Name"
                                                className={inputClass}
                                            />
                                        </div>
                                        {errors.guardian_name && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.guardian_name}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>Guardian Phone</label>
                                        <div className="relative">
                                            <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <input
                                                type="text"
                                                value={data.guardian_phone}
                                                onChange={(e) => setData('guardian_phone', e.target.value)}
                                                placeholder="+63 912 345 6789"
                                                className={inputClass}
                                            />
                                        </div>
                                        {errors.guardian_phone && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.guardian_phone}</p>}
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className={labelClass}>Guardian Email</label>
                                        <div className="relative">
                                            <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                            <input
                                                type="email"
                                                value={data.guardian_email}
                                                onChange={(e) => setData('guardian_email', e.target.value)}
                                                placeholder="e.g. guardian@example.com"
                                                className={inputClass}
                                            />
                                        </div>
                                        {errors.guardian_email && <p className="text-rose-500 text-xs mt-1.5 font-bold">{errors.guardian_email}</p>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="px-8 py-5 bg-gray-50/80 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-700 flex items-center justify-between">
                            <Link href={route('students.index')} className="text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/20 hover:shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
                            >
                                <UserPlus className="w-4 h-4" />
                                Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

