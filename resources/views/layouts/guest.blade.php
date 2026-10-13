@props(['title' => 'Akun'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Akun' }} — {{ \App\Models\Setting::get('site_name', 'University Press') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|lora:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-stone-800 antialiased">
@php $heroSlide = \App\Models\Slider::active()->first(); @endphp

<div class="min-h-full lg:grid lg:grid-cols-2">
    {{-- Brand panel --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-brand-900 p-12 text-white">
        <img src="{{ $heroSlide?->image_url ?? 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1400&q=80' }}"
             alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-950/90 via-brand-900/80 to-brand-800/70"></div>

        <a href="{{ route('home') }}" class="relative flex items-center gap-3">
            <div class="bg-white/95 rounded-full p-1"><x-site-logo imgClass="h-10 w-10 object-contain" fallbackClass="h-10 w-10 text-base" /></div>
            <div>
                <div class="font-serif text-xl font-bold">{{ \App\Models\Setting::get('site_name', 'University Press') }}</div>
                <div class="text-[11px] uppercase tracking-[0.15em] text-gold-400">Digital Press &amp; Book Store</div>
            </div>
        </a>

        <div class="relative">
            <svg class="h-12 w-12 text-gold-400/80 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7.17 6A5.17 5.17 0 002 11.17V18h6.83v-6.83H5.5a3.67 3.67 0 013.67-3.67V6zm9 0A5.17 5.17 0 0011 11.17V18h6.83v-6.83H14.5a3.67 3.67 0 013.67-3.67V6z"/></svg>
            <p class="font-serif text-3xl font-semibold leading-snug max-w-md">
                Mencerdaskan bangsa melalui karya dan inovasi.
            </p>
            <p class="mt-4 text-brand-200 max-w-md">{{ \App\Models\Setting::get('site_tagline', 'Penerbit universitas yang menghadirkan karya ilmiah dosen dan akademisi.') }}</p>
        </div>

        <div class="relative flex items-center gap-6 text-sm text-brand-200">
            <span class="flex items-center gap-2">📚 {{ \App\Models\Book::query()->published()->count() }}+ Buku</span>
            <span class="flex items-center gap-2">✦ Open Access</span>
            <span class="flex items-center gap-2">✦ ISBN Resmi</span>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="flex min-h-screen lg:min-h-full items-center justify-center bg-stone-50 px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            {{-- Mobile logo --}}
            <a href="{{ route('home') }}" class="lg:hidden flex items-center justify-center gap-2 mb-8">
                <x-site-logo imgClass="h-10 w-10 object-contain" fallbackClass="h-10 w-10 text-base" />
                <span class="font-serif text-lg font-bold text-brand-800">{{ \App\Models\Setting::get('site_name', 'University Press') }}</span>
            </a>

            <div class="rounded-2xl border border-stone-200 bg-white p-7 sm:p-9 shadow-xl shadow-stone-200/50">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-stone-400">
                &copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'University Press') }} ·
                <a href="{{ route('home') }}" class="hover:text-brand-700">Kembali ke beranda</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
