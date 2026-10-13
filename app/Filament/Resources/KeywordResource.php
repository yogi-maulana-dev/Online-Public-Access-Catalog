<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeywordResource\Pages;
use App\Models\Keyword;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KeywordResource extends Resource
{
    protected static ?string $model = Keyword::class;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Kata Kunci';

    protected static ?string $modelLabel = 'Kata Kunci';

    protected static ?string $pluralModelLabel = 'Kata Kunci';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Kata Kunci')->required()->maxLength(191),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Kata Kunci')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('books_count')->label('Buku')->counts('books')->badge(),
            ])
            ->defaultSort('name')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeywords::route('/'),
            'create' => Pages\CreateKeyword::route('/create'),
            'edit' => Pages\EditKeyword::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage books') ?? false;
    }
}
