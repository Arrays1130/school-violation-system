import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Eye, EyeOff, Lock, User } from "lucide-react";

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-cover bg-center relative" 
             style={{ backgroundImage: `url('${window.assetUrl ? window.assetUrl + "images/bg_user.jpg" : "/images/bg_user.jpg"}')` }}>
            
            <Head title="Log in" />

            {/* Overlay */}
            <div className="absolute inset-0 bg-black/20 z-0"></div>

            <div className="z-10 w-full sm:max-w-md px-8 py-10 bg-white/40 dark:bg-slate-900/40 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/40">
                <div className="mb-6 flex flex-col items-center">
                    <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 overflow-hidden border-2 border-white shadow-sm">
                        <img src={window.assetUrl ? window.assetUrl + "brand_logo.png" : "/brand_logo.png"} alt="Logo" className="w-16 h-16 object-contain" onError={(e) => { e.target.style.display = 'none'; e.target.nextSibling.style.display = 'block'; }} />
                        <span className="hidden text-xl font-bold text-blue-600">Logo</span>
                    </div>
                    <h2 className="text-3xl font-bold text-gray-900 dark:text-white">Welcome Back</h2>
                    <p className="text-sm font-medium text-blue-700 mt-1">Access your academic portal</p>
                </div>

                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                <form onSubmit={submit}>
                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-gray-700 dark:text-slate-300 ml-1 mb-1">Email Address</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <User className="h-5 w-5 text-gray-500 dark:text-slate-400" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="block w-full pl-10 pr-3 py-2.5 bg-white/60 dark:bg-slate-900/60 border border-white/50 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-500 backdrop-blur-sm shadow-sm transition-all"
                                placeholder="admin@ilink.edu.ph"
                                onChange={(e) => setData("email", e.target.value)}
                                required
                                autoFocus
                            />
                        </div>
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div className="mt-5">
                        <label htmlFor="password" className="block text-sm font-semibold text-gray-700 dark:text-slate-300 ml-1 mb-1">Password</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Lock className="h-5 w-5 text-gray-500 dark:text-slate-400" />
                            </div>
                            <input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                name="password"
                                value={data.password}
                                className="block w-full pl-10 pr-10 py-2.5 bg-white/60 dark:bg-slate-900/60 border border-white/50 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-500 backdrop-blur-sm shadow-sm transition-all"
                                placeholder="••••••••"
                                onChange={(e) => setData("password", e.target.value)}
                                required
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onClick={() => setShowPassword(!showPassword)}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-5 w-5 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:text-slate-300" />
                                ) : (
                                    <Eye className="h-5 w-5 text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:text-slate-300" />
                                )}
                            </button>
                        </div>
                        {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                    </div>

                    <div className="flex items-center justify-between mt-5 px-1">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData("remember", e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 bg-white/70 dark:bg-slate-900/70"
                            />
                            <span className="ml-2 text-sm text-gray-700 dark:text-slate-300 font-medium">Remember me</span>
                        </label>

                        {canResetPassword && (
                            <Link
                                href={route("password.request")}
                                className="text-sm font-medium text-blue-700 hover:text-blue-800 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        )}
                    </div>

                    <div className="mt-8">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-gray-900 dark:text-white bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all uppercase tracking-wide"
                        >
                            Sign In
                        </button>
                    </div>


                </form>
            </div>
        </div>
    );
}
