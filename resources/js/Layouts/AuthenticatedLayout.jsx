import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard, GraduationCap, FolderOpen, ShieldAlert, BookOpen,
    ClipboardList, BarChart3, Sparkles, Settings, LogOut, Menu, Bell, History,
    UserCircle, ChevronRight, X, Shield, Database
} from 'lucide-react';

export default function Authenticated({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { openCasesCount } = usePage().props;

    const navSections = [
        {
            label: 'Overview',
            items: [
                { name: 'Dashboard', href: user?.role === 'dean' ? route('dean.dashboard') : route('dashboard'), icon: LayoutDashboard, activeCheck: () => route().current('dashboard') || route().current('dean.dashboard') },
            ],
        },
        {
            label: 'Management',
            items: [
                { name: 'Students', href: route('students.index'), icon: GraduationCap, isExternal: true, activeCheck: () => route().current('students.*') },
                { 
                    name: 'Violation Cases', 
                    href: route('cases.index'), 
                    icon: FolderOpen, 
                    isExternal: true, 
                    activeCheck: () => route().current('cases.*') || route().current('hearings.*'),
                    badge: openCasesCount > 0 ? {
                        text: openCasesCount,
                        activeClass: 'bg-white/20 text-white',
                        inactiveClass: 'bg-rose-100 text-rose-700'
                    } : null
                },
                { name: 'Rules & Violations', href: route('violations.index'), icon: ShieldAlert, isExternal: true, activeCheck: () => route().current('violations.*') },
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
                ...(user?.role === 'super_admin' ? [{ name: 'User Accounts', href: route('users.index'), icon: UserCircle, isExternal: true, activeCheck: () => route().current('users.*') }] : []),
                ...(user?.role === 'super_admin' || user?.role === 'admin' ? [{ name: 'Audit Logs', href: route('reports.audit-logs'), icon: Shield, isExternal: true, activeCheck: () => route().current('reports.audit-logs') }] : []),
                { 
                    name: 'AI Assistant', 
                    href: route('ai-assistant.index'), 
                    icon: Sparkles, 
                    isExternal: true, 
                    activeCheck: () => route().current('ai-assistant.*'),
                    badge: {
                        text: 'Beta',
                        activeClass: 'bg-white/20 text-white',
                        inactiveClass: 'bg-purple-100 text-purple-700'
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
        
        const className = `group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all duration-200
            ${isActive
                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-650/20'
                : 'text-gray-600 hover:bg-slate-50 hover:text-indigo-600'
            }`;

        const iconClassName = `w-[18px] h-[18px] transition-colors
            ${isActive 
                ? 'text-white' 
                : item.name === 'AI Assistant' 
                    ? 'text-purple-500 group-hover:text-purple-600' 
                    : 'text-gray-400 group-hover:text-indigo-600'
            }`;

        const badgeElement = item.badge ? (
            <span className={`min-w-[20px] h-5 px-1.5 flex items-center justify-center font-bold ml-auto ${
                item.name === 'Violation Cases' ? 'rounded-full text-[10px]' : 'rounded-md text-[9px] uppercase tracking-wider'
            } ${isActive ? item.badge.activeClass : item.badge.inactiveClass}`}>
                {item.badge.text}
            </span>
        ) : null;

        if (item.isExternal) {
            return (
                <a href={item.href} className={className}>
                    <Icon className={iconClassName} />
                    <span>{item.name}</span>
                    {badgeElement}
                </a>
            );
        }
        
        return (
            <Link
                href={item.href}
                className={className}
            >
                <Icon className={iconClassName} />
                <span>{item.name}</span>
                {badgeElement}
            </Link>
        );
    };

    return (
        <div className="h-screen w-screen overflow-hidden flex bg-slate-50/50 font-['Inter',sans-serif]">

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* ========== SIDEBAR ========== */}
            <aside className={`fixed inset-y-0 left-0 z-50 w-66 bg-white border-r border-slate-100 flex flex-col transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none shrink-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                
                {/* Logo Header */}
                <div className="px-6 py-6 flex-shrink-0 border-b border-slate-100/80 flex items-center justify-between">
                    <div className="flex items-center gap-3.5">
                        <img 
                            className="w-9 h-9 object-contain shrink-0 rounded-lg shadow-sm" 
                            src={window.assetUrl ? `${window.assetUrl}brand_logo.png` : "/brand_logo.png"} 
                            alt="Logo" 
                            onError={(e) => { 
                                e.target.style.display = 'none'; 
                                if (e.target.nextSibling) e.target.nextSibling.style.display = 'flex'; 
                            }} 
                        />
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-650 flex items-center justify-center text-white shadow-md shadow-indigo-500/10 shrink-0" style={{ display: 'none' }}>
                            <Shield className="w-5.5 h-5.5 text-white" />
                        </div>
                        <div>
                            <p className="text-slate-900 font-extrabold text-[15px] tracking-tight leading-none uppercase">I-Link CST</p>
                            <p className="text-slate-400 text-[9px] font-bold uppercase tracking-wider mt-1.5">Disciplinary System</p>
                        </div>
                    </div>
                    {/* Close button for mobile */}
                    <button onClick={() => setSidebarOpen(false)} className="lg:hidden text-slate-400 hover:text-slate-900 transition-colors">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Nav */}
                <nav className="flex-1 overflow-y-auto px-4.5 py-6 no-scrollbar">
                    {navSections.map((section) => (
                        <div key={section.label} className="mb-6">
                            <p className="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
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
                <div className="p-4 border-t border-slate-100/80">
                    <div className="flex items-center gap-3.5 p-2 rounded-xl bg-slate-50 border border-slate-100/50 hover:bg-slate-100/40 transition-colors">
                        <div className="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/80 flex items-center justify-center text-indigo-700 font-extrabold text-xs shrink-0 shadow-sm">
                            {user.name.substring(0, 1).toUpperCase()}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-slate-800 text-sm font-extrabold truncate leading-tight">{user.name}</p>
                            <div className="mt-1">
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-slate-400 text-[10px] font-bold uppercase tracking-wide hover:text-indigo-600 transition-colors flex items-center gap-1 text-left"
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
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50/50">

                {/* Top Bar */}
                <header className="h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-6 sm:px-8 lg:px-10 sticky top-0 z-30 shrink-0">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-50 transition-colors border border-slate-100"
                        >
                            <Menu className="w-5 h-5" />
                        </button>
                        {header && (
                            <div className="text-base font-bold text-slate-800">
                                {header}
                            </div>
                        )}
                    </div>

                    <div className="flex items-center gap-4">
                        <button className="p-2 text-slate-400 hover:text-slate-500 hover:bg-slate-50 rounded-xl transition-colors border border-slate-100 relative">
                            <Bell className="w-5 h-5" />
                            <span className="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                        </button>
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
