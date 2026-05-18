import { useState } from 'react';
import { Link } from '@inertiajs/react';
import {
    LayoutDashboard, GraduationCap, FolderOpen, ShieldAlert, BookOpen,
    ClipboardList, BarChart3, Sparkles, Settings, LogOut, Menu, Bell, History,
    UserCircle, ChevronRight, X
} from 'lucide-react';

export default function Authenticated({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

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
                { name: 'Violation Cases', href: route('cases.index'), icon: FolderOpen, isExternal: true, activeCheck: () => route().current('cases.*') || route().current('hearings.*') },
                { name: 'Rules & Violations', href: route('violations.index'), icon: ShieldAlert, isExternal: true, activeCheck: () => route().current('violations.*') },
            ],
        },
        {
            label: 'Records',
            items: [
                { name: 'Handbook', href: '/handbooks', icon: BookOpen, isExternal: true, activeCheck: () => window.location.pathname.startsWith('/handbooks') },
                { name: 'Meeting Minutes', href: route('meeting-minutes.index'), icon: ClipboardList, isExternal: true, activeCheck: () => route().current('meeting-minutes.*') },
                { name: 'Reports', href: route('reports.index'), icon: BarChart3, isExternal: true, activeCheck: () => route().current('reports.index') || route().current('reports.*') },
            ],
        },
        {
            label: 'System',
            items: [
                { name: 'AI Assistant', href: route('ai-assistant.index'), icon: Sparkles, isExternal: true, activeCheck: () => route().current('ai-assistant.*') },
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
        
        const className = `flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
            ${isActive
                ? 'bg-blue-50 text-blue-700'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
            }`;

        if (item.isExternal) {
            return (
                <a href={item.href} className={className}>
                    <Icon className={`w-4 h-4 flex-shrink-0 ${isActive ? 'text-blue-600' : 'text-gray-400'}`} />
                    <span>{item.name}</span>
                    {isActive && <ChevronRight className="w-4 h-4 ml-auto opacity-50" />}
                </a>
            );
        }
        
        return (
            <Link
                href={item.href}
                className={className}
            >
                <Icon className={`w-4 h-4 flex-shrink-0 ${isActive ? 'text-blue-600' : 'text-gray-400'}`} />
                <span>{item.name}</span>
                {isActive && <ChevronRight className="w-4 h-4 ml-auto opacity-50" />}
            </Link>
        );
    };

    return (
        <div className="h-screen w-screen overflow-hidden flex bg-gray-50 font-['Inter',sans-serif]">

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* ========== SIDEBAR ========== */}
            <aside className={`fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 lg:translate-x-0 lg:static lg:inset-0 shadow-lg lg:shadow-none ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                
                {/* Logo Header */}
                <div className="px-6 py-6 flex-shrink-0 border-b border-gray-100 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <img src="/brand_logo.png" alt="Logo" className="w-8 h-8 rounded object-cover bg-gray-50 p-0.5 border border-gray-200" onError={(e) => { e.target.style.display = 'none'; }} />
                        <div>
                            <p className="text-gray-900 font-bold text-base tracking-tight leading-none">I-Link CST</p>
                            <p className="text-gray-500 text-[10px] font-medium uppercase tracking-wider mt-1 leading-none">Disciplinary System</p>
                        </div>
                    </div>
                    {/* Close button for mobile */}
                    <button onClick={() => setSidebarOpen(false)} className="lg:hidden text-gray-400 hover:text-gray-900">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Nav */}
                <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                    {navSections.map((section) => (
                        <div key={section.label}>
                            <p className="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                                {section.label}
                            </p>
                            <div className="space-y-1">
                                {section.items.map((item) => (
                                    <NavLink key={item.name} item={item} />
                                ))}
                            </div>
                        </div>
                    ))}
                </nav>

                {/* User Info */}
                <div className="p-4 border-t border-gray-100">
                    <div className="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 transition-colors">
                        <div className="w-8 h-8 rounded bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs flex-shrink-0">
                            {user.name.substring(0, 1).toUpperCase()}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-gray-900 text-sm font-medium truncate">{user.name}</p>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="text-gray-500 text-xs hover:text-gray-900 transition-colors block text-left font-medium"
                            >
                                Sign Out
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            {/* ========== MAIN CONTENT ========== */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">

                {/* Top Bar */}
                <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100 transition-colors"
                        >
                            <Menu className="w-5 h-5" />
                        </button>
                        {header && (
                            <div className="text-lg font-semibold text-gray-900">
                                {header}
                            </div>
                        )}
                    </div>

                    <div className="flex items-center gap-4">
                        <button className="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-full transition-colors relative">
                            <Bell className="w-5 h-5" />
                            <span className="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                        </button>
                    </div>
                </header>

                {/* Page Content */}
                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    <div className="max-w-7xl mx-auto">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
