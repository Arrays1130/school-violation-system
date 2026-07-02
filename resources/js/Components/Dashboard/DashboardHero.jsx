export default function DashboardHero({ badge, badgeIcon: BadgeIcon, title, description, children }) {
    return (
        <div className="vt-page-hero">
            <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    {badge && (
                        <div className="vt-page-hero-badge mb-3">
                            {BadgeIcon && <BadgeIcon className="w-3.5 h-3.5" />}
                            {badge}
                        </div>
                    )}
                    <h1 className="vt-page-hero-title">{title}</h1>
                    {description && <p className="vt-page-hero-desc">{description}</p>}
                </div>
                {children && <div className="flex flex-wrap items-center gap-3 shrink-0">{children}</div>}
            </div>
        </div>
    );
}

export function Panel({ title, subtitle, icon: Icon, action, children, className = '' }) {
    return (
        <div className={`rounded-2xl border border-slate-800 bg-slate-900 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col ${className}`}>
            <div className="p-6 pb-4 flex items-start justify-between gap-4 border-b border-slate-800">
                <div>
                    <h3 className="text-lg font-bold text-slate-100 tracking-tight">{title}</h3>
                    {subtitle && <p className="text-sm text-slate-400 mt-0.5">{subtitle}</p>}
                </div>
                <div className="flex items-center gap-2">
                    {action}
                    {Icon && (
                        <div className="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                            <Icon className="w-5 h-5" />
                        </div>
                    )}
                </div>
            </div>
            <div className="flex-1 min-h-0 p-6">{children}</div>
        </div>
    );
}
