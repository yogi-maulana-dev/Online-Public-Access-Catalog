<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherResource\Pages;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Penerbit';

    protected static ?string $modelLabel = 'Penerbit';

    protected static ?string $pluralModelLabel = 'Penerbit';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(191),
            Forms\Components\FileUpload::make('logo')->label('Logo')->image()->disk('public')->directory('publishers')
                ->helperText('PNG transparan disarankan. Persegi ±400×400 px. Maks 2MB.'),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\TextInput::make('website')->url(),
            Forms\Components\TextInput::make('city')->label('Kota'),
            Forms\Components\TextInput::make('country')->label('Negara')->default('Indonesia'),
            Forms\Components\TextInput::make('founded_year')->label('Tahun Berdiri')->numeric(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('')->disk('public'),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->label('Kota')->toggleable(),
                Tables\Columns\TextColumn::make('founded_year')->label('Berdiri')->toggleable(),
                Tables\Columns\TextColumn::make('books_count')->label('Buku')->counts('books')->badge(),
            ])
            ->defaultSort('name')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage publishers') ?? false;
    }
}
