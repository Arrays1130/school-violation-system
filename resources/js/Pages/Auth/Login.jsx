import React from "react";
import { Head, Link, useForm } from "@inertiajs/react";

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <Head title="Log in — I-Link CST" />

            <div className="w-full sm:max-w-md mt-6 px-8 py-8 bg-white shadow-sm overflow-hidden sm:rounded-2xl border border-gray-200">
                <div className="mb-8 text-center flex flex-col items-center">
                    <div className="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center mb-4 border border-blue-100 shadow-sm">
                        <img className="w-10 h-10 object-contain" src="/brand_logo.png" alt="Logo"
                            onError={(e) => { e.target.style.display='none'; e.target.parentElement.innerHTML='<span class="text-blue-600 font-bold text-xl">IC</span>'; }} />
                    </div>
                    <h2 className="text-2xl font-bold text-gray-900 tracking-tight">Welcome back</h2>
                    <p className="text-sm text-gray-500 mt-2">Enter your credentials to access the system.</p>
                </div>

                {status && (
                    <div className="mb-5 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm font-medium text-green-700 flex items-center gap-2">
                        <svg className="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" /></svg>
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1.5" htmlFor="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="block w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder-gray-400 shadow-sm"
                            placeholder="admin@ilink.edu.ph"
                            autoComplete="username"
                            autoFocus
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && <p className="text-red-600 text-xs mt-1.5 font-medium">{errors.email}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1.5" htmlFor="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="block w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder-gray-400 shadow-sm"
                            placeholder="••••••••"
                            autoComplete="current-password"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {errors.password && <p className="text-red-600 text-xs mt-1.5 font-medium">{errors.password}</p>}
                    </div>

                    <div className="flex items-center justify-between">
                        <label className="flex items-center cursor-pointer group">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="w-4 h-4 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                            />
                            <span className="ml-2.5 text-sm text-gray-500 group-hover:text-gray-700 transition-colors">Remember me</span>
                        </label>

                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors"
                            >
                                Forgot password?
                            </Link>
                        )}
                    </div>

                    <button
                        type="submit"
                        className="w-full flex justify-center items-center gap-2 px-4 py-3 mt-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-sm shadow-blue-600/20"
                        disabled={processing}
                    >
                        {processing ? (
                            <>
                                <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                                Signing In...
                            </>
                        ) : "Sign In"}
                    </button>
                </form>
            </div>
            
            <div className="mt-8 text-center text-xs text-gray-400">
                &copy; {new Date().getFullYear()} I-Link College of Science and Technology
            </div>
        </div>
    );
}
