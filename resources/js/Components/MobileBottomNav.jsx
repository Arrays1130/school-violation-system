import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { 
    LayoutDashboard, 
    Users, 
    Bell, 
    UserCircle,
    PlusCircle
} from 'lucide-react';
import { motion } from 'framer-motion';

export default function MobileBottomNav() {
    const { url } = usePage();

    const tabs = [
        { label: 'Home', icon: LayoutDashboard, href: '/dashboard', active: url === '/dashboard' },
        { label: 'Students', icon: Users, href: '/students', active: url.startsWith('/students') },
        { label: 'Center', icon: PlusCircle, href: '#', isCenter: true },
        { label: 'Alerts', icon: Bell, href: '/reports', active: url.startsWith('/reports') },
        { label: 'Profile', icon: UserCircle, href: '/profile', active: url.startsWith('/profile') },
    ];

    return (
        <div className="lg:hidden fixed bottom-0 left-0 right-0 z-50 px-6 pb-6 pt-2 pointer-events-none">
            <motion.nav 
                initial={{ y: 100 }}
                animate={{ y: 0 }}
                className="glass-ios pointer-events-auto flex items-center justify-around h-20 px-4 rounded-[2.5rem] shadow-2xl relative"
            >
                {tabs.map((tab, idx) => {
                    const Icon = tab.icon;
                    
                    if (tab.isCenter) {
                        return (
                            <div key={idx} className="relative -top-10">
                                <motion.button
                                    whileTap={{ scale: 0.9 }}
                                    className="bg-purple-600 text-white p-4 rounded-full shadow-lg shadow-purple-500/40 border-4 border-white dark:border-gray-900"
                                >
                                    <PlusCircle className="w-8 h-8" />
                                </motion.button>
                            </div>
                        );
                    }

                    return (
                        <Link 
                            key={idx}
                            href={tab.href}
                            className="flex flex-col items-center justify-center relative flex-1"
                        >
                            <Icon className={`w-6 h-6 transition-all duration-300 ${tab.active ? 'text-purple-600 scale-110' : 'text-gray-400'}`} />
                            <span className={`text-[10px] font-bold mt-1 tracking-tighter transition-all duration-300 ${tab.active ? 'text-purple-600 opacity-100' : 'text-gray-400 opacity-70'}`}>
                                {tab.label}
                            </span>
                            {tab.active && (
                                <motion.div 
                                    layoutId="mobile-tab-pill"
                                    className="absolute -top-1 w-1 h-1 bg-purple-600 rounded-full"
                                />
                            )}
                        </Link>
                    );
                })}
            </motion.nav>
        </div>
    );
}
