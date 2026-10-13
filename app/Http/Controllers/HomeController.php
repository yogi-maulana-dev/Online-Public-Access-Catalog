<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\Slider;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Services\AnalyticsService;

class HomeController extends Controller
{
    public function __construct(private BookRepositoryInterface $books)
    {
    }

    public function index(AnalyticsService $analytics)
    {
        $totals = $analytics->totals();

        return view('home', [
            'sliders' => Slider::active()->get(),
            'featured' => $this->books->featured(6),
            'catalogBooks' => Book::query()->published()->with(['authors', 'category'])
                ->orderByDesc('is_featured')->latest('published_at')->limit(12)->get(),
            'categories' => Category::roots()->withCount('books')->orderBy('sort_order')->get(),
            'spotlightAuthors' => Author::withCount('publishedBooks')
                ->having('published_books_count', '>', 0)
                ->orderByDesc('published_books_count')
                ->limit(4)
                ->get(),
            'gallery' => Gallery::active()->get(),
            'rector' => [
                'name' => Setting::get('rector_name'),
                'title' => Setting::get('rector_title'),
                'position' => Setting::get('rector_position'),
                'quote' => Setting::get('rector_quote'),
                'photo' => Setting::imageUrl('rector_photo', 'https://ui-avatars.com/api/?name=Rektor&size=400&background=174b27&color=fff'),
            ],
            'stats' => [
                ['label' => 'Buku Terbit', 'value' => $totals['books'], 'icon' => 'book'],
                ['label' => 'Penulis', 'value' => $totals['authors'], 'icon' => 'user'],
                ['label' => 'Pembaca', 'value' => $totals['views'], 'icon' => 'eye'],
                ['label' => 'Unduhan', 'value' => $totals['downloads'], 'icon' => 'download'],
                ['label' => 'Fakultas', 'value' => (int) Setting::get('stat_faculties', 8), 'icon' => 'building'],
            ],
        ]);
    }
}
