import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";

export default function DeanLogin({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route("dean.login.post"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <div className="min-h-screen bg-gray-100 flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <Head title="Dean Log in" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl overflow-hidden sm:rounded-xl border border-gray-200">
                <div className="mb-8 text-center flex flex-col items-center">
                    <div className="bg-gray-50 p-3 rounded-full mb-4 border border-gray-100">
                        <img className="w-16 h-16 object-contain" src="/brand_logo.png" alt="School Logo" onError={(e) => { e.target.src='https://ui-avatars.com/api/?name=Dean+Portal&background=4338ca&color=fff&rounded=true'; }} />
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 uppercase tracking-tight">Dean Portal</h1>
                    <p className="text-sm text-gray-500 mt-1">College Department Access</p>
                </div>

                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                <form onSubmit={submit}>
                    <div>
                        <label className="block font-medium text-sm text-gray-700" htmlFor="email">Academic Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full bg-white border border-gray-300 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm px-4 py-2.5 transition-all"
                            autoComplete="username"
                            isFocused={true}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && <div className="text-red-600 text-sm mt-1">{errors.email}</div>}
                    </div>

                    <div className="mt-4">
                        <label className="block font-medium text-sm text-gray-700" htmlFor="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full bg-white border border-gray-300 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm px-4 py-2.5 transition-all"
                            autoComplete="current-password"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {errors.password && <div className="text-red-600 text-sm mt-1">{errors.password}</div>}
                    </div>

                    <div className="block mt-4 flex items-center justify-between">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="rounded bg-white border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            />
                            <span className="ml-2 text-sm text-gray-600">Stay signed in</span>
                        </label>

                        <Link
                            href={route('password.request')}
                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <div className="flex items-center justify-end mt-6">
                        <button
                            className="w-full flex justify-center items-center px-4 py-3 mt-4 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 shadow-sm shadow-blue-600/20"
                            disabled={processing}
                        >
                            {processing ? "Signing In..." : "Access Portal"}
                        </button>
                    </div>
                </form>
            </div>
            
            <div className="mt-8 text-center text-xs text-gray-400">
                &copy; {new Date().getFullYear()} I-Link College of Science and Technology
            </div>
        </div>
    );
}
