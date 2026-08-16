import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { motion, useReducedMotion } from "framer-motion";
import { Eye, EyeOff, LoaderCircle, Lock, User } from "lucide-react";
import AuthBackground, { asset } from "@/Components/AuthBackground";
import BrandText from "@/Components/BrandText";

const fieldClass =
    "block w-full pl-11 pr-3 py-3 bg-white border border-slate-200 rounded-xl text-[15px] text-slate-900 placeholder-slate-400 shadow-sm transition-all duration-200 hover:border-slate-300 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600";

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

    const cardMotion = reduceMotion
        ? { initial: { opacity: 0 }, animate: { opacity: 1 }, transition: { duration: 0.15 } }
        : {
              initial: { opacity: 0, y: 24, scale: 0.98 },
              animate: { opacity: 1, y: 0, scale: 1 },
              transition: { duration: 0.45, ease: [0.22, 1, 0.36, 1] },
          };

    const formMotion = reduceMotion
        ? {}
        : {
              initial: "hidden",
              animate: "show",
              variants: {
                  hidden: {},
                  show: { transition: { staggerChildren: 0.06, delayChildren: 0.12 } },
              },
          };

    const itemMotion = reduceMotion
        ? {}
        : {
              variants: {
                  hidden: { opacity: 0, y: 10 },
                  show: { opacity: 1, y: 0, transition: { duration: 0.3, ease: [0.22, 1, 0.36, 1] } },
              },
          };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-10 sm:py-12">
            <AuthBackground />

            <Head title="Staff Login" />

            <motion.div
                className="auth-card relative z-10 w-full max-w-[26rem] overflow-hidden rounded-3xl border border-white/70 bg-white/95 px-7 py-9 shadow-[0_25px_60px_-15px_rgba(15,23,42,0.45)] backdrop-blur-sm sm:px-9 sm:py-10"
                {...cardMotion}
            >
                <div
                    className="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-700 via-blue-500 to-sky-400"
                    aria-hidden="true"
                />

                <div className="mb-8 flex flex-col items-center text-center">
                    <motion.div
                        className="mb-5 flex h-[5.25rem] w-[5.25rem] items-center justify-center overflow-hidden rounded-full border-[3px] border-white bg-blue-50 shadow-[0_8px_24px_rgba(37,99,235,0.18)]"
                        initial={reduceMotion ? false : { opacity: 0, scale: 0.85 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: reduceMotion ? 0.15 : 0.4, ease: [0.22, 1, 0.36, 1] }}
                    >
                        <img
                            src={logoSrc}
                            alt="i-Link College of Science and Technology logo"
                            className="h-[4.25rem] w-[4.25rem] object-contain"
                            onError={() => {
                                setLogoSrc((current) =>
                                    current.includes("brand_logo.png") ? asset("images/logo.png") : current
                                );
                            }}
                        />
                    </motion.div>

                    <p className="text-[11px] font-bold uppercase tracking-[0.22em] text-blue-700">
                        VioTrack
                    </p>
                    <h1 className="mt-2 text-[1.35rem] font-bold leading-snug tracking-tight text-slate-900 sm:text-2xl">
                        <BrandText>i-Link College of Science and Technology</BrandText>
                    </h1>
                    <p className="mt-2 text-sm font-medium text-slate-600">
                        Staff sign-in for Admin &amp; DSAS
                    </p>
                </div>

                {status && (
                    <div
                        className="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm font-medium text-emerald-800"
                        role="status"
                    >
                        {status}
                    </div>
                )}

                <motion.form onSubmit={submit} {...formMotion}>
                    <motion.div {...itemMotion}>
                        <label htmlFor="email" className="mb-1.5 ml-0.5 block text-sm font-semibold text-slate-800">
                            Email address
                        </label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <User className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="username"
                                value={data.email}
                                className={fieldClass}
                                placeholder="admin@ilink.edu.ph"
                                onChange={(e) => setData("email", e.target.value)}
                                onInput={(e) => setData("email", e.target.value)}
                                required
                                autoFocus
                                aria-invalid={errors.email ? "true" : undefined}
                                aria-describedby={errors.email ? "email-error" : undefined}
                            />
                        </div>
                        {errors.email && (
                            <div id="email-error" className="mt-1.5 text-sm font-medium text-red-600" role="alert">
                                {errors.email}
                            </div>
                        )}
                    </motion.div>

                    <motion.div className="mt-5" {...itemMotion}>
                        <label htmlFor="password" className="mb-1.5 ml-0.5 block text-sm font-semibold text-slate-800">
                            Password
                        </label>
                        <div className="relative">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <Lock className="h-5 w-5 text-blue-700 stroke-[2.25]" aria-hidden="true" />
                            </div>
                            <input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                name="password"
                                autoComplete="current-password"
                                value={data.password}
                                className={`${fieldClass} pr-11`}
                                placeholder="Enter your password"
                                onChange={(e) => setData("password", e.target.value)}
                                onInput={(e) => setData("password", e.target.value)}
                                required
                                aria-invalid={errors.password ? "true" : undefined}
                                aria-describedby={errors.password ? "password-error" : undefined}
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-blue-700 transition-colors"
                                onClick={() => setShowPassword(!showPassword)}
                                aria-label={showPassword ? "Hide password" : "Show password"}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-5 w-5 stroke-[2.25]" />
                                ) : (
                                    <Eye className="h-5 w-5 stroke-[2.25]" />
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <div id="password-error" className="mt-1.5 text-sm font-medium text-red-600" role="alert">
                                {errors.password}
                            </div>
                        )}
                    </motion.div>

                    <motion.div className="mt-5 flex items-center justify-between gap-3 px-0.5" {...itemMotion}>
                        <label htmlFor="remember" className="flex cursor-pointer items-center">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData("remember", e.target.checked)}
                                className="h-4 w-4 rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500 bg-white"
                            />
                            <span className="ml-2 text-sm font-medium text-slate-700">Remember me</span>
                        </label>

                        {canResetPassword && (
                            <Link
                                href={route("password.request")}
                                className="shrink-0 text-sm font-semibold text-blue-700 hover:text-blue-800 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        )}
                    </motion.div>

                    <motion.div className="mt-8" {...itemMotion}>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex w-full items-center justify-center gap-2 rounded-xl border border-transparent bg-blue-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? (
                                <>
                                    <LoaderCircle className={`h-4 w-4 ${reduceMotion ? "" : "animate-spin"}`} aria-hidden="true" />
                                    Signing in…
                                </>
                            ) : (
                                "Sign in"
                            )}
                        </button>
                    </motion.div>
                </motion.form>
            </motion.div>

            <motion.div
                className="relative z-10 mt-7 rounded-full bg-slate-900/35 px-4 py-1.5 text-center text-xs font-medium text-white backdrop-blur-sm"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: reduceMotion ? 0 : 0.3, duration: 0.35 }}
            >
                &copy; {new Date().getFullYear()}{" "}
                <BrandText>i-Link College of Science and Technology</BrandText>
            </motion.div>
        </div>
    );
}
