import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, User, Save, Shield, Key, Mail, Edit3 } from 'lucide-react';
import InputError from '@/Components/InputError';

export default function Edit({ auth, userRecord }) {
    const { data, setData, put, processing, errors } = useForm({
        name: userRecord.name || '',
        email: userRecord.email || '',
        password: '',
        password_confirmation: '',
        role: userRecord.role || 'dean',
        department: userRecord.department || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('users.update', userRecord.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">Edit User</h2>}
        >
            <Head title={`Edit User - ${userRecord.name}`} />

            <div className="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* Modern Header */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    
                    <div className="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div className="flex items-center gap-5">
                            <Link href={route('users.index')} className="w-12 h-12 rounded-xl bg-white/10 dark:bg-slate-900/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 dark:bg-slate-900/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                                <ArrowLeft className="w-5.5 h-5.5" />
                            </Link>
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                                    <Edit3 className="w-3.5 h-3.5" />
                                    Update Profile
                                </div>
                                <h2 className="text-3xl font-bold text-white tracking-tight">{userRecord.name}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden">
                    <form onSubmit={submit} className="p-8 space-y-6">
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Profile Information */}
                            <div className="space-y-6">
                                <div>
                                    <h3 className="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-5 border-b border-slate-100 dark:border-slate-800 pb-2">Profile Details</h3>
                                    
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Full Name</label>
                                            <div className="relative">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <User className="w-4 h-4" />
                                                </div>
                                                <input 
                                                    type="text" 
                                                    value={data.name}
                                                    onChange={e => setData('name', e.target.value)}
                                                    className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                    required 
                                                />
                                            </div>
                                            <InputError message={errors.name} className="mt-2" />
                                        </div>

                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Email Address</label>
                                            <div className="relative">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <Mail className="w-4 h-4" />
                                                </div>
                                                <input 
                                                    type="email" 
                                                    value={data.email}
                                                    onChange={e => setData('email', e.target.value)}
                                                    className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                    required 
                                                />
                                            </div>
                                            <InputError message={errors.email} className="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Access & Security */}
                            <div className="space-y-6">
                                <div>
                                    <h3 className="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-5 border-b border-slate-100 dark:border-slate-800 pb-2">Access & Security</h3>
                                    
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">System Role</label>
                                            <div className="relative">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <Shield className="w-4 h-4" />
                                                </div>
                                                <select 
                                                    value={data.role}
                                                    onChange={e => setData('role', e.target.value)}
                                                    className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none"
                                                >
                                                    <option value="super_admin">Super Admin</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="dean">Dean</option>
                                                </select>
                                            </div>
                                            <InputError message={errors.role} className="mt-2" />
                                        </div>

                                        {data.role === 'dean' && (
                                            <div>
                                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Department (For Deans)</label>
                                                <input 
                                                    type="text" 
                                                    value={data.department}
                                                    onChange={e => setData('department', e.target.value)}
                                                    placeholder="e.g. CITE"
                                                    className="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                />
                                                <InputError message={errors.department} className="mt-2" />
                                            </div>
                                        )}

                                        <div className="pt-2">
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Change Password (Optional)</label>
                                            <div className="relative mb-3">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <Key className="w-4 h-4" />
                                                </div>
                                                <input 
                                                    type="password" 
                                                    value={data.password}
                                                    onChange={e => setData('password', e.target.value)}
                                                    placeholder="Leave blank to keep current password"
                                                    className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                />
                                            </div>
                                            <InputError message={errors.password} className="mt-2" />

                                            {data.password && (
                                                <>
                                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Confirm New Password</label>
                                                    <div className="relative">
                                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                            <Key className="w-4 h-4" />
                                                        </div>
                                                        <input 
                                                            type="password" 
                                                            value={data.password_confirmation}
                                                            onChange={e => setData('password_confirmation', e.target.value)}
                                                            className="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                                            required={!!data.password}
                                                        />
                                                    </div>
                                                    <InputError message={errors.password_confirmation} className="mt-2" />
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                            <Link href={route('users.index')} className="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 transition-all">
                                Cancel
                            </Link>
                            <button 
                                type="submit" 
                                disabled={processing}
                                className="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-500 hover:-translate-y-0.5 transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Save className="w-4 h-4" />
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
