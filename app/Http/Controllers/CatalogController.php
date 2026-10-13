<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\SearchService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private SearchService $search)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category', 'year', 'author', 'publisher', 'sort', 'open_access', 'format']);

        $books = $this->search->search($filters);

        return view('catalog.index', [
            'books' => $books,
            'filters' => $filters,
            'categories' => Category::withCount('books')->orderBy('name')->get(),
            'years' => Book::query()->published()->whereNotNull('year')
                ->distinct()->orderByDesc('year')->pluck('year'),
            'authors' => Author::has('publishedBooks')->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function category(Category $category, Request $request)
    {
        $filters = array_merge(
            $request->only(['q', 'year', 'author', 'sort']),
            ['category' => $category->slug],
        );

        return view('catalog.category', [
            'category' => $category,
            'books' => $this->search->search($filters),
            'filters' => $filters,
        ]);
    }
}
