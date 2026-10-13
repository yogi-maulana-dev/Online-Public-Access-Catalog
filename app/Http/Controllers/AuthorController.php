<?php

namespace App\Http\Controllers;

use App\Models\Author;

class AuthorController extends Controller
{
    public function index()
    {
        return view('authors.index', [
            'authors' => Author::has('publishedBooks')
                ->withCount('publishedBooks')
                ->orderByDesc('published_books_count')
                ->paginate(16),
        ]);
    }

    public function show(Author $author)
    {
        $author->load(['publishedBooks' => fn ($q) => $q->with('category')->latest('published_at')]);

        return view('authors.show', [
            'author' => $author,
            'books' => $author->publishedBooks,
        ]);
    }
}
