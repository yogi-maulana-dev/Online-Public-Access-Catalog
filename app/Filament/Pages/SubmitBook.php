<?php

namespace App\Filament\Pages;

use App\Models\Author;
use App\Models\Book;
use App\Models\Keyword;
use App\Models\Submission;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SubmitBook extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Ajukan Naskah';

    protected static ?string $title = 'Ajukan Naskah Buku';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.submit-book';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('create submission') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('create submission') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Buku')
                    ->description('Lengkapi metadata naskah Anda. Naskah akan ditinjau oleh editor sebelum diterbitkan.')
                    ->schema([
                        Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(191)->columnSpanFull(),
                        Forms\Components\TextInput::make('subtitle')->label('Sub-judul')->columnSpanFull(),
                        Forms\Components\Textarea::make('abstract')->label('Abstrak')->required()->rows(5)->columnSpanFull(),
                        Forms\Components\Select::make('category_id')->label('Kategori')
                            ->options(fn () => \App\Models\Category::orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(),
                        Forms\Components\Select::make('format')->label('Format')
                            ->options(['PDF' => 'PDF', 'E-Book' => 'E-Book', 'Paperback' => 'Paperback'])->default('PDF'),
                        Forms\Components\TagsInput::make('keywords')->label('Kata Kunci')->placeholder('Tambah kata kunci')->columnSpanFull(),
                        Forms\Components\TextInput::make('co_authors')->label('Penulis Pendamping')
                            ->helperText('Pisahkan dengan koma. Anda otomatis menjadi penulis utama.')->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Berkas Naskah')->schema([
                    Forms\Components\FileUpload::make('manuscript')->label('Manuskrip (PDF)')
                        ->acceptedFileTypes(['application/pdf'])->maxSize(20480)
                        ->disk('local')->directory('protected/submissions')->required()
                        ->helperText('Format PDF, maksimal 20MB.'),
                    Forms\Components\FileUpload::make('cover')->label('Usulan Sampul (opsional)')
                        ->image()->disk('public')->directory('covers')
                        ->imageEditor()->imageEditorAspectRatios(['2:3'])
                        ->helperText('Rasio 2:3 (potret). Disarankan 600×900 px. Maks 2MB.'),
                ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        DB::transaction(function () use ($data, $user) {
            $book = Book::create([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'abstract' => $data['abstract'],
                'category_id' => $data['category_id'],
                'format' => $data['format'] ?? 'PDF',
                'cover_image' => $data['cover'] ?? null,
                'submitted_by' => $user->id,
                'status' => 'draft',
                'year' => now()->year,
                'is_open_access' => true,
            ]);

            // Primary author = the submitting user (find or create an Author profile).
            $author = Author::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $user->name, 'title' => $user->title, 'affiliation' => $user->affiliation, 'email' => $user->email],
            );
            $book->authors()->attach($author->id, ['role' => 'author', 'sequence' => 1]);

            // Co-authors.
            $seq = 2;
            foreach (array_filter(array_map('trim', explode(',', $data['co_authors'] ?? ''))) as $name) {
                $co = Author::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($name)], ['name' => $name]);
                $book->authors()->syncWithoutDetaching([$co->id => ['role' => 'author', 'sequence' => $seq++]]);
            }

            // Keywords.
            $keywordIds = collect($data['keywords'] ?? [])->map(fn ($k) => Keyword::findOrCreateByName($k)->id)->all();
            $book->keywords()->sync($keywordIds);

            // Manuscript file.
            if (! empty($data['manuscript'])) {
                $book->files()->create([
                    'uploaded_by' => $user->id,
                    'type' => 'manuscript',
                    'disk' => 'local',
                    'path' => $data['manuscript'],
                    'original_name' => basename($data['manuscript']),
                    'mime_type' => 'application/pdf',
                    'is_public' => false,
                ]);
            }

            Submission::create([
                'book_id' => $book->id,
                'author_id' => $user->id,
                'title' => $book->title,
                'abstract' => $book->abstract,
                'status' => 'pending',
                'stage' => 'submission',
            ]);
        });

        Notification::make()->title('Naskah berhasil diajukan')
            ->body('Tim editorial akan meninjau naskah Anda. Anda dapat memantau statusnya di menu Naskah Masuk.')
            ->success()->send();

        $this->form->fill();
    }
}
