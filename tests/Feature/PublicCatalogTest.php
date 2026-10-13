<?php

namespace Tests\Feature;

use App\Services\CitationService;
use App\Services\SearchService;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CatalogSeeder::class);
    }

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('University Press');
    }

    public function test_catalog_fulltext_search_ranks_relevant_book_first(): void
    {
        $results = app(SearchService::class)->search(['q' => 'kecerdasan buatan untuk pendidikan']);

        $this->assertGreaterThan(0, $results->total());
        $this->assertEquals('Kecerdasan Buatan untuk Pendidikan', $results->first()->title);
    }

    public function test_catalog_filters_by_category(): void
    {
        $this->get('/kategori/kecerdasan-buatan')->assertOk()->assertSee('Kecerdasan Buatan');
    }

    public function test_book_detail_page_shows_metadata_and_citations(): void
    {
        $this->get('/buku/kecerdasan-buatan-untuk-pendidikan')
            ->assertOk()
            ->assertSee('Abstrak')
            ->assertSee('Buat Sitasi');
    }

    public function test_citation_endpoint_returns_apa(): void
    {
        $book = \App\Models\Book::where('slug', 'like', 'kecerdasan-buatan%')->first();

        $apa = app(CitationService::class)->forStyle($book, \App\Enums\CitationStyle::APA);
        $this->assertStringContainsString('Rahmawati', $apa);
        $this->assertStringContainsString('(2024)', $apa);

        $this->get("/buku/{$book->slug}/sitasi/bibtex?download=1")
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="'.$book->slug.'.bib"');
    }
}
