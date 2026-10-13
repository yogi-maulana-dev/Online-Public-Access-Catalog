@extends('layouts.public')

@section('title', $author->display_name.' — University Press')
@section('meta_description', \Illuminate\Support\Str::limit($author->bio, 160))

@section('content')
<section class="bg-brand-900 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <nav class="text-xs text-brand-300 mb-5">
            <a href="{{ route('home') }}" class="hover:text-gold-400">Beranda</a> /
            <a href="{{ route('authors.index') }}" class="hover:text-gold-400">Penulis</a> /
            <span class="text-white">{{ $author->name }}</span>
        </nav>
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <img src="{{ $author->photo_url }}" alt="{{ $author->name }}" class="h-28 w-28 rounded-full object-cover ring-4 ring-gold-400/40">
            <div class="flex-1">
                <h1 class="font-serif text-3xl font-bold">{{ $author->display_name }}</h1>
                @if($author->affiliation)<p class="mt-1 text-brand-200">{{ $author->affiliation }}</p>@endif
                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-brand-200">
                    @if($author->department)<span>📚 {{ $author->department }}</span>@endif
                    @if($author->orcid)<a href="https://orcid.org/{{ $author->orcid }}" target="_blank" rel="noopener" class="hover:text-gold-400">ORCID: {{ $author->orcid }}</a>@endif
                    @if($author->scholar_url)<a href="{{ $author->scholar_url }}" target="_blank" rel="noopener" class="hover:text-gold-400">Google Scholar</a>@endif
                </div>
                <p class="mt-2 text-sm text-gold-300 font-medium">{{ $books->count() }} publikasi</p>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 grid lg:grid-cols-[1fr_280px] gap-10">
    <div>
        <h2 class="font-serif text-2xl font-bold text-brand-900 mb-6">Publikasi</h2>
        @if($books->isEmpty())
            <p class="text-stone-500">Belum ada publikasi.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        @endif
    </div>

    @if($author->bio)
    <aside>
        <div class="rounded-xl border border-stone-200 bg-white p-5 lg:sticky lg:top-20">
            <h3 class="font-serif text-lg font-semibold text-brand-900 mb-3">Biografi</h3>
            <p class="text-sm text-stone-600 leading-relaxed">{{ $author->bio }}</p>
        </div>
    </aside>
    @endif
</div>
@endsection
