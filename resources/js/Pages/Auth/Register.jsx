import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock, Mail, User } from 'lucide-react';
import AuthBackground, { asset } from '@/Components/AuthBackground';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [logoSrc, setLogoSrc] = useState(asset('brand_logo.png'));

    const submit = (e) => {
        e.preventDefault();
        post(route('register'));
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />
            <Head title="Register" />

            <div className="relative z-10 w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/60">
                <div className="mb-6 flex flex-col items-center">
                    <img src={logoSrc} alt="Logo" className="mb-4 h-16 w-16 object-contain" onError={() => setLogoSrc(asset('images/logo.png'))} />
                    <h2 className="text-2xl font-bold text-gray-900">Create Account</h2>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Name</label>
                        <div className="relative">
                            <User className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-blue-700 stroke-[2.25]" />
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required autoFocus className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-900 shadow-sm" />
                        </div>
                        {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Email</label>
                        <div className="relative">
                            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-blue-700 stroke-[2.25]" />
                            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-900 shadow-sm" />
                        </div>
                        {errors.email && <p className="text-red-600 text-sm mt-1">{errors.email}</p>}
                    </div>
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Password</label>
                        <div className="relative">
                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-blue-700 stroke-[2.25]" />
                            <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-900 shadow-sm" />
                        </div>
                        {errors.password && <p className="text-red-600 text-sm mt-1">{errors.password}</p>}
                    </div>
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Confirm Password</label>
                        <div className="relative">
                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-blue-700 stroke-[2.25]" />
                            <input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} required className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-900 shadow-sm" />
                        </div>
                    </div>
                    <button type="submit" disabled={processing} className="w-full py-3 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-900 shadow-sm hover:bg-gray-50 disabled:opacity-50">Register</button>
                    <div className="text-center">
                        <Link href={route('login')} className="text-sm font-medium text-blue-700 hover:text-blue-800">&larr; Back to login</Link>
                    </div>
                </form>
            </div>
        </div>
    );
}
