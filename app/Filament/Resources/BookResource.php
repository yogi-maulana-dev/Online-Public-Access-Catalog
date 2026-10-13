<?php

namespace App\Filament\Resources;

use App\Enums\BookStatus;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Buku';

    protected static ?string $modelLabel = 'Buku';

    protected static ?string $pluralModelLabel = 'Buku';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Informasi Utama')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                        ->maxLength(191)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('subtitle')
                        ->label('Sub-judul')
                        ->maxLength(191)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(191),
                    Forms\Components\Textarea::make('abstract')
                        ->label('Abstrak')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Kontributor & Klasifikasi')
                    ->description('Cari & pilih dari data yang ada, atau klik tombol + untuk menambah baru — data baru langsung tersimpan dan jadi pilihan.')
                    ->schema([
                    Forms\Components\Select::make('authors')
                        ->label('Penulis')
                        ->relationship('authors', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(191),
                            Forms\Components\TextInput::make('title')->label('Gelar Akademik')->placeholder('Dr., M.Kom.'),
                            Forms\Components\TextInput::make('affiliation')->label('Afiliasi / Instansi'),
                            Forms\Components\TextInput::make('email')->email(),
                        ])
                        ->createOptionModalHeading('Tambah Penulis Baru'),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nama Kategori')->required()->maxLength(191),
                            Forms\Components\TextInput::make('icon')->label('Ikon (emoji)')->placeholder('📘'),
                            Forms\Components\Select::make('parent_id')->label('Induk (opsional)')
                                ->options(fn () => \App\Models\Category::orderBy('name')->pluck('name', 'id'))
                                ->searchable(),
                        ])
                        ->createOptionModalHeading('Tambah Kategori Baru'),
                    Forms\Components\Select::make('publisher_id')
                        ->label('Penerbit')
                        ->relationship('publisher', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nama Penerbit')->required()->maxLength(191),
                            Forms\Components\TextInput::make('city')->label('Kota'),
                            Forms\Components\TextInput::make('email')->email(),
                        ])
                        ->createOptionModalHeading('Tambah Penerbit Baru'),
                    Forms\Components\Select::make('keywords')
                        ->label('Kata Kunci')
                        ->relationship('keywords', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Kata Kunci')->required(),
                        ])
                        ->createOptionModalHeading('Tambah Kata Kunci Baru'),
                ])->columns(2),

                Forms\Components\Section::make('Metadata Penerbitan')->schema([
                    Forms\Components\TextInput::make('isbn')->maxLength(20),
                    Forms\Components\TextInput::make('eisbn')->label('E-ISBN')->maxLength(20),
                    Forms\Components\TextInput::make('doi')->label('DOI')->maxLength(191),
                    Forms\Components\TextInput::make('year')->label('Tahun')->numeric()->minValue(1900)->maxValue(2100),
                    Forms\Components\DatePicker::make('publication_date')->label('Tanggal Terbit'),
                    Forms\Components\TextInput::make('edition')->label('Edisi'),
                    Forms\Components\TextInput::make('pages')->label('Jumlah Halaman')->numeric(),
                    Forms\Components\Select::make('format')->options([
                        'PDF' => 'PDF', 'E-Book' => 'E-Book', 'Hardcover' => 'Hardcover', 'Paperback' => 'Paperback',
                    ])->default('PDF')->required(),
                    Forms\Components\TextInput::make('language')->label('Bahasa')->default('Indonesia')->required(),
                    Forms\Components\TextInput::make('series')->label('Seri'),
                    Forms\Components\TextInput::make('license')->label('Lisensi')->placeholder('CC BY 4.0'),
                ])->columns(3),
            ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Sampul')->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('Gambar Sampul')
                        ->image()
                        ->disk('public')
                        ->directory('covers')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['2:3'])
                        ->helperText('Rasio 2:3 (potret). Disarankan 600×900 px. JPG/PNG/WEBP, maks 2MB.'),
                ]),
                Forms\Components\Section::make('Publikasi')->schema([
                    Forms\Components\Select::make('status')
                        ->options(collect(BookStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                        ->default('draft')
                        ->required()
                        ->native(false),
                    Forms\Components\DateTimePicker::make('published_at')->label('Diterbitkan pada'),
                    Forms\Components\Toggle::make('is_featured')->label('Tampilkan sebagai unggulan'),
                    Forms\Components\Toggle::make('is_open_access')->label('Akses Terbuka (gratis)')->default(true),
                    Forms\Components\TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')
                        ->helperText('Kosongkan atau 0 jika gratis. Harga tampil di katalog & detail buku.'),
                ]),

                Forms\Components\Section::make('Berkas Digital (PDF)')
                    ->description('Opsional. Tanpa PDF, buku tampil sebagai koleksi katalog saja (cover & info, tanpa tombol Baca/Unduh).')
                    ->schema([
                        Forms\Components\Placeholder::make('current_pdf')
                            ->label('Berkas saat ini')
                            ->content(fn (?Book $record) => $record?->pdfFile()
                                ? $record->pdfFile()->original_name.' ('.$record->pdfFile()->human_size.')'
                                : 'Belum ada — koleksi katalog (cover saja).'),
                        Forms\Components\FileUpload::make('pdf_file')
                            ->label('Unggah / Ganti PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200)
                            ->disk('local')
                            ->directory('protected/books')
                            ->storeFileNamesIn('pdf_file_name')
                            ->helperText('Maks 50MB. Tersimpan terproteksi (unduhan tercatat & bisa dibatasi).'),
                        Forms\Components\Toggle::make('remove_pdf')
                            ->label('Hapus berkas PDF (jadikan koleksi cover saja)')
                            ->visible(fn (?Book $record) => (bool) $record?->pdfFile()),
                    ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->disk('public')
                    ->height(56)
                    ->width(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->description(fn (Book $r) => $r->authorNames())
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('year')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (BookStatus $state) => $state->label())
                    ->color(fn (BookStatus $state) => $state->color()),
                Tables\Columns\IconColumn::make('is_featured')->label('Unggulan')->boolean(),
                Tables\Columns\TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('download_count')->label('Unduh')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(BookStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Unggulan'),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Terbitkan')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn (Book $r) => $r->status !== BookStatus::Published && auth()->user()->can('publish books'))
                    ->requiresConfirmation()
                    ->action(fn (Book $r) => app(\App\Services\BookService::class)->publish($r)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage books') ?? false;
    }
}
