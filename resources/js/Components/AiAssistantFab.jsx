import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';

export default function AiAssistantFab() {
    const { aiAssistant } = usePage().props;

    if (!aiAssistant?.canUse || !aiAssistant?.url) {
        return null;
    }

    if (route().current('ai-assistant.*')) {
        return null;
    }

    return (
        <Link
            href={aiAssistant.url}
            className="fixed bottom-[5.75rem] right-4 lg:bottom-8 lg:right-8 z-40 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-violet-600 via-indigo-600 to-cyan-500 px-4 py-3 text-sm font-bold text-white shadow-xl shadow-indigo-500/30 hover:brightness-110 hover:scale-[1.02] transition-all"
            aria-label="Open Nexus"
        >
            <Sparkles className="w-4 h-4 shrink-0" />
            <span>Nexus</span>
        </Link>
    );
}
