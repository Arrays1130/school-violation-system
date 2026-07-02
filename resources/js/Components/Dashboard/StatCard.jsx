import { Link } from '@inertiajs/react';
import { ArrowUpRight, TrendingUp } from 'lucide-react';
import { Card, CardContent } from '@/Components/ui/card';

const accents = {
    indigo: {
        bar: 'from-indigo-400 to-indigo-600',
        icon: 'text-indigo-600 dark:text-indigo-400',
        iconBg: 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-100/80 dark:border-indigo-500/20',
        glow: 'from-indigo-400/40 to-indigo-600/20',
    },
    blue: {
        bar: 'from-blue-400 to-indigo-600',
        icon: 'text-blue-600 dark:text-blue-400',
        iconBg: 'bg-blue-50 dark:bg-blue-500/10 border-blue-100/80 dark:border-blue-500/20',
        glow: 'from-blue-400/35 to-indigo-500/20',
    },
    rose: {
        bar: 'from-rose-400 to-rose-600',
        icon: 'text-rose-600 dark:text-rose-400',
        iconBg: 'bg-rose-50 dark:bg-rose-500/10 border-rose-100/80 dark:border-rose-500/20',
        glow: 'from-rose-400/30 to-rose-600/15',
    },
    amber: {
        bar: 'from-amber-400 to-amber-600',
        icon: 'text-amber-600 dark:text-amber-400',
        iconBg: 'bg-amber-50 dark:bg-amber-500/10 border-amber-100/80 dark:border-amber-500/20',
        glow: 'from-amber-400/30 to-amber-600/15',
    },
    emerald: {
        bar: 'from-emerald-400 to-emerald-600',
        icon: 'text-emerald-600 dark:text-emerald-400',
        iconBg: 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100/80 dark:border-emerald-500/20',
        glow: 'from-emerald-400/30 to-emerald-600/15',
    },
};

export default function StatCard({ label, value, icon: Icon, trend, href, accent = 'indigo' }) {
    const a = accents[accent] || accents.indigo;
    const content = (
        <Card className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-sm hover:shadow-lg hover:shadow-indigo-500/10 hover:-translate-y-0.5 hover:border-indigo-800 transition-all duration-300 h-full group">
            <div className={`absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r ${a.bar} opacity-0 group-hover:opacity-100 transition-opacity duration-500`} />
            <CardContent className="p-6 flex flex-col h-full justify-between relative z-10">
                <div className="flex items-start justify-between mb-4">
                    <h3 className="text-[13px] font-bold uppercase tracking-wider text-slate-400">{label}</h3>
                    <div className={`p-3 rounded-2xl border shadow-sm ${a.iconBg} ${a.icon}`}>
                        <Icon className="w-5 h-5" strokeWidth={2.5} />
                    </div>
                </div>
                <div className="flex items-end justify-between mt-auto">
                    <span className="text-4xl font-black tracking-tight text-slate-100">
                        {typeof value === 'number' ? value.toLocaleString() : value}
                    </span>
                    {trend && (
                        <div
                            className={`flex items-center px-2 py-1 rounded-full text-xs font-bold border ${
                                trend.direction === 'up'
                                    ? trend.isPositive
                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        : 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400'
                                    : 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400'
                            }`}
                        >
                            {trend.direction === 'up' ? (
                                <ArrowUpRight className="w-3.5 h-3.5 mr-0.5" />
                            ) : trend.direction === 'down' ? (
                                <TrendingUp className="w-3.5 h-3.5 mr-0.5 rotate-180" />
                            ) : null}
                            {trend.percentage}%
                        </div>
                    )}
                </div>
            </CardContent>
            <div className={`absolute -bottom-8 -right-8 w-28 h-28 bg-gradient-to-br ${a.glow} rounded-full blur-3xl opacity-50 pointer-events-none`} />
        </Card>
    );

    if (href) {
        return (
            <Link href={href} className="block h-full outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-2xl">
                {content}
            </Link>
        );
    }
    return content;
}

export function DeanStatCard({ label, value, icon: Icon, badge, accent = 'indigo' }) {
    const a = accents[accent] || accents.indigo;
    return (
        <div className="group relative bg-slate-900 rounded-2xl p-6 border border-slate-800 shadow-sm hover:shadow-lg hover:shadow-indigo-500/10 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
            <div className={`absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r ${a.bar} opacity-0 group-hover:opacity-100 transition-opacity`} />
            <div className={`absolute -right-10 -top-10 w-32 h-32 bg-gradient-to-br ${a.glow} rounded-full blur-2xl opacity-60`} />
            <div className="flex items-start justify-between relative z-10 mb-4">
                <div className={`p-3 rounded-2xl border ${a.iconBg} ${a.icon}`}>
                    <Icon className="w-5 h-5" strokeWidth={2.5} />
                </div>
                {badge}
            </div>
            <p className="text-[13px] font-bold text-slate-400 uppercase tracking-wider mb-1">{label}</p>
            <p className="text-4xl font-black text-slate-100 tracking-tight">{value}</p>
        </div>
    );
}
