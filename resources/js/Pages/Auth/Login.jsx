import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { motion, useReducedMotion } from "framer-motion";
import { Eye, EyeOff, Lock, User } from "lucide-react";
import AuthBackground, { asset } from "@/Components/AuthBackground";
import BrandText from "@/Components/BrandText";

export default function Login({ status, canResetPassword }) {
    const reduceMotion = useReducedMotion();
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);
    const [logoSrc, setLogoSrc] = useState(asset("brand_logo.png"));

    const submit = (e) => {
        e.preventDefault();
        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />

            <Head title="Staff Login" />

            <motion.div
                className="auth-card relative z-10 w-full sm:max-w-md px-8 py-10 bg-white shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/80"
                initial={reduceMotion ? { opacity: 0 } : { opacity: 0, y: 20, scale: 0.98 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                transition={{ duration: reduceMotion ? 0.15 : 0.4, ease: [0.22, 1, 0.36, 1] }}
            >
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
                    <h2 className="text-3xl font-bold text-gray-900">Welcome Back</h2>
                    <p className="text-sm font-medium text-blue-700 mt-1">
                        <BrandText>i-Link College of Science and Technology</BrandText>
                    </p>
                </div>

                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                <form onSubmit={submit}>
                    <div>
                        <label htmlFor="email" className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Email Address</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <User className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="username"
                                value={data.email}
                                className="block w-full pl-10 pr-3 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 shadow-sm transition-all"
                                placeholder="admin@ilink.edu.ph"
                                onChange={(e) => setData("email", e.target.value)}
                                onInput={(e) => setData("email", e.target.value)}
                                required
                                autoFocus
                            />
                        </div>
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div className="mt-5">
                        <label htmlFor="password" className="block text-sm font-semibold text-gray-700 ml-1 mb-1">Password</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Lock className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                name="password"
                                autoComplete="current-password"
                                value={data.password}
                                className="block w-full pl-10 pr-10 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400 shadow-sm transition-all"
                                placeholder="••••••••"
                                onChange={(e) => setData("password", e.target.value)}
                                onInput={(e) => setData("password", e.target.value)}
                                required
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onClick={() => setShowPassword(!showPassword)}
                                aria-label={showPassword ? 'Hide password' : 'Show password'}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-5 w-5 text-gray-700 stroke-[2.25] hover:text-blue-700 transition-colors" />
                                ) : (
                                    <Eye className="h-5 w-5 text-gray-700 stroke-[2.25] hover:text-blue-700 transition-colors" />
                                )}
                            </button>
                        </div>
                        {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                    </div>

                    <div className="flex items-center justify-between mt-5 px-1">
                        <label htmlFor="remember" className="flex items-center">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData("remember", e.target.checked)}
                                className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 bg-white"
                            />
                            <span className="ml-2 text-sm text-gray-700 font-medium">Remember me</span>
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
                            className="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 shadow-blue-600/20"
                        >
                            {processing ? "Signing In..." : "Sign In"}
                        </button>
                    </div>

                    <p className="mt-6 text-center text-sm text-gray-500">
                        <Link href={route('home')} className="hover:text-gray-700">
                            Back to home
                        </Link>
                    </p>
                </form>
            </motion.div>

            <motion.div
                className="relative z-10 mt-8 text-center text-xs text-white drop-shadow-md"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: reduceMotion ? 0 : 0.25, duration: 0.35 }}
            >
                &copy; {new Date().getFullYear()}{' '}
                <BrandText>I-Link College of Science and Technology</BrandText>
            </motion.div>
        </div>
    );
}
