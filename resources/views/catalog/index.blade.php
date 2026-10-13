@extends('layouts.public')

@section('title', ($filters['q'] ?? null) ? 'Cari: '.$filters['q'].' — University Press' : 'Katalog Buku — University Press')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-brand-900">
            @if($filters['q'] ?? null)
                Hasil pencarian untuk “{{ $filters['q'] }}”
            @else
                Katalog Buku
            @endif
        </h1>
        <p class="text-sm text-stone-500 mt-1">{{ number_format($books->total()) }} buku ditemukan</p>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        {{-- Filters --}}
        <aside class="lg:col-span-1">
            <form action="{{ route('catalog') }}" method="GET" x-data class="space-y-6 lg:sticky lg:top-20">
                <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Urutkan</label>
                    <select name="sort" onchange="this.form.submit()" class="w-full rounded-lg border-stone-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        @foreach(['' => 'Relevansi', 'newest' => 'Terbaru', 'popular' => 'Terpopuler', 'title' => 'Judul A-Z', 'oldest' => 'Terlama'] as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['sort'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Kategori</h3>
                    <div class="space-y-1 max-h-56 overflow-auto pr-1">
                        @foreach($categories as $c)
                            <label class="flex items-center justify-between text-sm cursor-pointer rounded px-2 py-1 hover:bg-stone-50">
                                <span class="flex items-center gap-2">
                                    <input type="radio" name="category" value="{{ $c->slug }}" @checked(($filters['category'] ?? '') === $c->slug)
                                           onchange="this.form.submit()" class="text-brand-700 focus:ring-brand-500">
                                    {{ $c->name }}
                                </span>
                                <span class="text-xs text-stone-400">{{ $c->books_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Jenis Koleksi</h3>
                    <div class="space-y-1">
                        @foreach(['' => 'Semua Koleksi', 'digital' => '💾 Buku Digital (PDF/E-Book)', 'cetak' => '📖 Buku Cetak'] as $val => $label)
                            <label class="flex items-center gap-2 text-sm cursor-pointer rounded px-2 py-1 hover:bg-stone-50">
                                <input type="radio" name="format" value="{{ $val }}" @checked(($filters['format'] ?? '') === $val)
                                       onchange="this.form.submit()" class="text-brand-700 focus:ring-brand-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2">Tahun</h3>
                    <select name="year" onchange="this.form.submit()" class="w-full rounded-lg border-stone-300 text-sm">
                        <option value="">Semua tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected((string)($filters['year'] ?? '') === (string)$y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="open_access" value="1" @checked(!empty($filters['open_access']))
                               onchange="this.form.submit()" class="rounded text-brand-700 focus:ring-brand-500">
                        Hanya Akses Terbuka
                    </label>
                </div>

                @if(array_filter($filters))
                    <a href="{{ route('catalog') }}" class="inline-block text-sm text-rose-600 hover:underline">Reset filter</a>
                @endif
            </form>
        </aside>

        {{-- Results --}}
        <div class="lg:col-span-3">
            @if($books->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-16 text-center">
                    <p class="text-stone-500">Tidak ada buku yang cocok dengan pencarian Anda.</p>
                    <a href="{{ route('catalog') }}" class="mt-3 inline-block text-gold-600 font-medium">Lihat semua buku</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($books as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $books->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
