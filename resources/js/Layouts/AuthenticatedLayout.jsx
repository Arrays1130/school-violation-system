import { useState, useEffect, Fragment } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import Swal from 'sweetalert2';
import { Menu as HeadlessMenu, Transition } from '@headlessui/react';
import {
    LayoutDashboard, GraduationCap, FolderOpen, ShieldAlert, BookOpen,
    ClipboardList, BarChart3, Sparkles, Settings, LogOut, Menu, Bell, History,
    UserCircle, ChevronRight, X, Shield, Database, MessageSquare, FileWarning, CheckCircle2, Moon, Sun,
    Trash2, List, Sliders
} from 'lucide-react';

export default function Authenticated({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { openCasesCount, auth, flash } = usePage().props;
    
    const [isDarkMode, setIsDarkMode] = useState(() => {
        if (typeof window !== 'undefined') {
            return localStorage.getItem('theme') === 'dark' || 
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        }
        return false;
    });

    useEffect(() => {
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }, [isDarkMode]);

    useEffect(() => {
        if (flash?.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Success',
                text: flash.success,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: '!rounded-xl !shadow-xl !border !border-slate-100 dark:border-slate-800',
                }
            });
        }
        if (flash?.error) {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Proceed',
                text: flash.error,
                confirmButtonColor: '#e11d48',
                customClass: {
                    popup: '!rounded-xl !shadow-xl !border !border-slate-100 dark:border-slate-800',
                }
            });
        }
    }, [flash]);
    
    // Fallback to auth.user if user is not explicitly passed as a prop
    const currentUser = user || auth?.user || {};
    const [notifications, setNotifications] = useState(auth?.unreadNotifications || []);

    useEffect(() => {
        if (!currentUser?.id || !window.Echo) return;

        const channel = window.Echo.private(`App.Models.User.${currentUser.id}`);
        
        channel.notification((notification) => {
            setNotifications(prev => [notification, ...prev]);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'New Violation Logged',
                text: `${notification.student_name} - ${notification.violation_title}`,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                showCloseButton: true,
                customClass: {
                    popup: '!rounded-xl !shadow-xl !border !border-slate-100 dark:border-slate-800',
                    title: '!font-bold !text-slate-800 dark:text-slate-200 !text-sm',
                    htmlContainer: '!text-slate-500 dark:text-slate-400 !text-sm'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        });

        return () => {
            window.Echo.leave(`App.Models.User.${currentUser.id}`);
        };
    }, [currentUser?.id]);

    const markAsRead = (id) => {
        router.post(route('notifications.mark-as-read', id), {}, {
            preserveScroll: true,
            onSuccess: () => setNotifications(prev => prev.filter(n => n.id !== id))
        });
    };

    const markAllAsRead = () => {
        router.post(route('notifications.mark-all-as-read'), {}, {
            preserveScroll: true,
            onSuccess: () => setNotifications([])
        });
    };

    const navSections = [
        {
            label: 'Overview',
            items: [
                { name: 'Dashboard', href: currentUser?.role === 'dean' ? route('dean.dashboard') : route('dashboard'), icon: LayoutDashboard, activeCheck: () => route().current('dashboard') || route().current('dean.dashboard') },
            ],
        },
        {
            label: 'Management',
            items: [
                { 
                    name: 'Students', 
                    href: route('students.index'), 
                    icon: GraduationCap, 
                    isExternal: true, 
                    activeCheck: () => route().current('students.*'),
                    subItems: [
                        { name: 'All Students', href: route('students.index'), icon: List, isExternal: true, activeCheck: () => route().current('students.index') || route().current('students.show') || route().current('students.edit') || route().current('students.create') },
                        ...(currentUser?.role !== 'dean' ? [{ name: 'Trash Bin', href: route('students.trash'), icon: Trash2, isExternal: true, activeCheck: () => route().current('students.trash') }] : []),
                    ]
                },
                { 
                    name: 'Violation Cases', 
                    href: route('cases.index'), 
                    icon: FolderOpen, 
                    isExternal: true, 
                    activeCheck: () => route().current('cases.*') || route().current('hearings.*'),
                    badge: openCasesCount > 0 ? {
                        text: openCasesCount,
                        activeClass: 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400',
                        inactiveClass: 'bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400'
                    } : null,
                    subItems: [
                        { name: 'All Cases', href: route('cases.index'), icon: List, isExternal: true, activeCheck: () => route().current('cases.index') || route().current('cases.show') || route().current('cases.edit') || route().current('cases.create') || route().current('hearings.*') },
                        ...(currentUser?.role !== 'dean' ? [{ name: 'Trash Bin', href: route('cases.trash'), icon: Trash2, isExternal: true, activeCheck: () => route().current('cases.trash') }] : []),
                    ]
                },
                { name: 'Rules & Violations', href: route('violations.index'), icon: FileWarning, isExternal: true, activeCheck: () => route().current('violations.*') },
            ],
        },
        {
            label: 'Records',
            items: [
                { name: 'Handbook', href: route('handbooks.index'), icon: BookOpen, isExternal: true, activeCheck: () => route().current('handbooks.*') },
                { name: 'Meeting Minutes', href: route('meeting-minutes.index'), icon: ClipboardList, isExternal: true, activeCheck: () => route().current('meeting-minutes.*') },
                { name: 'Reports', href: route('reports.index'), icon: BarChart3, isExternal: true, activeCheck: () => route().current('reports.index') || route().current('reports.*') },
                { name: 'Record Retrieval', href: route('reports.retrieval'), icon: Database, isExternal: true, activeCheck: () => route().current('reports.retrieval') },
            ],
        },
        {
            label: 'System',
            items: [
                ...(currentUser?.role === 'super_admin' ? [{ name: 'User Accounts', href: route('users.index'), icon: UserCircle, isExternal: true, activeCheck: () => route().current('users.*') }] : []),
                ...(currentUser?.role === 'super_admin' || currentUser?.role === 'dean' ? [
                    { name: 'Audit Logs', href: route('reports.audit-logs'), icon: Shield, isExternal: true, activeCheck: () => route().current('reports.audit-logs') },
                    { name: 'Message Templates', href: route('message-templates.index'), icon: MessageSquare, isExternal: true, activeCheck: () => route().current('message-templates.*') },
                    { name: 'System Settings', href: route('settings.index'), icon: Sliders, isExternal: true, activeCheck: () => route().current('settings.*') }
                ] : []),
                { 
                    name: 'AI Assistant', 
                    href: route('ai-assistant.index'), 
                    icon: Sparkles, 
                    isExternal: true, 
                    activeCheck: () => route().current('ai-assistant.*'),
                    badge: {
                        text: 'Beta',
                        activeClass: 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400',
                        inactiveClass: 'bg-purple-100 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400'
                    }
                },
                { name: 'Settings', href: route('profile.edit'), icon: Settings, activeCheck: () => route().current('profile.*') },
            ],
        },
    ];

    const NavLink = ({ item }) => {
        let isActive = false;
        try {
            isActive = item.activeCheck ? item.activeCheck() : false;
        } catch (e) {
            isActive = window.location.pathname.startsWith(item.href);
        }
        const Icon = item.icon;
        const hasSubItems = item.subItems && item.subItems.length > 0;
        
        const className = `group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-bold transition-all duration-200
            ${isActive
                ? 'bg-blue-50/80 dark:bg-indigo-500/10 text-blue-700 dark:text-indigo-400 shadow-[0_2px_10px_rgb(59,130,246,0.06)] border border-blue-100/50 dark:border-indigo-500/20'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800/50 hover:text-blue-600 dark:hover:text-indigo-400'
            }`;

        const iconClassName = `w-[18px] h-[18px] transition-colors
            ${isActive 
                ? 'text-blue-600 dark:text-indigo-400' 
                : item.name === 'AI Assistant' 
                    ? 'text-purple-500 dark:text-purple-400 group-hover:text-purple-600 dark:group-hover:text-purple-300' 
                    : 'text-slate-400 dark:text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-indigo-400'
            }`;

        const badgeElement = item.badge ? (
            <span className={`min-w-[20px] h-5 px-1.5 flex items-center justify-center font-bold ml-auto ${
                item.name === 'Violation Cases' ? 'rounded-full text-[10px]' : 'rounded-md text-[9px] uppercase tracking-wider'
            } ${isActive ? item.badge.activeClass : item.badge.inactiveClass}`}>
                {item.badge.text}
            </span>
        ) : null;

        const mainLink = item.isExternal ? (
            <a href={item.href} className={className}>
                <Icon className={iconClassName} />
                <span>{item.name}</span>
                {badgeElement}
            </a>
        ) : (
            <Link href={item.href} className={className}>
                <Icon className={iconClassName} />
                <span>{item.name}</span>
                {badgeElement}
            </Link>
        );

        if (!hasSubItems) return mainLink;

        // Render parent + sub-items when active
        return (
            <div>
                {mainLink}
                {isActive && (
                    <div className="ml-3 mt-0.5 pl-3 border-l-2 border-blue-100 dark:border-indigo-500/20 space-y-0.5">
                        {item.subItems.map((sub) => {
                            let subActive = false;
                            try { subActive = sub.activeCheck ? sub.activeCheck() : false; } catch (e) {}
                            const SubIcon = sub.icon;
                            const subClass = `flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 ${
                                subActive
                                    ? 'text-blue-700 dark:text-indigo-400 bg-blue-50 dark:bg-indigo-500/10'
                                    : subActive && sub.name === 'Trash Bin'
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                            } ${sub.name === 'Trash Bin' && !subActive ? 'text-rose-500 dark:text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10' : ''}`;
                            return sub.isExternal ? (
                                <a key={sub.name} href={sub.href} className={subClass}>
                                    <SubIcon className="w-3.5 h-3.5 shrink-0" />
                                    {sub.name}
                                </a>
                            ) : (
                                <Link key={sub.name} href={sub.href} className={subClass}>
                                    <SubIcon className="w-3.5 h-3.5 shrink-0" />
                                    {sub.name}
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        );
    };

    return (
        <div className="h-screen w-screen overflow-hidden flex bg-slate-50/50 dark:bg-slate-800/50 dark:bg-slate-900 font-['Inter',sans-serif]">

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* ========== SIDEBAR ========== */}
            <aside className={`fixed inset-y-0 left-0 z-50 w-66 bg-white/95 dark:bg-slate-900/95 dark:bg-slate-900/95 backdrop-blur-xl border-r border-slate-100/80 dark:border-slate-800/80 dark:border-slate-800/80 flex flex-col transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-[4px_0_24px_rgb(0,0,0,0.02)] lg:shadow-none shrink-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                
                {/* Logo Header */}
                <div className="px-6 py-6 flex-shrink-0 border-b border-slate-100/80 dark:border-slate-800/80 dark:border-slate-800/80 flex items-center justify-between">
                    <div className="flex items-center gap-3.5">
                        <div className="relative flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200 dark:ring-white/10 shrink-0 p-1">
                            <img 
                                className="w-full h-full object-contain drop-shadow-sm" 
                                src={window.assetUrl ? `${window.assetUrl}brand_logo.png` : "/brand_logo.png"} 
                                alt="Logo" 
                                onError={(e) => { 
                                    e.target.parentElement.style.display = 'none'; 
                                    const fallback = document.getElementById('logo-fallback');
                                    if (fallback) fallback.style.display = 'flex'; 
                                }} 
                            />
                        </div>
                        <div id="logo-fallback" className="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 shrink-0" style={{ display: 'none' }}>
                            <Shield className="w-5.5 h-5.5 text-white" />
                        </div>
                        <div>
                            <p className="text-slate-900 dark:text-white font-extrabold text-[15px] tracking-tight leading-none uppercase">I-Link CST</p>
                        </div>
                    </div>
                    {/* Close button for mobile */}
                    <button onClick={() => setSidebarOpen(false)} className="lg:hidden text-slate-400 hover:text-slate-900 dark:text-white dark:hover:text-white transition-colors">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Nav */}
                <nav className="flex-1 overflow-y-auto px-4 py-4">
                    {navSections.map((section, idx) => (
                        <div key={section.label} className={`mb-4`}>
                            <p className="px-3 mb-1.5 text-[10px] font-bold text-gray-400 dark:text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em]">
                                {section.label}
                            </p>
                            <div className="space-y-0.5">
                                {section.items.map((item) => (
                                    <NavLink key={item.name} item={item} />
                                ))}
                            </div>
                        </div>
                    ))}
                </nav>

                {/* User Info */}
                <div className="p-4 border-t border-slate-100/80 dark:border-slate-800/80 dark:border-slate-800/80">
                    <div className="flex items-center gap-3.5 p-2 rounded-xl bg-slate-50/50 dark:bg-slate-800/50 border border-slate-100/50 dark:border-slate-800/50 dark:border-slate-700/50 hover:bg-slate-100/40 dark:hover:bg-slate-800 transition-colors">
                        <div className="w-9 h-9 rounded-xl bg-blue-50 dark:bg-indigo-500/20 border border-blue-100/80 dark:border-indigo-500/30 flex items-center justify-center text-blue-700 dark:text-indigo-400 font-extrabold text-xs shrink-0 shadow-sm">
                            {(currentUser?.name || 'U').substring(0, 1).toUpperCase()}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-slate-800 dark:text-white text-sm font-extrabold truncate leading-tight">{currentUser?.name}</p>
                            <div className="mt-1">
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-slate-400 text-[10px] font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1 text-left"
                                >
                                    <LogOut className="w-3 h-3" />
                                    Sign Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {/* ========== MAIN CONTENT ========== */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50/50 dark:bg-slate-800/50 dark:bg-slate-900">

                {/* Top Bar */}
                <header className="h-16 bg-white/80 dark:bg-slate-900/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 flex items-center justify-between px-6 sm:px-8 lg:px-10 sticky top-0 z-30 shrink-0">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors border border-slate-100 dark:border-slate-800"
                        >
                            <Menu className="w-5 h-5" />
                        </button>
                        {header && (
                            <div className="text-base font-bold text-slate-800 dark:text-white">
                                {header}
                            </div>
                        )}
                    </div>

                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setIsDarkMode(!isDarkMode)}
                            className="p-2 text-slate-400 dark:text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800/50 rounded-xl transition-colors border border-slate-100 dark:border-slate-800 focus:outline-none"
                            title="Toggle Theme"
                        >
                            {isDarkMode ? <Sun className="w-5 h-5" /> : <Moon className="w-5 h-5" />}
                        </button>

                        <HeadlessMenu as="div" className="relative">
                            <HeadlessMenu.Button className="p-2 text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-800/50 rounded-xl transition-colors border border-slate-100 dark:border-slate-800 relative focus:outline-none">
                                <Bell className="w-5 h-5" />
                                {notifications.length > 0 && (
                                    <span className="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                                )}
                            </HeadlessMenu.Button>
                            <Transition
                                as={Fragment}
                                enter="transition ease-out duration-200"
                                enterFrom="transform opacity-0 scale-95"
                                enterTo="transform opacity-100 scale-100"
                                leave="transition ease-in duration-150"
                                leaveFrom="transform opacity-100 scale-100"
                                leaveTo="transform opacity-0 scale-95"
                            >
                                <HeadlessMenu.Items className="absolute right-0 mt-2 w-80 sm:w-96 origin-top-right bg-white dark:bg-slate-800 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_-10px_rgba(0,0,0,0.5)] border border-slate-100 dark:border-slate-700 focus:outline-none overflow-hidden z-50">
                                    <div className="px-4 py-3 flex items-center justify-between border-b border-slate-100/80 dark:border-slate-800/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 dark:bg-slate-800/50">
                                        <h3 className="font-bold text-slate-800 dark:text-white text-sm">Notifications</h3>
                                        {notifications.length > 0 && (
                                            <button 
                                                onClick={markAllAsRead}
                                                className="text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:underline"
                                            >
                                                Mark all as read
                                            </button>
                                        )}
                                    </div>
                                    <div className="max-h-[350px] overflow-y-auto">
                                        {notifications.length === 0 ? (
                                            <div className="p-6 text-center text-slate-400 flex flex-col items-center">
                                                <div className="w-12 h-12 bg-slate-50/80 dark:bg-slate-800/80 rounded-full flex items-center justify-center mb-3">
                                                    <CheckCircle2 className="w-6 h-6 text-slate-300 dark:text-slate-500 dark:text-slate-400" />
                                                </div>
                                                <p className="text-sm font-medium">All caught up!</p>
                                                <p className="text-xs mt-0.5">No new notifications</p>
                                            </div>
                                        ) : (
                                            <div className="divide-y divide-slate-100 dark:divide-slate-700/50">
                                                {notifications.map((notification) => (
                                                    <div key={notification.id} className="p-4 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700/50 transition-colors group relative flex gap-3">
                                                        <div className="w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                                            <Bell className="w-4 h-4" />
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <Link href={notification.data?.url || '#'} className="block focus:outline-none">
                                                                <p className="text-sm text-slate-800 dark:text-slate-200 font-semibold truncate pr-6">
                                                                    {notification.data?.student_name}
                                                                </p>
                                                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                                                                    {notification.data?.violation_title}
                                                                </p>
                                                            </Link>
                                                        </div>
                                                        <button 
                                                            onClick={() => markAsRead(notification.id)}
                                                            className="absolute top-4 right-4 p-1.5 text-slate-300 dark:text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 opacity-0 group-hover:opacity-100 transition-all rounded-md hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                                            title="Mark as read"
                                                        >
                                                            <CheckCircle2 className="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </HeadlessMenu.Items>
                            </Transition>
                        </HeadlessMenu>
                    </div>
                </header>

                {/* Page Content */}
                <main className="flex-1 overflow-y-auto p-6 sm:p-8 lg:p-10 no-scrollbar">
                    <div className="max-w-7xl mx-auto">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
