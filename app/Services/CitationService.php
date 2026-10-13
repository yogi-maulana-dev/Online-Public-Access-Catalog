<?php

namespace App\Services;

use App\Enums\CitationStyle;
use App\Models\Book;
use App\Models\Citation;
use Illuminate\Support\Str;

/**
 * Generates and caches bibliographic citations in multiple styles.
 */
class CitationService
{
    /**
     * Return every supported style for a book.
     *
     * @return array<string, string>
     */
    public function all(Book $book): array
    {
        $out = [];
        foreach (CitationStyle::cases() as $style) {
            $out[$style->value] = $this->forStyle($book, $style);
        }

        return $out;
    }

    public function forStyle(Book $book, CitationStyle $style): string
    {
        $book->loadMissing('authors', 'publisher');

        $content = match ($style) {
            CitationStyle::APA => $this->apa($book),
            CitationStyle::MLA => $this->mla($book),
            CitationStyle::Chicago => $this->chicago($book),
            CitationStyle::BibTeX => $this->bibtex($book),
            CitationStyle::RIS => $this->ris($book),
        };

        // Cache the rendered citation.
        Citation::updateOrCreate(
            ['book_id' => $book->id, 'style' => $style->value],
            ['content' => $content],
        );

        return $content;
    }

    // ------------------------------------------------------------------
    // Human-readable styles
    // ------------------------------------------------------------------

    private function apa(Book $book): string
    {
        $authors = $this->authorList($book, fn ($n) => $this->apaName($n), ' & ');
        $year = $book->year ? "($book->year)." : '(n.d.).';
        $title = $this->italicTitle($book);
        $edition = $book->edition ? " ({$book->edition})." : '';
        $publisher = $book->publisher?->name ? " {$book->publisher->name}." : '';
        $doi = $book->doi ? ' https://doi.org/'.$book->doi : '';

        return trim("{$authors} {$year} {$title}{$edition}{$publisher}{$doi}");
    }

    private function mla(Book $book): string
    {
        $authors = $this->authorList($book, fn ($n, $i) => $i === 0 ? $this->invertName($n) : $n, ', and ');
        $title = $this->italicTitle($book);
        $publisher = $book->publisher?->name ? "{$book->publisher->name}, " : '';
        $year = $book->year ?? 'n.d.';

        return trim("{$authors}. {$title} {$publisher}{$year}.");
    }

    private function chicago(Book $book): string
    {
        $authors = $this->authorList($book, fn ($n, $i) => $i === 0 ? $this->invertName($n) : $n, ', and ');
        $title = $this->italicTitle($book);
        $city = $book->publisher?->city ? "{$book->publisher->city}: " : '';
        $publisher = $book->publisher?->name ? "{$book->publisher->name}, " : '';
        $year = $book->year ?? 'n.d.';

        return trim("{$authors}. {$title} {$city}{$publisher}{$year}.");
    }

    // ------------------------------------------------------------------
    // Machine-readable formats
    // ------------------------------------------------------------------

    private function bibtex(Book $book): string
    {
        $key = $this->citationKey($book);
        $authors = $book->authors->pluck('name')->map(fn ($n) => $this->invertName($n))->join(' and ');
        $title = trim($book->title.($book->subtitle ? ': '.$book->subtitle : ''));

        $fields = array_filter([
            'title' => $title,
            'author' => $authors,
            'year' => $book->year,
            'publisher' => $book->publisher?->name,
            'address' => $book->publisher?->city,
            'edition' => $book->edition,
            'isbn' => $book->isbn,
            'doi' => $book->doi,
            'url' => route('books.show', $book->slug),
        ]);

        $lines = collect($fields)->map(fn ($v, $k) => "  $k = {{$v}}")->join(",\n");

        return "@book{{$key},\n{$lines}\n}";
    }

    private function ris(Book $book): string
    {
        $lines = ['TY  - BOOK'];
        foreach ($book->authors as $author) {
            $lines[] = 'AU  - '.$this->invertName($author->name);
        }
        $lines[] = 'TI  - '.$book->title;
        if ($book->subtitle) {
            $lines[] = 'T2  - '.$book->subtitle;
        }
        if ($book->year) {
            $lines[] = 'PY  - '.$book->year;
        }
        if ($book->publisher?->name) {
            $lines[] = 'PB  - '.$book->publisher->name;
        }
        if ($book->publisher?->city) {
            $lines[] = 'CY  - '.$book->publisher->city;
        }
        if ($book->isbn) {
            $lines[] = 'SN  - '.$book->isbn;
        }
        if ($book->doi) {
            $lines[] = 'DO  - '.$book->doi;
        }
        if ($book->abstract) {
            $lines[] = 'AB  - '.Str::of($book->abstract)->squish();
        }
        $lines[] = 'UR  - '.route('books.show', $book->slug);
        $lines[] = 'ER  - ';

        return implode("\n", $lines);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function italicTitle(Book $book): string
    {
        $title = $book->title.($book->subtitle ? ': '.$book->subtitle : '');

        return "*{$title}*.";
    }

    /**
     * @param  callable  $formatter  fn(string $name, int $index): string
     */
    private function authorList(Book $book, callable $formatter, string $lastSeparator): string
    {
        $names = $book->authors->pluck('name')->values();

        if ($names->isEmpty()) {
            return 'Anonim';
        }

        $formatted = $names->map(fn ($n, $i) => $formatter($n, $i))->all();

        if (count($formatted) === 1) {
            return $formatted[0];
        }

        $last = array_pop($formatted);

        return implode(', ', $formatted).$lastSeparator.$last;
    }

    /** "Budi Santoso" -> "Santoso, B." (APA) */
    private function apaName(string $name): string
    {
        [$given, $family] = $this->splitName($name);
        $initials = collect(explode(' ', $given))
            ->filter()
            ->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)).'.')
            ->join(' ');

        return trim($family.($initials ? ", {$initials}" : ''));
    }

    /** "Budi Santoso" -> "Santoso, Budi" */
    private function invertName(string $name): string
    {
        [$given, $family] = $this->splitName($name);

        return $given ? "{$family}, {$given}" : $family;
    }

    /**
     * @return array{0: string, 1: string} [given, family]
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) <= 1) {
            return ['', $name];
        }
        $family = array_pop($parts);

        return [implode(' ', $parts), $family];
    }

    private function citationKey(Book $book): string
    {
        $first = $book->authors->first();
        $surname = $first ? Str::slug($this->splitName($first->name)[1]) : 'anon';

        return Str::of($surname.($book->year ?? ''))->replace('-', '')->lower();
    }
}
