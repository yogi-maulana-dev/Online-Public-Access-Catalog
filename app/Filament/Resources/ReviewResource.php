<?php

namespace App\Filament\Resources;

use App\Enums\ReviewRecommendation;
use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Penugasan Review';

    protected static ?string $modelLabel = 'Review';

    protected static ?string $pluralModelLabel = 'Review';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Reviewer') && ! $user->hasAnyRole(['Super Admin', 'Editor', 'Publisher Admin'])) {
            return (string) Review::where('reviewer_id', $user->id)->where('status', 'assigned')->count() ?: null;
        }

        return null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('submission')
                ->label('Naskah')
                ->content(fn (?Review $record) => $record?->submission?->title ?? '—'),
            Forms\Components\Select::make('recommendation')->label('Rekomendasi Anda')
                ->options(collect(ReviewRecommendation::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                ->native(false)->required(),
            Forms\Components\TextInput::make('rating')->label('Nilai Kualitas (1-5)')->numeric()->minValue(1)->maxValue(5),
            Forms\Components\Textarea::make('comments_author')->label('Komentar untuk Penulis')->rows(5)->columnSpanFull(),
            Forms\Components\Textarea::make('comments_editor')->label('Komentar Rahasia untuk Editor')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('submission.reference')->label('Nomor')->searchable(),
                Tables\Columns\TextColumn::make('submission.title')->label('Naskah')->wrap()->limit(50),
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewer')->toggleable(),
                Tables\Columns\TextColumn::make('round')->label('Putaran')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success', 'assigned' => 'warning', 'declined' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('recommendation')->label('Rekomendasi')->badge()
                    ->formatStateUsing(fn (?ReviewRecommendation $state) => $state?->label() ?? '—')
                    ->color(fn (?ReviewRecommendation $state) => $state?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('due_date')->label('Tenggat')->date('d M Y')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'assigned' => 'Ditugaskan', 'completed' => 'Selesai', 'declined' => 'Ditolak',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('submitReview')
                    ->label('Isi Review')
                    ->icon('heroicon-o-pencil-square')
                    ->modalWidth('2xl')
                    ->visible(fn (Review $r) => $r->status !== 'completed' && $r->reviewer_id === auth()->id())
                    ->form([
                        Forms\Components\Select::make('recommendation')->label('Rekomendasi Anda')
                            ->options(collect(ReviewRecommendation::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                            ->native(false)->required(),
                        Forms\Components\TextInput::make('rating')->label('Nilai Kualitas (1-5)')->numeric()->minValue(1)->maxValue(5),
                        Forms\Components\Textarea::make('comments_author')->label('Komentar untuk Penulis')->rows(5),
                        Forms\Components\Textarea::make('comments_editor')->label('Komentar Rahasia untuk Editor')->rows(3),
                    ])
                    ->fillForm(fn (Review $r) => [
                        'recommendation' => $r->recommendation?->value,
                        'rating' => $r->rating,
                        'comments_author' => $r->comments_author,
                        'comments_editor' => $r->comments_editor,
                    ])
                    ->action(fn (Review $r, array $data) => app(\App\Services\SubmissionService::class)->completeReview($r, $data)),
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()->can('manage submissions')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['submission', 'reviewer']);
        $user = auth()->user();

        if ($user && $user->hasRole('Reviewer') && ! $user->hasAnyRole(['Super Admin', 'Editor', 'Publisher Admin'])) {
            $query->where('reviewer_id', $user->id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->can('submit review') || $user?->can('manage submissions') || false;
    }

    public static function canCreate(): bool
    {
        return false; // reviews are created via submission assignment
    }
}
