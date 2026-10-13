<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Download;
use App\Models\Publisher;
use App\Models\Submission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @return array<string, int>
     */
    public function totals(): array
    {
        return [
            'books' => Book::query()->published()->count(),
            'all_books' => Book::count(),
            'authors' => Author::count(),
            'publishers' => Publisher::count(),
            'downloads' => (int) Book::sum('download_count'),
            'views' => (int) Book::sum('view_count'),
            'pending_submissions' => Submission::where('status', 'pending')->count(),
        ];
    }

    public function popularBooks(int $limit = 5)
    {
        return Book::query()
            ->published()
            ->with('authors')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Publications grouped by year (for the bar/line chart).
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function publicationsPerYear(int $years = 8): array
    {
        $start = now()->year - $years + 1;

        $rows = Book::query()
            ->published()
            ->where('year', '>=', $start)
            ->selectRaw('year, COUNT(*) as total')
            ->groupBy('year')
            ->pluck('total', 'year');

        $labels = [];
        $data = [];
        for ($y = $start; $y <= now()->year; $y++) {
            $labels[] = (string) $y;
            $data[] = (int) ($rows[$y] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Downloads over the last N months.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function downloadsPerMonth(int $months = 6): array
    {
        $rows = Download::query()
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function booksByCategory(int $limit = 6): array
    {
        $rows = Book::query()
            ->published()
            ->join('categories', 'categories.id', '=', 'books.category_id')
            ->selectRaw('categories.name as name, COUNT(*) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
        ];
    }
}
