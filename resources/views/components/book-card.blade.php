@props(['book'])

<a href="{{ route('books.show', $book->slug) }}"
   class="group flex flex-col rounded-xl border border-stone-200 bg-white overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-200">
    <div class="relative aspect-[2/3] overflow-hidden bg-brand-100">
        <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}"
             loading="lazy"
             class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
        @if($book->is_open_access)
            <span class="absolute top-2 left-2 rounded-full bg-emerald-600/90 px-2 py-0.5 text-[10px] font-semibold text-white">Open Access</span>
        @endif
        @if($book->is_featured)
            <span class="absolute top-2 right-2 rounded-full bg-gold-500 px-2 py-0.5 text-[10px] font-semibold text-white">Unggulan</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-3.5">
        @if($book->category)
            <span class="text-[11px] font-medium text-gold-600 mb-1">{{ $book->category->name }}</span>
        @endif
        <h3 class="font-serif text-sm font-semibold leading-snug text-brand-900 line-clamp-2 group-hover:text-brand-700">
            {{ $book->title }}
        </h3>
        <p class="mt-1 text-xs text-stone-500 line-clamp-1">{{ $book->authorNames() ?: 'Anonim' }}</p>
        <div class="mt-auto pt-3 flex items-center justify-between text-[11px] text-stone-400">
            <span class="flex items-center gap-1.5">
                {{ $book->year }}
                <span class="rounded bg-brand-50 px-1.5 py-0.5 font-medium text-brand-700">{{ in_array($book->format, ['PDF', 'E-Book']) ? '💾 '.$book->format : '📖 '.$book->format }}</span>
            </span>
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                {{ number_format($book->view_count) }}
            </span>
        </div>
    </div>
</a>
