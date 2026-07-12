import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    GraduationCap,
    FolderOpen,
    UserCircle,
    PlusCircle,
} from 'lucide-react';
import { motion } from 'framer-motion';

export default function MobileBottomNav({ user }) {
    const { url } = usePage();
    const isDean = user?.role === 'dean';
    const homeHref = isDean ? route('dean.dashboard') : route('dashboard');
    const homeActive = route().current('dashboard') || route().current('dean.dashboard');
    const tabs = [
        { label: 'Home', icon: LayoutDashboard, href: homeHref, active: homeActive },
        { label: 'Cases', icon: FolderOpen, href: route('cases.index'), active: route().current('cases.*') },
        { label: 'Students', icon: GraduationCap, href: route('students.index'), active: route().current('students.*') },
        { label: 'Profile', icon: UserCircle, href: route('profile.edit'), active: route().current('profile.*') },
    ];

    return (
        <div className="lg:hidden fixed bottom-0 left-0 right-0 z-50 px-4 pb-4 pt-2 pointer-events-none">
            <motion.nav
                initial={{ y: 100 }}
                animate={{ y: 0 }}
                className="pointer-events-auto flex items-center justify-around h-[4.5rem] px-2 rounded-[2rem] shadow-2xl relative bg-white/90 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/80 dark:border-slate-700/80"
            >
                {tabs.slice(0, 2).map((tab) => (
                    <NavTab key={tab.label} tab={tab} />
                ))}
                <div className="relative -top-8">
                    <Link
                        href={route('cases.create')}
                        className="flex items-center justify-center bg-indigo-600 text-white p-4 rounded-full shadow-lg shadow-indigo-500/40 border-4 border-white dark:border-slate-900"
                        aria-label="Log new violation"
                    >
                        <PlusCircle className="w-7 h-7" />
                    </Link>
                </div>
                {tabs.slice(2).map((tab) => (
                    <NavTab key={tab.label} tab={tab} />
                ))}
            </motion.nav>
        </div>
    );
}

function NavTab({ tab }) {
    const Icon = tab.icon;
    return (
        <Link
            href={tab.href}
            className="flex flex-col items-center justify-center relative flex-1 min-w-0 py-1"
            aria-label={tab.label}
            aria-current={tab.active ? 'page' : undefined}
        >
            <Icon className={`w-5 h-5 transition-all ${tab.active ? 'text-indigo-600 dark:text-indigo-400 scale-110' : 'text-slate-400'}`} />
            <span className={`text-[10px] font-bold mt-0.5 truncate max-w-full ${tab.active ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'}`}>
                {tab.label}
            </span>
        </Link>
    );
}
