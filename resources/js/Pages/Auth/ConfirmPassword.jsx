import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import AuthBackground, { asset } from '@/Components/AuthBackground';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors } = useForm({ password: '' });
    const [logoSrc, setLogoSrc] = useState(asset('brand_logo.png'));

    const submit = (e) => {
        e.preventDefault();
        post(route('password.confirm'));
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />
            <Head title="Confirm Password" />

            <div className="relative z-10 w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/60">
                <div className="mb-6 flex flex-col items-center">
                    <img src={logoSrc} alt="Logo" className="mb-4 h-16 w-16 object-contain" onError={() => setLogoSrc(asset('images/logo.png'))} />
                    <h2 className="text-2xl font-bold text-gray-900">Confirm Password</h2>
                    <p className="mt-2 text-sm text-gray-600 text-center">This is a secure area. Please confirm your password before continuing.</p>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Password</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Lock className="h-5 w-5 text-blue-700 stroke-[2.25]" />
                            </div>
                            <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required autoFocus className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm" />
                        </div>
                        {errors.password && <p className="text-red-600 text-sm mt-1">{errors.password}</p>}
                    </div>
                    <button type="submit" disabled={processing} className="w-full py-3 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-900 shadow-sm hover:bg-gray-50 disabled:opacity-50">Confirm</button>
                </form>
            </div>
        </div>
    );
}
