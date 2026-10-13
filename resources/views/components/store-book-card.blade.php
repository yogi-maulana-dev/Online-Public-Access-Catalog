@props(['book'])

@php
    $rating = (float) $book->rating ?: 0;
    $full = (int) floor($rating);
@endphp

<div {{ $attributes->merge(['class' => 'group flex flex-col rounded-2xl border border-stone-200 bg-white overflow-hidden hover:shadow-2xl hover:border-brand-200 transition-all duration-300']) }}>
    <a href="{{ route('books.show', $book->slug) }}" class="relative block aspect-[3/4] overflow-hidden bg-stone-100">
        <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}" loading="lazy"
             class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
        @if($book->is_open_access)
            <span class="absolute top-3 left-3 rounded-full bg-brand-600/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow">Gratis</span>
        @endif
        @if($book->is_featured)
            <span class="absolute top-3 right-3 rounded-full bg-gold-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow">Unggulan</span>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-brand-950/40 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
    </a>

    <div class="flex flex-1 flex-col p-4">
        @if($book->category)
            <a href="{{ route('categories.show', $book->category->slug) }}" class="text-[11px] font-semibold uppercase tracking-wide text-brand-600 mb-1">{{ $book->category->name }}</a>
        @endif
        <h3 class="font-serif text-[15px] font-bold leading-snug text-stone-900 line-clamp-2">
            <a href="{{ route('books.show', $book->slug) }}" class="hover:text-brand-700">{{ $book->title }}</a>
        </h3>
        <p class="mt-1 text-xs text-stone-500 line-clamp-1">{{ $book->authorNames() ?: 'Anonim' }}</p>
        <p class="mt-1 text-[11px] text-stone-400">
            <span class="rounded bg-brand-50 px-1.5 py-0.5 font-medium text-brand-700">{{ in_array($book->format, ['PDF', 'E-Book']) ? '💾 '.$book->format : '📖 '.$book->format }}</span>
            @if($book->pages) · {{ $book->pages }} hlm @endif
        </p>

        {{-- Rating --}}
        <div class="mt-2 flex items-center gap-1.5">
            <div class="flex">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="h-3.5 w-3.5 {{ $i <= $full ? 'text-gold-500' : 'text-stone-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.28 3.94a1 1 0 00.95.69h4.15c.97 0 1.37 1.24.59 1.81l-3.36 2.44a1 1 0 00-.36 1.12l1.28 3.94c.3.92-.75 1.69-1.54 1.12l-3.35-2.44a1 1 0 00-1.18 0l-3.35 2.44c-.79.57-1.84-.2-1.54-1.12l1.28-3.94a1 1 0 00-.36-1.12L2.83 9.37c-.78-.57-.38-1.81.59-1.81h4.15a1 1 0 00.95-.69l1.28-3.94z"/></svg>
                @endfor
            </div>
            <span class="text-[11px] text-stone-400">{{ number_format($rating, 1) }} ({{ $book->rating_count }})</span>
        </div>

        <div class="mt-auto pt-4 flex items-center justify-between">
            <span class="font-bold text-brand-700">
                @if($book->is_open_access || ! $book->price || (float) $book->price == 0)
                    Gratis
                @else
                    Rp {{ number_format($book->price, 0, ',', '.') }}
                @endif
            </span>
            <a href="{{ route('books.show', $book->slug) }}"
               class="rounded-lg bg-brand-700 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-800 transition">Detail</a>
        </div>
    </div>
</div>
