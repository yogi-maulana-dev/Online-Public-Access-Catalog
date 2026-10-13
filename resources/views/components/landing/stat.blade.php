@props(['value' => 0, 'label' => '', 'icon' => 'book'])

@php
    $icons = [
        'book' => 'M12 6.25a8 8 0 00-8 0v13a8 8 0 018 0 8 8 0 018 0v-13a8 8 0 00-8 0z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z',
        'download' => 'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3',
        'building' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3',
    ];
@endphp

<div x-data="{ n: 0, target: {{ (int) $value }} }"
     x-init="
        const run = () => { let s = Date.now(); let t = setInterval(() => { let p = Math.min((Date.now()-s)/1200, 1); n = Math.floor(p*target); if(p>=1){n=target;clearInterval(t)} }, 30); };
        const io = new IntersectionObserver((e) => { if(e[0].isIntersecting){ run(); io.disconnect(); } }, { threshold: 0.4 });
        io.observe($el);
     "
     class="text-center">
    <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-white/10 text-gold-300 ring-1 ring-white/15">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $icons[$icon] ?? $icons['book'] }}"/></svg>
    </div>
    <div class="font-serif text-3xl sm:text-4xl font-bold text-white tabular-nums" x-text="n.toLocaleString('id-ID')">0</div>
    <div class="mt-1 text-sm text-brand-100">{{ $label }}</div>
</div>
