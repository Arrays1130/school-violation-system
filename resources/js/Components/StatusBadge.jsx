import { getCaseStatusConfig, resolveCaseStatus } from '@/lib/caseStatus';

export default function StatusBadge({ status, item, variant = 'pill', className = '' }) {
    const resolved = resolveCaseStatus(item ?? status);
    const cfg = getCaseStatusConfig(resolved);
    const Icon = cfg.icon;

    if (variant === 'compact') {
        return (
            <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold border whitespace-nowrap ${cfg.bg} ${cfg.text} ${cfg.border} ${className}`}>
                <Icon className="w-3 h-3 shrink-0" aria-hidden="true" />
                {cfg.shortLabel ?? cfg.label}
            </span>
        );
    }

    return (
        <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase border ${cfg.border} ${cfg.bg} ${cfg.text} ${className}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot} shadow-sm`} aria-hidden="true" />
            {cfg.label}
        </span>
    );
}
