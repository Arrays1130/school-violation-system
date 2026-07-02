import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Mail } from "lucide-react";
import AuthBackground, { asset } from "@/Components/AuthBackground";

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({ email: "" });
    const [logoSrc, setLogoSrc] = useState(asset("brand_logo.png"));

    const submit = (e) => {
        e.preventDefault();
        post(route("password.email"));
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />

            <Head title="Forgot Password" />

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
                    <p className="mt-2 text-sm font-medium text-gray-600 leading-relaxed text-center">
                        Forgot your password? No problem. Just let us know your email address and we will email you a
                        password reset link that will allow you to choose a new one.
                    </p>
                </div>

                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                <form onSubmit={submit}>
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
                                className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 shadow-sm transition-all"
                                placeholder="admin@ilink.edu.ph"
                                onChange={(e) => setData("email", e.target.value)}
                                required
                                autoFocus
                            />
                        </div>
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div className="mt-8">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all uppercase tracking-wide disabled:opacity-50"
                        >
                            Email Password Reset Link
                        </button>
                    </div>

                    <div className="mt-6 text-center">
                        <Link
                            href={route("login")}
                            className="text-sm font-medium text-blue-700 hover:text-blue-800 transition-colors"
                        >
                            &larr; Back to login
                        </Link>
                    </div>
                </form>
            </div>

            <div className="relative z-10 mt-8 text-center text-xs text-white drop-shadow-md">
                &copy; {new Date().getFullYear()} I-Link College of Science and Technology
            </div>
        </div>
    );
}
