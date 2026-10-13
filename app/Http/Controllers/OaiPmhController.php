<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Minimal OAI-PMH 2.0 data provider exposing published books as Dublin Core (oai_dc).
 * Supported verbs: Identify, ListMetadataFormats, ListSets, ListIdentifiers, ListRecords, GetRecord.
 */
class OaiPmhController extends Controller
{
    public function handle(Request $request): Response
    {
        $verb = $request->query('verb', '');

        $data = [
            'verb' => $verb,
            'request' => $request->fullUrl(),
            'responseDate' => now()->toIso8601ZuluString(),
        ];

        try {
            $data += match ($verb) {
                'Identify' => $this->identify(),
                'ListMetadataFormats' => ['error' => null],
                'ListSets' => ['sets' => Category::all()],
                'ListIdentifiers', 'ListRecords' => ['books' => $this->records($request)],
                'GetRecord' => $this->getRecord($request),
                default => ['error' => ['code' => 'badVerb', 'message' => 'Illegal or missing verb.']],
            };
        } catch (\RuntimeException $e) {
            $data['error'] = ['code' => $e->getMessage(), 'message' => $e->getMessage()];
        }

        return response()->view('oai.response', $data)->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    private function identify(): array
    {
        return [
            'earliest' => optional(Book::query()->published()->min('published_at'))
                ? \Illuminate\Support\Carbon::parse(Book::query()->published()->min('published_at'))->toIso8601ZuluString()
                : now()->toIso8601ZuluString(),
        ];
    }

    private function records(Request $request)
    {
        $query = Book::query()->published()->with(['authors', 'keywords', 'category', 'publisher']);

        if ($set = $request->query('set')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $set));
        }
        if ($from = $request->query('from')) {
            $query->where('published_at', '>=', $from);
        }
        if ($until = $request->query('until')) {
            $query->where('published_at', '<=', $until);
        }

        $books = $query->get();

        if ($books->isEmpty()) {
            throw new \RuntimeException('noRecordsMatch');
        }

        return $books;
    }

    private function getRecord(Request $request): array
    {
        $identifier = $request->query('identifier', '');
        $slug = \Illuminate\Support\Str::afterLast($identifier, ':');

        $book = Book::query()->published()->with(['authors', 'keywords', 'category', 'publisher'])
            ->where('slug', $slug)->first();

        if (! $book) {
            throw new \RuntimeException('idDoesNotExist');
        }

        return ['books' => collect([$book])];
    }

    public static function identifierFor(Book $book): string
    {
        return 'oai:'.request()->getHost().':'.$book->slug;
    }
}
