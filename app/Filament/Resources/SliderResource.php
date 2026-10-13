<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten Situs';

    protected static ?string $navigationLabel = 'Slider Hero';

    protected static ?string $modelLabel = 'Slider';

    protected static ?string $pluralModelLabel = 'Slider Hero';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Konten')->schema([
                Forms\Components\TextInput::make('title')->label('Judul Besar')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('subtitle')->label('Subjudul')->rows(2)->columnSpanFull(),
                Forms\Components\FileUpload::make('image')->label('Gambar Latar')->image()->disk('public')->directory('sliders')
                    ->imageEditor()->imageEditorAspectRatios(['16:9'])->columnSpanFull()
                    ->helperText('Rasio 16:9 (lanskap). Disarankan 1920×1080 px. JPG/PNG/WEBP, maks 2MB.'),
            ]),
            Forms\Components\Section::make('Tombol')->schema([
                Forms\Components\TextInput::make('cta_label')->label('Teks Tombol 1')->default('Jelajahi Buku'),
                Forms\Components\TextInput::make('cta_url')->label('URL Tombol 1')->default('/katalog'),
                Forms\Components\TextInput::make('cta2_label')->label('Teks Tombol 2'),
                Forms\Components\TextInput::make('cta2_url')->label('URL Tombol 2'),
            ])->columns(2),
            Forms\Components\Section::make('Pengaturan')->schema([
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('Gambar')->height(56)->width(100),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(50),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage settings') ?? false;
    }
}
