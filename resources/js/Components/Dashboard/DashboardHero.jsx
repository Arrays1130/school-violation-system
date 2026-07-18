export default function DashboardHero({ badge, badgeIcon: BadgeIcon, title, description, children }) {
    return (
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-blue-950 p-6 sm:p-8 shadow-xl shadow-slate-900/10 border border-slate-800/80">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(37,99,235,0.18),_transparent_55%)]" />
            <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl" />

            <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    {badge && (
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                            {BadgeIcon && <BadgeIcon className="w-3.5 h-3.5" />}
                            {badge}
                        </div>
                    )}
                    <h1 className="text-3xl font-bold text-white tracking-tight">{title}</h1>
                    {description && (
                        <p className="text-blue-100/70 text-sm mt-2 max-w-xl leading-relaxed">{description}</p>
                    )}
                </div>
                {children && <div className="flex flex-wrap items-center gap-3 shrink-0">{children}</div>}
            </div>
        </div>
    );
}

export function Panel({ title, subtitle, icon: Icon, action, children, className = '' }) {
    return (
        <div className={`rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col ${className}`}>
            <div className="p-6 pb-4 flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight">{title}</h3>
                    {subtitle && <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{subtitle}</p>}
                </div>
                <div className="flex items-center gap-2">
                    {action}
                    {Icon && (
                        <div className="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-700 dark:text-blue-300">
                            <Icon className="w-5 h-5" />
                        </div>
                    )}
                </div>
            </div>
            <div className="flex-1 min-h-0 p-6">{children}</div>
        </div>
    );
}
