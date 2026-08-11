import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Shield, Clock } from 'lucide-react';
import AuthBackground, { asset } from '@/Components/AuthBackground';
import BrandText from '@/Components/BrandText';

export default function Welcome({ studentRegistrationEnabled = false }) {
    const [logoSrc, setLogoSrc] = useState(asset('brand_logo.png'));

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-10">
            <AuthBackground />
            <Head title="Welcome" />

            <div className="auth-card relative z-10 w-full max-w-lg px-8 py-10 bg-white shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/80">
                <div className="mb-8 flex flex-col items-center text-center">
                    <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 overflow-hidden border-2 border-white shadow-sm">
                        <img
                            src={logoSrc}
                            alt="I-Link CST Logo"
                            className="h-16 w-16 object-contain"
                            onError={() => {
                                setLogoSrc((current) =>
                                    current.includes('brand_logo.png') ? asset('images/logo.png') : current
                                );
                            }}
                        />
                    </div>
                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">VioTrack</p>
                    <h1 className="mt-2 text-3xl font-bold text-gray-900 tracking-tight">
                        Student Violation Management
                    </h1>
                    <p className="mt-2 text-sm font-medium text-blue-700">
                        <BrandText>i-Link College of Science and Technology</BrandText>
                    </p>
                    <p className="mt-3 text-sm text-gray-600 leading-relaxed max-w-md">
                        Record cases, notify deans, and review department reports for DSAS in one place.
                    </p>
                </div>

                <div className="space-y-3">
                    <Link
                        href={route('login')}
                        className="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
                    >
                        <Shield className="w-4 h-4" aria-hidden="true" />
                        Admin / DSAS Login
                    </Link>
                    {studentRegistrationEnabled && (
                        <a
                            href={route('student.register.form')}
                            className="block text-center text-sm font-medium text-blue-700 hover:text-blue-800 pt-1"
                        >
                            Student registration
                        </a>
                    )}
                </div>

                <p className="mt-6 flex items-start gap-2 text-xs text-slate-500 leading-relaxed">
                    <Clock className="w-3.5 h-3.5 mt-0.5 shrink-0" aria-hidden="true" />
                    First visit may take a few seconds while the server wakes up.
                </p>
            </div>

            <p className="relative z-10 mt-8 text-center text-xs text-white drop-shadow-md">
                &copy; {new Date().getFullYear()}{' '}
                <BrandText>I-Link College of Science and Technology</BrandText>
            </p>
        </div>
    );
}
