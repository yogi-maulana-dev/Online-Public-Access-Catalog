@extends('layouts.public')

@section('title', $category->name.' — University Press')
@section('meta_description', $category->description)

@section('content')
<section class="bg-brand-900 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <nav class="text-xs text-brand-300 mb-3">
            <a href="{{ route('home') }}" class="hover:text-gold-400">Beranda</a> /
            <a href="{{ route('catalog') }}" class="hover:text-gold-400">Katalog</a> /
            <span class="text-white">{{ $category->name }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <span class="text-4xl">{{ $category->icon ?? '📘' }}</span>
            <div>
                <h1 class="font-serif text-3xl font-bold">{{ $category->name }}</h1>
                <p class="text-sm text-brand-200 mt-1 max-w-2xl">{{ $category->description }}</p>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <p class="text-sm text-stone-500 mb-6">{{ number_format($books->total()) }} buku dalam kategori ini</p>
    @if($books->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-16 text-center text-stone-500">
            Belum ada buku pada kategori ini.
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($books as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>
        <div class="mt-8">{{ $books->links() }}</div>
    @endif
</div>
@endsection
