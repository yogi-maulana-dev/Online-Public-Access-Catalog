@extends('layouts.public')

@section('title', \App\Models\Setting::get('site_name', 'University Press').' — Digital University Press & Online Book Store')

@section('content')

{{-- ============================ 1. HERO SLIDER ============================ --}}
<section class="relative bg-brand-950"
         x-data="{
            active: 0,
            count: {{ max($sliders->count(), 1) }},
            timer: null,
            start() { this.timer = setInterval(() => this.next(), 6000) },
            next() { this.active = (this.active + 1) % this.count },
            go(i) { this.active = i; clearInterval(this.timer); this.start() }
         }"
         x-init="start()">
    <div class="relative h-[70vh] min-h-[520px] max-h-[760px] overflow-hidden">
        @forelse($sliders as $i => $slide)
            <div x-show="active === {{ $i }}" x-transition.opacity.duration.700ms class="absolute inset-0">
                <img src="{{ $slide->image_url ?? 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1920&q=80' }}"
                     alt="{{ $slide->title }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 via-brand-900/70 to-brand-900/30"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8">
                        <div class="max-w-2xl" x-show="active === {{ $i }}" x-transition.duration.700ms>
                            <span class="inline-flex items-center gap-2 rounded-full bg-gold-500/20 px-4 py-1.5 text-xs font-semibold text-gold-300 ring-1 ring-gold-400/30 mb-5">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> University Press Resmi
                            </span>
                            <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight text-white">{{ $slide->title }}</h1>
                            @if($slide->subtitle)
                                <p class="mt-5 text-lg text-brand-100 leading-relaxed max-w-xl">{{ $slide->subtitle }}</p>
                            @endif
                            <div class="mt-8 flex flex-wrap gap-3">
                                @if($slide->cta_label)
                                    <a href="{{ $slide->cta_url }}" class="rounded-full bg-gold-500 px-7 py-3.5 font-semibold text-brand-950 hover:bg-gold-400 shadow-lg hover:shadow-gold-500/30 transition">{{ $slide->cta_label }}</a>
                                @endif
                                @if($slide->cta2_label)
                                    <a href="{{ $slide->cta2_url }}" class="rounded-full border-2 border-white/40 px-7 py-3.5 font-semibold text-white hover:bg-white hover:text-brand-900 transition backdrop-blur-sm">{{ $slide->cta2_label }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="absolute inset-0 grid place-items-center text-white">Belum ada slider.</div>
        @endforelse

        {{-- Arrows --}}
        <button @click="go((active - 1 + count) % count)" class="absolute left-4 top-1/2 -translate-y-1/2 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30 transition">‹</button>
        <button @click="go((active + 1) % count)" class="absolute right-4 top-1/2 -translate-y-1/2 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30 transition">›</button>

        {{-- Dots --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
            @foreach($sliders as $i => $s)
                <button @click="go({{ $i }})" :class="active === {{ $i }} ? 'w-8 bg-gold-400' : 'w-2.5 bg-white/50'" class="h-2.5 rounded-full transition-all"></button>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ 2. FEATURE CARDS ============================ --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-16 relative z-10">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <x-landing.feature title="Buku Berkualitas" desc="Kurasi ketat oleh dewan editorial." icon="M12 6.25a8 8 0 00-8 0v13a8 8 0 018 0 8 8 0 018 0v-13a8 8 0 00-8 0z" />
        <x-landing.feature title="Terbit Resmi ISBN" desc="Setiap judul terdaftar resmi." icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-landing.feature title="Akses Digital" desc="Baca daring kapan saja." icon="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9 6 9-6M3 7a2 2 0 012-2h14a2 2 0 012 2" />
        <x-landing.feature title="Pembayaran Aman" desc="Transaksi terenkripsi." icon="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        <x-landing.feature title="Pengiriman Cepat" desc="Kirim ke seluruh Indonesia." icon="M9 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H3m10 11h6m0 0V8a1 1 0 011-1h2.05a2 2 0 011.84 1.22L21 11" />
        <x-landing.feature title="Layanan 24/7" desc="Dukungan pelanggan siaga." icon="M18.36 6.64a9 9 0 11-12.73 0M12 2v10" />
    </div>
</section>

{{-- ============================ 3. BOOK CATALOG ============================ --}}
<section id="buku" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-20"
         x-data="{ cat: 'all' }">
    <div class="text-center mb-10">
        <span class="text-sm font-semibold uppercase tracking-widest text-gold-600">Koleksi Pilihan</span>
        <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-bold text-stone-900">Katalog Buku</h2>
        <p class="mt-2 text-stone-500 max-w-2xl mx-auto">Telusuri ribuan karya akademik dari berbagai disiplin ilmu.</p>
    </div>

    <div class="grid lg:grid-cols-[240px_1fr] gap-8">
        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-28 self-start">
            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                <h3 class="px-2 text-xs font-semibold uppercase tracking-wide text-stone-400 mb-2">Kategori</h3>
                <button @click="cat='all'" :class="cat==='all' ? 'bg-brand-600 text-white' : 'hover:bg-brand-50 text-stone-700'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition">
                    <span>Semua Kategori</span>
                </button>
                @foreach($categories as $c)
                    <button @click="cat='{{ $c->slug }}'" :class="cat==='{{ $c->slug }}' ? 'bg-brand-600 text-white' : 'hover:bg-brand-50 text-stone-700'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition">
                        <span class="flex items-center gap-2">{{ $c->icon ?? '📘' }} {{ $c->name }}</span>
                        <span class="text-xs opacity-70">{{ $c->books_count }}</span>
                    </button>
                @endforeach
                <a href="{{ route('catalog') }}" class="mt-3 block rounded-lg bg-stone-100 px-3 py-2.5 text-center text-sm font-semibold text-stone-700 hover:bg-stone-200">Lihat Semua →</a>
            </div>
        </aside>

        {{-- Grid --}}
        <div>
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($catalogBooks as $book)
                    <div x-show="cat === 'all' || cat === '{{ $book->category?->slug }}'" x-transition.opacity>
                        <x-store-book-card :book="$book" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================ 4. RECTOR ============================ --}}
<section id="tentang" class="mt-24 bg-gradient-to-br from-brand-50 to-stone-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-12 items-center">
        <div class="relative">
            <div class="absolute -top-4 -left-4 h-full w-full rounded-3xl bg-gold-200/50"></div>
            <img src="{{ $rector['photo'] }}" alt="{{ $rector['name'] }}"
                 class="relative rounded-3xl shadow-2xl w-full max-w-md object-cover aspect-[4/5]">
            <div class="absolute -bottom-5 left-1/2 lg:left-8 -translate-x-1/2 lg:translate-x-0 rounded-2xl bg-white px-6 py-3 shadow-xl ring-1 ring-stone-100 text-center">
                <div class="font-serif font-bold text-brand-800">{{ $rector['name'] }}</div>
                <div class="text-xs text-gold-600">{{ $rector['position'] }}</div>
            </div>
        </div>
        <div>
            <span class="text-sm font-semibold uppercase tracking-widest text-gold-600">Sambutan Rektor</span>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-bold text-stone-900">{{ $rector['title'] }}</h2>
            <svg class="mt-6 h-10 w-10 text-gold-400" fill="currentColor" viewBox="0 0 24 24"><path d="M7.17 6A5.17 5.17 0 002 11.17V18h6.83v-6.83H5.5a3.67 3.67 0 013.67-3.67V6zm9 0A5.17 5.17 0 0011 11.17V18h6.83v-6.83H14.5a3.67 3.67 0 013.67-3.67V6z"/></svg>
            <p class="mt-4 text-lg text-stone-600 leading-relaxed italic">{{ $rector['quote'] }}</p>
            <a href="{{ route('catalog') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-brand-700 px-7 py-3.5 font-semibold text-white hover:bg-brand-800 transition">
                Selengkapnya
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================ 5. STATISTICS ============================ --}}
<section class="relative bg-brand-900 overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 15% 50%, #c8a45c 0, transparent 35%), radial-gradient(circle at 85% 50%, #2a9244 0, transparent 35%)"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8">
            @foreach($stats as $stat)
                <x-landing.stat :value="$stat['value']" :label="$stat['label']" :icon="$stat['icon']" />
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ 6. AUTHORS ============================ --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-24">
    <div class="flex items-end justify-between mb-10">
        <div>
            <span class="text-sm font-semibold uppercase tracking-widest text-gold-600">Akademisi Kami</span>
            <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-bold text-stone-900">Penulis Unggulan</h2>
        </div>
        <a href="{{ route('authors.index') }}" class="hidden sm:inline text-sm font-semibold text-brand-700 hover:text-brand-800">Semua Penulis →</a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($spotlightAuthors as $author)
            <a href="{{ route('authors.show', $author->slug) }}" class="group text-center rounded-2xl border border-stone-200 bg-white p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="relative mx-auto w-24 h-24">
                    <img src="{{ $author->photo_url }}" alt="{{ $author->name }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-brand-50 group-hover:ring-gold-200 transition">
                    <span class="absolute -bottom-1 -right-1 grid h-8 w-8 place-items-center rounded-full bg-gold-500 text-xs font-bold text-white ring-2 ring-white">{{ $author->published_books_count }}</span>
                </div>
                <h3 class="mt-4 font-serif font-bold text-stone-900 group-hover:text-brand-700">{{ $author->display_name }}</h3>
                <p class="text-xs text-stone-500 mt-1 line-clamp-1">{{ $author->department ?? $author->affiliation }}</p>
                <p class="mt-2 text-xs font-medium text-gold-600">{{ $author->published_books_count }} publikasi</p>
            </a>
        @endforeach
    </div>
