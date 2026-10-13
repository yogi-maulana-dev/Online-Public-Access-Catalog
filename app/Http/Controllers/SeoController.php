<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * XML sitemap of all public, indexable URLs.
     */
    public function sitemap(): Response
    {
        $urls = collect();

        $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('catalog'), 'priority' => '0.9', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('authors.index'), 'priority' => '0.7', 'changefreq' => 'weekly']);

        foreach (Category::all() as $c) {
            $urls->push(['loc' => route('categories.show', $c->slug), 'priority' => '0.7', 'changefreq' => 'weekly']);
        }

        foreach (Author::has('publishedBooks')->get() as $a) {
            $urls->push(['loc' => route('authors.show', $a->slug), 'priority' => '0.6', 'changefreq' => 'monthly']);
        }

        foreach (Book::query()->published()->get(['slug', 'updated_at']) as $b) {
            $urls->push([
                'loc' => route('books.show', $b->slug),
                'lastmod' => $b->updated_at?->toAtomString(),
                'priority' => '0.8',
                'changefreq' => 'monthly',
            ]);
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /buku/*/baca',
            'Disallow: /buku/*/stream',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
