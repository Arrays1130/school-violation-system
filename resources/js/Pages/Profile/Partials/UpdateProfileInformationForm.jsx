import React from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Link, useForm, usePage } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import { User } from 'lucide-react';

export default function UpdateProfileInformation({ mustVerifyEmail, status, className = '' }) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: user.name,
        email: user.email,
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header className="mb-8">
                <div className="flex items-center gap-3 mb-2">
                    <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <User className="w-5 h-5" />
                    </div>
                    <h2 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">Profile Information</h2>
                </div>
                <p className="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400 pl-13">
                    Update your account's profile information and email address.
                </p>
            </header>

            <form onSubmit={submit} className="space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />

                    <TextInput
                        id="name"
                        className="mt-2 block w-full rounded-xl"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-2 block w-full rounded-xl"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="text-sm mt-2 text-slate-800 dark:text-slate-200">
                            Your email address is unverified.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="underline text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ml-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-xl border border-green-100">
                                A new verification link has been sent to your email address.
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4 pt-4">
                    <PrimaryButton disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200">
                        Save Changes
                    </PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                            <span className="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                            Saved.
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