</section>

{{-- ============================ 7. CAMPUS GALLERY ============================ --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-24"
         x-data="{ open: false, img: '', cap: '' }">
    <div class="text-center mb-10">
        <span class="text-sm font-semibold uppercase tracking-widest text-gold-600">Galeri</span>
        <h2 class="mt-2 font-serif text-3xl sm:text-4xl font-bold text-stone-900">Suasana Kampus</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($gallery as $i => $g)
            <button @click="open=true; img='{{ $g->image_url }}'; cap='{{ $g->title }}'"
                    class="group relative overflow-hidden rounded-2xl {{ $i === 0 ? 'md:col-span-2 md:row-span-2 aspect-square md:aspect-auto' : 'aspect-[4/3]' }}">
                <img src="{{ $g->image_url }}" alt="{{ $g->title }}" loading="lazy"
                     class="h-full w-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-950/70 to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-4">
                    <span class="text-white font-medium text-sm">{{ $g->title }}</span>
                </div>
                @if($g->category)<span class="absolute top-3 left-3 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold text-brand-800">{{ $g->category }}</span>@endif
            </button>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="open" x-cloak x-transition.opacity @click="open=false" @keydown.escape.window="open=false"
         class="fixed inset-0 z-[60] grid place-items-center bg-black/80 p-4 backdrop-blur-sm">
        <div class="max-w-4xl w-full" @click.stop>
            <img :src="img" class="w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl">
            <p class="mt-3 text-center text-white" x-text="cap"></p>
        </div>
        <button @click="open=false" class="absolute top-5 right-5 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-white text-xl hover:bg-white/30">✕</button>
    </div>
</section>

{{-- ============================ CTA ============================ --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-24 mb-8">
    <div class="relative overflow-hidden rounded-3xl bg-brand-800 px-8 py-16 text-center">
        <div class="absolute inset-0 opacity-25" style="background-image:radial-gradient(circle at 80% 20%, #c8a45c 0, transparent 40%)"></div>
        <div class="relative">
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-white">Punya karya ilmiah untuk diterbitkan?</h2>
            <p class="mt-3 text-brand-100 max-w-xl mx-auto">Bergabunglah bersama ratusan akademisi dan terbitkan karya Anda dengan ISBN resmi.</p>
            <a href="{{ route('register') }}" class="mt-7 inline-block rounded-full bg-gold-500 px-8 py-4 font-semibold text-brand-950 hover:bg-gold-400 shadow-lg transition">Mulai Terbitkan Sekarang</a>
        </div>
    </div>
</section>

@endsection
