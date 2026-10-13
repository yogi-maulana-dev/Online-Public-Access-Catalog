@extends('layouts.public')

@section('title', 'Penulis — University Press')

@section('content')
<section class="bg-brand-900 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <h1 class="font-serif text-3xl font-bold">Penulis & Peneliti</h1>
        <p class="text-sm text-brand-200 mt-1">Para akademisi yang berkontribusi dalam koleksi kami</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($authors as $author)
            <a href="{{ route('authors.show', $author->slug) }}"
               class="flex flex-col items-center text-center rounded-xl border border-stone-200 bg-white p-6 hover:shadow-lg hover:-translate-y-0.5 transition">
                <img src="{{ $author->photo_url }}" alt="{{ $author->name }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-gold-100">
                <h2 class="mt-4 font-serif font-semibold text-brand-900">{{ $author->display_name }}</h2>
                <p class="text-xs text-stone-500 mt-1 line-clamp-2">{{ $author->affiliation }}</p>
                <span class="mt-3 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">{{ $author->published_books_count }} publikasi</span>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $authors->links() }}</div>
</div>
@endsection
