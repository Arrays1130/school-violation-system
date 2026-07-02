import { Head, Link } from '@inertiajs/react';

export default function Guest({ children, title = 'I-Link CST' }) {
    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-indigo-50 dark:bg-slate-950 px-4">
            <Head title={title} />
            <div className="w-full max-w-md">
                <div className="text-center mb-6">
                    <Link href={route('login')} className="text-xl font-bold text-indigo-900 dark:text-white">
                        I-Link CST
                    </Link>
                </div>
                <div className="bg-slate-900 rounded-2xl shadow-xl border border-indigo-100 dark:border-slate-700 p-8">
                    {children}
                </div>
            </div>
        </div>
    );
}
