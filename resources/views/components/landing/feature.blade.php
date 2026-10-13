@props(['title' => '', 'desc' => '', 'icon' => ''])

<div class="group relative rounded-2xl border border-stone-200 bg-white p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
    <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-brand-700 group-hover:bg-brand-600 group-hover:text-white transition">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $icon }}"/></svg>
    </div>
    <h3 class="mt-4 font-semibold text-stone-900">{{ $title }}</h3>
    <p class="mt-1.5 text-sm text-stone-500 leading-relaxed">{{ $desc }}</p>
    <span class="absolute bottom-0 left-6 right-6 h-0.5 bg-gradient-to-r from-brand-500 to-gold-400 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</div>
