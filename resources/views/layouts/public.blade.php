<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Penerbit universitas yang menghadirkan karya ilmiah dosen dan akademisi. Pustaka digital & toko buku akademik.')">
    @stack('head')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|lora:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans text-stone-800 bg-stone-50 antialiased">

@php $navCategories = \App\Models\Category::roots()->orderBy('sort_order')->get(); @endphp

<header x-data="{ open: false, search: false, scrolled: false }"
        @scroll.window="scrolled = window.scrollY > 20"
        class="sticky top-0 z-50">
    {{-- Top bar --}}
    <div class="hidden lg:block bg-brand-950 text-brand-100 text-xs">
        <div class="mx-auto max-w-7xl px-6 flex h-9 items-center justify-between">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">✉ {{ \App\Models\Setting::get('contact_email', 'press@univ.ac.id') }}</span>
                <span class="flex items-center gap-1.5">☎ {{ \App\Models\Setting::get('contact_phone', '+62 721 123 456') }}</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ url('/oai?verb=Identify') }}" class="hover:text-gold-300">OAI-PMH</a>
                <a href="{{ url('/sitemap.xml') }}" class="hover:text-gold-300">Sitemap</a>
                <span class="text-brand-300">|</span>
                <span class="text-gold-300 font-medium">ISBN Resmi · Open Access</span>
            </div>
        </div>
    </div>

    {{-- Main nav --}}
    <div class="transition-all" :class="scrolled ? 'bg-white/95 backdrop-blur shadow-lg' : 'bg-white shadow-sm'">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-[68px] items-center justify-between gap-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                    <x-site-logo imgClass="h-11 w-11 object-contain" fallbackClass="h-11 w-11 text-lg" />
                    <span class="leading-tight">
                        <span class="block font-serif text-[17px] font-bold text-brand-800">{{ \App\Models\Setting::get('site_name', 'University Press') }}</span>
                        <span class="hidden sm:block text-[10px] uppercase tracking-[0.15em] text-gold-600">Digital Press &amp; Book Store</span>
                    </span>
                </a>

                {{-- Menu --}}
                <nav class="hidden xl:flex items-center gap-1 text-[14px] font-medium text-stone-700">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Beranda</a>
                    <a href="{{ route('catalog') }}" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Buku</a>
                    <a href="{{ route('authors.index') }}" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Penulis</a>
                    <div x-data="{ o: false }" class="relative" @mouseenter="o=true" @mouseleave="o=false">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">
                            Kategori <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5z"/></svg>
                        </button>
                        <div x-show="o" x-transition x-cloak class="absolute left-0 mt-1 w-60 rounded-xl border border-stone-100 bg-white p-2 shadow-xl">
                            @foreach($navCategories as $c)
                                <a href="{{ route('categories.show', $c->slug) }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700">
                                    <span>{{ $c->icon ?? '📘' }}</span> {{ $c->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('home') }}#tentang" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Tentang Kami</a>
                    <a href="{{ route('catalog', ['sort' => 'newest']) }}" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Publikasi</a>
                    <a href="{{ route('home') }}#kontak" class="px-3 py-2 rounded-lg hover:bg-brand-50 hover:text-brand-700 transition">Kontak</a>
                </nav>

                {{-- Actions --}}
                <div class="flex items-center gap-1.5">
                    <button @click="search=!search" class="grid h-10 w-10 place-items-center rounded-lg text-stone-600 hover:bg-stone-100" title="Cari">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    </button>

                    @auth
                        <div x-data="{ m:false }" class="relative hidden sm:block" @click.outside="m=false" @keydown.escape.window="m=false">
                            <button @click="m=!m" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-stone-100">
                                <img src="{{ auth()->user()->avatar_url }}" class="h-8 w-8 rounded-full object-cover" alt="">
                            </button>
                            <div x-show="m" x-transition x-cloak class="absolute right-0 mt-1 w-48 rounded-xl border border-stone-100 bg-white py-1 shadow-xl">
                                @can('access admin')<a href="/admin" class="block px-4 py-2 text-sm hover:bg-stone-50">Panel Admin</a>@endcan
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-stone-50">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-stone-50">Keluar</button></form>
                            </div>
                        </div>
                    @endauth

                    <button @click="open=!open" class="xl:hidden grid h-10 w-10 place-items-center rounded-lg text-stone-700 hover:bg-stone-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Search bar (toggle) --}}
            <div x-show="search" x-transition x-cloak class="pb-4">
                <form action="{{ route('catalog') }}" method="GET" class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" autofocus placeholder="Cari judul buku, penulis, ISBN, atau topik…"
                           class="w-full rounded-full border-stone-300 bg-stone-50 pl-12 pr-4 py-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <svg class="absolute left-4 top-3.5 h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </form>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-transition x-cloak class="xl:hidden border-t border-stone-100 bg-white">
            <div class="px-4 py-3 space-y-1">
                @foreach(['Beranda' => route('home'), 'Buku' => route('catalog'), 'Penulis' => route('authors.index'), 'Publikasi' => route('catalog', ['sort'=>'newest']), 'Tentang Kami' => route('home').'#tentang', 'Kontak' => route('home').'#kontak'] as $label => $url)
                    <a href="{{ $url }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-stone-700 hover:bg-brand-50">{{ $label }}</a>
                @endforeach
                @auth
                    <div class="pt-2 flex gap-2">
                        @can('access admin')<a href="/admin" class="flex-1 rounded-lg bg-brand-700 px-4 py-2.5 text-center text-sm font-semibold text-white">Panel Admin</a>@endcan
                        <form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf<button class="w-full rounded-lg border border-stone-300 px-4 py-2.5 text-sm font-semibold">Keluar</button></form>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>

@auth
    @if(auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
        <div class="bg-gold-50 border-b border-gold-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-sm text-gold-800">
                <span>⚠ Email Anda belum terverifikasi. Cek kotak masuk untuk tautan verifikasi.</span>
                <form method="POST" action="{{ route('verification.send') }}">@csrf
                    <button class="font-semibold text-brand-700 hover:text-brand-800 underline">Kirim ulang</button>
                </form>
            </div>
        </div>
    @endif
@endauth

<main>
    @yield('content')
</main>

@include('layouts.partials.footer')

</body>
</html>
