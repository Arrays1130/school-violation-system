import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthBackground, { asset } from '@/Components/AuthBackground';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});
    const [logoSrc, setLogoSrc] = useState(asset('brand_logo.png'));

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />
            <Head title="Verify Email" />

            <div className="relative z-10 w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/60">
                <div className="mb-6 flex flex-col items-center text-center">
                    <img src={logoSrc} alt="Logo" className="mb-4 h-16 w-16 object-contain" onError={() => setLogoSrc(asset('images/logo.png'))} />
                    <h2 className="text-2xl font-bold text-gray-900">Verify Email</h2>
                    <p className="mt-2 text-sm text-gray-600">Thanks for signing up! Please verify your email by clicking the link we sent.</p>
                </div>

                {status === 'verification-link-sent' && (
                    <p className="mb-4 text-sm text-green-600 text-center">A new verification link has been sent to your email.</p>
                )}

                <form onSubmit={submit} className="flex flex-col gap-4">
                    <button type="submit" disabled={processing} className="w-full py-3 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-900 shadow-sm hover:bg-gray-50 disabled:opacity-50">
                        Resend Verification Email
                    </button>
                    <Link href={route('logout')} method="post" as="button" className="text-sm font-medium text-blue-700 hover:text-blue-800 text-center">
                        Log out
                    </Link>
                </form>
            </div>
        </div>
    );
}
