import React, { useRef } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import { ShieldCheck } from "lucide-react";
import AuthBackground from "@/Components/AuthBackground";
import RecaptchaField from "@/Components/RecaptchaField";

export default function RecaptchaChallenge({ recaptchaSiteKey, userName }) {
    const { data, setData, post, processing, errors } = useForm({
        "g-recaptcha-response": "",
    });

    const recaptchaRef = useRef(null);
    const canContinue = Boolean(data["g-recaptcha-response"]);

    const submit = (e) => {
        e.preventDefault();
        post(route("recaptcha.verify"), {
            onError: () => recaptchaRef.current?.reset(),
        });
    };

    return (
        <div className="auth-screen relative flex min-h-[100dvh] w-full items-center justify-center px-4 py-8">
            <AuthBackground />
            <Head title="Security Verification" />

            <div className="fixed inset-0 z-20 bg-slate-900/55 backdrop-blur-sm" aria-hidden="true" />

            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="recaptcha-title"
                className="relative z-30 w-full max-w-md rounded-[2rem] border border-white/70 bg-white/95 p-8 shadow-2xl backdrop-blur-md"
            >
                <div className="mb-6 flex flex-col items-center text-center">
                    <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                        <ShieldCheck className="h-8 w-8" />
                    </div>
                    <h1 id="recaptcha-title" className="text-2xl font-bold text-gray-900">
                        Confirm you're not a robot
                    </h1>
                    <p className="mt-2 text-sm text-gray-600">
                        Welcome back, <span className="font-semibold text-gray-900">{userName}</span>.
                        Check the box below to continue to your dashboard.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <RecaptchaField
                        ref={recaptchaRef}
                        siteKey={recaptchaSiteKey}
                        onChange={(token) => setData("g-recaptcha-response", token)}
                        error={errors["g-recaptcha-response"]}
                    />

                    <button
                        type="submit"
                        disabled={processing || !canContinue}
                        className="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {processing ? "Verifying..." : "Continue"}
                    </button>
                </form>

                <button
                    type="button"
                    onClick={() => router.post(route("logout"))}
                    className="mt-4 w-full text-center text-sm font-medium text-gray-500 hover:text-gray-700"
                >
                    Sign out instead
                </button>
            </div>
        </div>
    );
}
