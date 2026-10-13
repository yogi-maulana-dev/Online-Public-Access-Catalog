<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Models\Author;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Penulis';

    protected static ?string $modelLabel = 'Penulis';

    protected static ?string $pluralModelLabel = 'Penulis';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas')->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(191),
                Forms\Components\TextInput::make('title')->label('Gelar Akademik')->placeholder('Dr., M.Kom.'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\FileUpload::make('photo')->label('Foto')->image()->disk('public')->directory('authors')->avatar()
                    ->imageEditor()->imageEditorAspectRatios(['1:1'])
                    ->helperText('Foto persegi (1:1). Disarankan 400×400 px. Maks 2MB.'),
            ])->columns(2),
            Forms\Components\Section::make('Biodata Akademik')->schema([
                Forms\Components\TextInput::make('affiliation')->label('Afiliasi'),
                Forms\Components\TextInput::make('faculty')->label('Fakultas'),
                Forms\Components\TextInput::make('department')->label('Program Studi'),
                Forms\Components\TextInput::make('orcid')->label('ORCID')->maxLength(40),
                Forms\Components\TextInput::make('scholar_url')->label('Google Scholar')->url(),
                Forms\Components\TextInput::make('website')->url(),
                Forms\Components\Textarea::make('bio')->label('Biografi')->rows(4)->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('')->disk('public')->circular()
                    ->defaultImageUrl(fn (Author $r) => $r->photo_url),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()
                    ->description(fn (Author $r) => $r->title),
                Tables\Columns\TextColumn::make('affiliation')->label('Afiliasi')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('department')->label('Program Studi')->toggleable(),
                Tables\Columns\TextColumn::make('books_count')->label('Buku')->counts('books')->badge(),
            ])
            ->defaultSort('name')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage authors') ?? false;
    }
}
