<?php

namespace App\Http\Controllers;

use App\Enums\CitationStyle;
use App\Models\Book;
use App\Services\CitationService;
use Illuminate\Http\Request;

class CitationController extends Controller
{
    public function __construct(private CitationService $citations)
    {
    }

    public function show(Book $book, string $style, Request $request)
    {
        $citationStyle = CitationStyle::tryFrom($style);
        abort_unless($citationStyle, 404);

        $content = $this->citations->forStyle($book, $citationStyle);
        $book->increment('citation_count');

        // Machine formats are downloaded as files; otherwise return JSON for the UI.
        if ($request->boolean('download') || $citationStyle->isMachineFormat()) {
            $ext = match ($citationStyle) {
                CitationStyle::BibTeX => 'bib',
                CitationStyle::RIS => 'ris',
                default => 'txt',
            };

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$book->slug.'.'.$ext.'"',
            ]);
        }

        return response()->json([
            'style' => $citationStyle->value,
            'label' => $citationStyle->label(),
            'content' => $content,
        ]);
    }
}
