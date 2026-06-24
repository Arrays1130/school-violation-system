import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, UserCog } from 'lucide-react';

export default function Edit({ auth, mustVerifyEmail, status }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Profile</h2>}
        >
            <Head title="Profile" />

            <div className="py-8">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950 px-8 py-8 shadow-2xl shadow-indigo-900/20 border border-indigo-900/50">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                        <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                        
                        <div className="relative flex flex-col md:flex-row md:items-center gap-5 z-10">
                            <Link 
                                href={route('dashboard')} 
                                className="w-12 h-12 rounded-2xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-1"
                            >
                                <ArrowLeft className="w-5 h-5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                    <UserCog className="w-3.5 h-3.5" />
                                    Account Settings
                                </div>
                                <h1 className="text-3xl font-black text-white tracking-tight">Profile Settings</h1>
                                <p className="text-indigo-200/70 text-sm mt-2 max-w-xl leading-relaxed font-medium">Manage your institutional credentials and security preferences.</p>
                            </div>
                        </div>
                    </div>

                    {/* Forms Section */}
                    <div className="grid grid-cols-1 gap-8">
                        <div className="p-8 sm:p-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl shadow-xl shadow-slate-200/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                className="max-w-xl"
                            />
                        </div>

                        <div className="p-8 sm:p-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl shadow-xl shadow-slate-200/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                            <UpdatePasswordForm className="max-w-xl" />
                        </div>

                        <div className="p-8 sm:p-10 bg-rose-50 dark:bg-rose-900/20/50 backdrop-blur-xl shadow-xl shadow-rose-100/40 rounded-3xl border border-rose-100">
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
