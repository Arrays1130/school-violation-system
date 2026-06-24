import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Edit3, Calendar, Clock, MapPin, FileCheck2, Save } from 'lucide-react';
import InputError from '@/Components/InputError';

export default function Edit({ auth, hearing }) {
    // We assume scheduled_at is a standard ISO string or parseable format from Laravel like "2023-10-15T14:30:00.000000Z"
    // Let's create proper date and time values
    const dateObj = new Date(hearing.scheduled_at);
    
    // Format YYYY-MM-DD
    const dateString = isNaN(dateObj) ? '' : dateObj.toISOString().split('T')[0];
    
    // Format HH:MM (24-hour)
    const timeString = isNaN(dateObj) ? '' : dateObj.toTimeString().split(' ')[0].substring(0, 5);

    const { data, setData, put, processing, errors } = useForm({
        scheduled_date: dateString,
        scheduled_time: timeString,
        location: hearing.venue || '',
        notes: hearing.notes || '',
        meeting_minutes: hearing.meeting_minutes || ''
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('hearings.update', hearing.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Edit Hearing Details</h2>}
        >
            <Head title={`Edit Hearing - Case #${hearing.case.id}`} />

            <div className="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-6 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex items-center gap-5">
                        <Link href={route('hearings.show', hearing.id)} className="w-10 h-10 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                <Edit3 className="w-3.5 h-3.5" />
                                Hearing Setup
                            </div>
                            <h2 className="text-2xl font-bold text-white tracking-tight">Edit Hearing Details</h2>
                            <div className="flex items-center gap-3 mt-1.5">
                                <span className="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-500/20 border border-indigo-500/30 text-indigo-200 text-[10px] font-bold uppercase tracking-wider">
                                    Case #{String(hearing.case.id).padStart(4, '0')}
                                </span>
                                <span className="text-indigo-100/70 text-xs font-medium">{hearing.case.violation?.title}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form onSubmit={submit} className="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
                    <div className="p-8 space-y-8">
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date *</label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <Calendar className="w-4.5 h-4.5" />
                                    </div>
                                    <input 
                                        type="date" 
                                        value={data.scheduled_date}
                                        onChange={e => setData('scheduled_date', e.target.value)}
                                        required 
                                        className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    />
                                    <InputError message={errors.scheduled_date} className="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time *</label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <Clock className="w-4.5 h-4.5" />
                                    </div>
                                    <input 
                                        type="time" 
                                        value={data.scheduled_time}
                                        onChange={e => setData('scheduled_time', e.target.value)}
                                        required 
                                        className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    />
                                    <InputError message={errors.scheduled_time} className="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div className="pt-6 border-t border-gray-100 dark:border-slate-800">
                            <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Venue *</label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <MapPin className="w-4.5 h-4.5" />
                                </div>
                                <input 
                                    type="text" 
                                    value={data.location}
                                    onChange={e => setData('location', e.target.value)}
                                    required 
                                    placeholder="e.g. Dean's Office, Room 101"
                                    className="w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                />
                                <InputError message={errors.location} className="mt-2" />
                            </div>
                        </div>

                        <div className="pt-6 border-t border-gray-100 dark:border-slate-800">
                            <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Notes / Instructions</label>
                            <textarea 
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                rows="3" 
                                placeholder="Add any special instructions or notes for this hearing..."
                                className="w-full p-4 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400"
                            ></textarea>
                            <InputError message={errors.notes} className="mt-2" />
                        </div>

                        <div className="pt-6 border-t border-gray-100 dark:border-slate-800">
                            <div className="flex items-center gap-2 mb-3">
                                <div className="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 shadow-sm">
                                    <FileCheck2 className="w-4.5 h-4.5" />
                                </div>
                                <label className="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Minutes of Meeting</label>
                            </div>
                            <textarea 
                                value={data.meeting_minutes}
                                onChange={e => setData('meeting_minutes', e.target.value)}
                                rows="8" 
                                className="w-full p-4 bg-gray-50/50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none placeholder-gray-400" 
                                placeholder="Record the proceedings, testimonies, and decisions here..."
                            ></textarea>
                            <InputError message={errors.meeting_minutes} className="mt-2" />
                        </div>

                    </div>

                    <div className="px-8 py-5 bg-gray-50/80 dark:bg-slate-800/80 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <Link href={route('hearings.show', hearing.id)} className="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-slate-200 transition-colors">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            disabled={processing}
                            className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-600/20 hover:shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
                        >
                            <Save className="w-4.5 h-4.5" />
                            Update Record
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
