<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Konten Situs';

    protected static ?string $navigationLabel = 'Galeri Kampus';

    protected static ?string $modelLabel = 'Foto Galeri';

    protected static ?string $pluralModelLabel = 'Galeri Kampus';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul / Caption')->required(),
            Forms\Components\Select::make('category')->label('Kategori')->options([
                'Gedung' => 'Gedung', 'Perpustakaan' => 'Perpustakaan', 'Laboratorium' => 'Laboratorium',
                'Mahasiswa' => 'Mahasiswa', 'Wisuda' => 'Wisuda', 'Seminar' => 'Seminar',
            ])->native(false),
            Forms\Components\FileUpload::make('image')->label('Foto')->image()->disk('public')->directory('gallery')
                ->imageEditor()->imageEditorAspectRatios(['3:2', '4:3', '1:1'])->columnSpanFull()->required()
                ->helperText('Rasio 3:2 (lanskap). Disarankan 1200×800 px. JPG/PNG/WEBP, maks 2MB.'),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('Foto')->height(56)->width(80),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage settings') ?? false;
    }
}
