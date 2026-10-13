@extends('layouts.public')

@section('title', $book->title.' — University Press')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($book->abstract), 160))

@push('head')
    <meta property="og:type" content="book">
    <meta property="og:title" content="{{ $book->title }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($book->abstract), 160) }}">
    <meta property="og:image" content="{{ $book->cover_url }}">
    <meta property="og:url" content="{{ route('books.show', $book->slug) }}">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        'name' => $book->title,
        'author' => $book->authors->map(fn($a) => ['@type' => 'Person', 'name' => $a->name])->values(),
        'isbn' => $book->isbn,
        'datePublished' => (string) $book->year,
        'inLanguage' => $book->language,
        'numberOfPages' => $book->pages,
        'publisher' => ['@type' => 'Organization', 'name' => $book->publisher?->name],
        'abstract' => strip_tags((string) $book->abstract),
        'image' => $book->cover_url,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="bg-white border-b border-stone-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-xs text-stone-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-gold-600">Beranda</a> /
            <a href="{{ route('catalog') }}" class="hover:text-gold-600">Katalog</a> /
            @if($book->category)<a href="{{ route('categories.show', $book->category->slug) }}" class="hover:text-gold-600">{{ $book->category->name }}</a> /@endif
            <span class="text-stone-600">{{ Str::limit($book->title, 40) }}</span>
        </nav>

        <div class="grid lg:grid-cols-[280px_1fr] gap-10">
            {{-- Cover + actions --}}
            <div>
                <div class="relative aspect-[2/3] rounded-xl overflow-hidden shadow-2xl ring-1 ring-stone-200">
                    <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}" class="h-full w-full object-cover">
                </div>
                <div class="mt-5 space-y-2.5">
                    {{-- Harga / akses --}}
                    <div class="rounded-lg px-4 py-3 text-center ring-1 {{ $book->is_open_access || ! (float) $book->price ? 'bg-emerald-50 ring-emerald-200' : 'bg-gold-50 ring-gold-200' }}">
                        @if($book->is_open_access || ! (float) $book->price)
                            <span class="text-lg font-bold text-emerald-700">Gratis</span>
                            <span class="block text-[11px] text-emerald-600/80">Akses Terbuka</span>
                        @else
                            <span class="text-lg font-bold text-brand-800">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
                            <span class="block text-[11px] text-stone-500">Hubungi penerbit untuk pemesanan</span>
                        @endif
                    </div>

                    @if($pdf)
                        <a href="{{ route('books.read', $book->slug) }}"
                           class="flex items-center justify-center gap-2 w-full rounded-lg bg-brand-800 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.25v13M12 6.25a8 8 0 00-8 0v13a8 8 0 018 0 8 8 0 018 0v-13a8 8 0 00-8 0z"/></svg>
                            Baca Online
                        </a>
                        <a href="{{ route('books.download', $book->slug) }}"
                           class="flex items-center justify-center gap-2 w-full rounded-lg border border-brand-700 px-4 py-3 text-sm font-semibold text-brand-800 hover:bg-brand-50">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                            Unduh PDF
                        </a>
                    @else
                        <div class="rounded-lg bg-stone-100 px-4 py-3 text-center text-sm text-stone-600">
                            📖 Koleksi katalog{{ in_array($book->format, ['Hardcover', 'Paperback']) ? ' (buku cetak)' : '' }}
                            <span class="block text-[11px] text-stone-400 mt-0.5">Berkas digital tidak tersedia</span>
                        </div>
                    @endif
                    <button type="button" @click="document.getElementById('cite-box').scrollIntoView({behavior:'smooth'})"
                            class="flex items-center justify-center gap-2 w-full rounded-lg border border-stone-300 px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-6 8l-3-3h13a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14z"/></svg>
                        Sitasi
                    </button>
                </div>

                <dl class="mt-6 flex justify-around rounded-lg bg-stone-50 py-3 text-center">
                    <div><dt class="text-xs text-stone-400">Dilihat</dt><dd class="font-semibold text-brand-800">{{ number_format($book->view_count) }}</dd></div>
                    <div><dt class="text-xs text-stone-400">Unduhan</dt><dd class="font-semibold text-brand-800">{{ number_format($book->download_count) }}</dd></div>
                    <div><dt class="text-xs text-stone-400">Halaman</dt><dd class="font-semibold text-brand-800">{{ $book->pages ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Details --}}
            <div>
                @if($book->category)
                    <a href="{{ route('categories.show', $book->category->slug) }}" class="text-sm font-medium text-gold-600">{{ $book->category->name }}</a>
                @endif
                <h1 class="mt-1 font-serif text-3xl lg:text-4xl font-bold text-brand-900 leading-tight">{{ $book->title }}</h1>
                @if($book->subtitle)<p class="mt-2 text-lg text-stone-600">{{ $book->subtitle }}</p>@endif

                <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                    <span class="text-stone-500">oleh</span>
                    @foreach($book->authors as $author)
                        <a href="{{ route('authors.show', $author->slug) }}" class="font-medium text-brand-700 hover:text-gold-600">{{ $author->display_name }}</a>@if(!$loop->last)<span class="text-stone-400">,</span>@endif
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    @if($book->is_open_access)<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Open Access</span>@endif
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">{{ $book->format }}</span>
                    @if($book->license)<span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">{{ $book->license }}</span>@endif
                </div>

                @if($book->abstract)
                <div class="mt-7">
                    <h2 class="font-serif text-lg font-semibold text-brand-900 mb-2">Abstrak</h2>
                    <p class="text-stone-700 leading-relaxed text-[15px]">{{ $book->abstract }}</p>
                </div>
                @endif

                @if($book->keywords->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Kata Kunci</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($book->keywords as $kw)
                            <a href="{{ route('catalog', ['q' => $kw->name]) }}" class="rounded-full bg-brand-50 px-3 py-1 text-xs text-brand-700 hover:bg-brand-100">{{ $kw->name }}</a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Metadata table --}}
                <div class="mt-8 rounded-xl border border-stone-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-stone-100">
                            @foreach([
                                'Penerbit' => $book->publisher?->name,
                                'Tahun Terbit' => $book->year,
                                'ISBN' => $book->isbn,
                                'DOI' => $book->doi,
                                'Edisi' => $book->edition,
                                'Bahasa' => $book->language,
                                'Jumlah Halaman' => $book->pages,
                                'Seri' => $book->series,
                            ] as $label => $value)
                                @if($value)
                                <tr>
                                    <th class="bg-stone-50 px-4 py-2.5 text-left font-medium text-stone-500 w-44">{{ $label }}</th>
                                    <td class="px-4 py-2.5 text-stone-800">
                                        @if($label === 'DOI')<a href="https://doi.org/{{ $value }}" class="text-brand-700 hover:underline" target="_blank" rel="noopener">{{ $value }}</a>@else{{ $value }}@endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Citation generator --}}
                <div id="cite-box" class="mt-8 rounded-xl border border-stone-200 bg-white p-5"
                     x-data="{
                        active: 'apa',
                        citations: {{ Illuminate\Support\Js::from($citations) }},
                        copied: false,
                        copy() {
                            navigator.clipboard.writeText(this.citations[this.active]);
                            this.copied = true; setTimeout(() => this.copied = false, 1500);
                        }
                     }">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-serif text-lg font-semibold text-brand-900">Buat Sitasi</h2>
                        <button @click="copy()" class="flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:text-gold-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach(['apa' => 'APA 7', 'mla' => 'MLA', 'chicago' => 'Chicago', 'bibtex' => 'BibTeX', 'ris' => 'RIS'] as $key => $label)
                            <button @click="active = '{{ $key }}'"
                                    :class="active === '{{ $key }}' ? 'bg-brand-800 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                                    class="rounded-full px-3.5 py-1.5 text-xs font-medium transition">{{ $label }}</button>
                        @endforeach
                    </div>
                    <pre class="whitespace-pre-wrap rounded-lg bg-stone-50 p-4 text-[13px] leading-relaxed text-stone-800 font-mono" x-text="citations[active]"></pre>
                    <div class="mt-3 flex gap-3 text-xs">
                        <a :href="`{{ route('books.show', $book->slug) }}/sitasi/bibtex?download=1`" class="text-brand-700 hover:underline">Unduh .bib</a>
                        <a :href="`{{ route('books.show', $book->slug) }}/sitasi/ris?download=1`" class="text-brand-700 hover:underline">Unduh .ris</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recommendations --}}
@if($similar->isNotEmpty())
<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="font-serif text-2xl font-bold text-brand-900 mb-6">Buku Terkait</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($similar as $rec)
            <x-book-card :book="$rec" />
        @endforeach
    </div>
</section>
@endif
@endsection
