import React, { useRef, useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthBackground, { asset } from "@/Components/AuthBackground";
import RecaptchaField from "@/Components/RecaptchaField";
import BrandText from "@/Components/BrandText";

export default function DeanLogin({ status, recaptchaSiteKey }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
        "g-recaptcha-response": "",
    });

    const [logoSrc, setLogoSrc] = useState(asset("brand_logo.png"));
    const recaptchaRef = useRef(null);

    const submit = (e) => {
        e.preventDefault();
        post(route("dean.login.post"), {
            onFinish: () => reset("password"),
            onError: () => recaptchaRef.current?.reset(),
        });
    };

    return (
        <div className="auth-screen flex min-h-[100dvh] w-full flex-col items-center justify-center px-4 py-8">
            <AuthBackground />

            <Head title="Dean Log in" />

            <div className="relative z-10 w-full sm:max-w-md px-8 py-10 bg-white/75 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-[2rem] border border-white/60">
                <div className="mb-8 text-center flex flex-col items-center">
                    <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4 overflow-hidden border-2 border-white shadow-sm">
                        <img
                            className="h-16 w-16 object-contain"
                            src={logoSrc}
                            alt="School Logo"
                            onError={() => {
                                setLogoSrc((current) =>
                                    current.includes("brand_logo.png") ? asset("images/logo.png") : current
                                );
                            }}
                        />
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 uppercase tracking-tight">Dean Portal</h1>
                    <p className="text-sm text-gray-600 mt-1">College Department Access</p>
                </div>

                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                <form onSubmit={submit}>
                    <div>
                        <label className="block font-medium text-sm text-gray-700" htmlFor="email">
                            Academic Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full bg-white/80 border border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm px-4 py-2.5 transition-all"
                            autoComplete="username"
                            autoFocus
                            onChange={(e) => setData("email", e.target.value)}
                        />
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div className="mt-4">
                        <label className="block font-medium text-sm text-gray-700" htmlFor="password">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full bg-white/80 border border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm px-4 py-2.5 transition-all"
                            autoComplete="current-password"
                            onChange={(e) => setData("password", e.target.value)}
                        />
                        {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                    </div>

                    <div className="mt-4 flex items-center justify-between">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData("remember", e.target.checked)}
                                className="rounded bg-white border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            />
                            <span className="ml-2 text-sm text-gray-600">Stay signed in</span>
                        </label>

                        <Link
                            href={route("password.request")}
                            className="underline text-sm text-blue-700 hover:text-blue-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <RecaptchaField
                        ref={recaptchaRef}
                        siteKey={recaptchaSiteKey}
                        onChange={(token) => setData("g-recaptcha-response", token)}
                        error={errors["g-recaptcha-response"]}
                    />

                    <div className="flex items-center justify-end mt-6">
                        <button
                            className="w-full flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 shadow-sm shadow-blue-600/20"
                            disabled={processing}
                        >
                            {processing ? "Signing In..." : "Access Portal"}
                        </button>
                    </div>
                </form>
            </div>

            <div className="relative z-10 mt-8 text-center text-xs text-white/90 drop-shadow">
                &copy; {new Date().getFullYear()}{' '}
                <BrandText>I-Link College of Science and Technology</BrandText>
            </div>
        </div>
    );
}
