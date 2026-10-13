@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border-stone-300 bg-white text-stone-800 shadow-sm placeholder-stone-400 focus:border-brand-500 focus:ring-brand-500']) }}>
