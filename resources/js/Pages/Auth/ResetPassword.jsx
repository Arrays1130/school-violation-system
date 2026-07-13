import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Lock, Mail } from "lucide-react";
import AuthBackground, { asset } from "@/Components/AuthBackground";
import BrandText from "@/Components/BrandText";

export default function ResetPassword({ email, token }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: "",
        password_confirmation: "",
    });
    const [logoSrc, setLogoSrc] = useState(asset("brand_logo.png"));

    const submit = (e) => {
        e.preventDefault();
        post(route("password.store"));
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />

            <Head title="Reset Password" />

            <div className="relative z-10 w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/60">
                <div className="mb-6 flex flex-col items-center">
                    <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 overflow-hidden border-2 border-white shadow-sm">
                        <img
                            src={logoSrc}
                            alt="I-Link CST Logo"
                            className="h-16 w-16 object-contain"
                            onError={() => {
                                setLogoSrc((current) =>
                                    current.includes("brand_logo.png") ? asset("images/logo.png") : current
                                );
                            }}
                        />
                    </div>
                    <h2 className="text-2xl font-bold text-gray-900">Reset Password</h2>
                    <p className="mt-2 text-sm font-medium text-gray-600 text-center">
                        Enter your new password below to complete the reset process.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-gray-700 ml-1 mb-1">
                            Email Address
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Mail className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                onChange={(e) => setData("email", e.target.value)}
                                className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm transition-all"
                                required
                                autoFocus
                            />
                        </div>
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div>
                        <label htmlFor="password" className="block text-sm font-semibold text-gray-700 ml-1 mb-1">
                            Password
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Lock className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                onChange={(e) => setData("password", e.target.value)}
                                className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm transition-all"
                                required
                            />
                        </div>
                        {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                    </div>

                    <div>
                        <label htmlFor="password_confirmation" className="block text-sm font-semibold text-gray-700 ml-1 mb-1">
                            Confirm Password
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Lock className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) => setData("password_confirmation", e.target.value)}
                                className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm transition-all"
                                required
                            />
                        </div>
                        {errors.password_confirmation && (
                            <div className="text-red-600 text-sm mt-1">{errors.password_confirmation}</div>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all uppercase tracking-wide disabled:opacity-50"
                    >
                        Reset Password
                    </button>

                    <div className="text-center">
                        <Link href={route("login")} className="text-sm font-medium text-blue-700 hover:text-blue-800 transition-colors">
                            &larr; Back to login
                        </Link>
                    </div>
                </form>
            </div>

            <div className="relative z-10 mt-8 text-center text-xs text-white drop-shadow-md">
                &copy; {new Date().getFullYear()}{' '}
                <BrandText>I-Link College of Science and Technology</BrandText>
            </div>
        </div>
    );
}
