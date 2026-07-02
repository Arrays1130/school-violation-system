@props(['title', 'description' => null, 'badge' => null, 'badgeIcon' => 'shield-alert'])
<div {{ $attributes->merge(['class' => 'vt-page-hero mb-8']) }} role="banner">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(148,163,184,0.12),_transparent_55%)]" aria-hidden="true"></div>
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-slate-500/10 blur-3xl" aria-hidden="true"></div>
    <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            @if($badge)
                <div class="vt-page-hero-badge mb-3" aria-hidden="true">
                    <i data-lucide="{{ $badgeIcon }}" class="w-3.5 h-3.5"></i>
                    {{ $badge }}
                </div>
            @endif
            <h1 class="vt-page-hero-title" id="page-title">{{ $title }}</h1>
            @if($description)
                <p class="vt-page-hero-desc">{{ $description }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center gap-3 shrink-0">{{ $actions }}</div>
        @endif
    </div>
</div>
