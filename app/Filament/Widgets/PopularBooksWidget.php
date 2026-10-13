<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PopularBooksWidget extends BaseWidget
{
    protected static ?string $heading = 'Buku Terpopuler';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()->published()->with('authors')->orderByDesc('view_count')->limit(8)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('')->disk('public')->height(48)->width(34)
                    ->defaultImageUrl(fn (Book $r) => $r->cover_url),
                Tables\Columns\TextColumn::make('title')->label('Judul')->wrap()->limit(60)
                    ->description(fn (Book $r) => $r->authorNames()),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('download_count')->label('Unduh')->numeric()->sortable(),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('view analytics') ?? false;
    }
}
