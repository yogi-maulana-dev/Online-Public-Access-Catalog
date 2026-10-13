<?php

namespace App\Filament\Resources;

use App\Enums\SubmissionStatus;
use App\Filament\Resources\SubmissionResource\Pages;
use App\Filament\Resources\SubmissionResource\RelationManagers\ReviewsRelationManager;
use App\Models\Submission;
use App\Models\User;
use App\Services\SubmissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Naskah Masuk';

    protected static ?string $modelLabel = 'Naskah';

    protected static ?string $pluralModelLabel = 'Naskah';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', SubmissionStatus::Pending->value)->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Naskah')->schema([
                Forms\Components\TextInput::make('reference')->label('Nomor')->disabled(),
                Forms\Components\TextInput::make('title')->label('Judul')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('abstract')->label('Abstrak')->rows(4)->columnSpanFull(),
                Forms\Components\Select::make('book_id')->label('Buku')->relationship('book', 'title')->searchable()->required(),
            ])->columns(2),
            Forms\Components\Section::make('Editorial')->schema([
                Forms\Components\Select::make('author_id')->label('Pengusul')->relationship('author', 'name')->searchable()->required(),
                Forms\Components\Select::make('editor_id')->label('Editor')->relationship('editor', 'name')->searchable(),
                Forms\Components\Select::make('status')->label('Status')
                    ->options(collect(SubmissionStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->native(false)->required(),
                Forms\Components\TextInput::make('current_round')->label('Putaran')->numeric()->default(1),
                Forms\Components\Textarea::make('decision_note')->label('Catatan Keputusan')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Nomor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(50)
                    ->description(fn (Submission $r) => 'oleh '.$r->author?->name),
                Tables\Columns\TextColumn::make('editor.name')->label('Editor')->badge()->color('gray')->placeholder('Belum ditugaskan'),
                Tables\Columns\TextColumn::make('reviews_count')->label('Review')->counts('reviews')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (SubmissionStatus $state) => $state->label())
                    ->color(fn (SubmissionStatus $state) => $state->color()),
                Tables\Columns\TextColumn::make('submitted_at')->label('Diajukan')->date('d M Y')->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(SubmissionStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('assignReviewer')
                        ->label('Tugaskan Reviewer')
                        ->icon('heroicon-o-user-plus')
                        ->visible(fn () => auth()->user()->can('assign reviewers'))
                        ->form([
                            Forms\Components\Select::make('reviewer_id')->label('Reviewer')
                                ->options(fn () => User::role('Reviewer')->pluck('name', 'id'))
                                ->searchable()->required(),
                            Forms\Components\DatePicker::make('due_date')->label('Tenggat'),
                        ])
                        ->action(fn (Submission $r, array $data) => app(SubmissionService::class)
                            ->assignReviewer($r, User::find($data['reviewer_id']), $data['due_date'] ?? null)),
                    Tables\Actions\Action::make('requestRevisions')
                        ->label('Minta Revisi')->icon('heroicon-o-arrow-path')->color('warning')
                        ->visible(fn (Submission $r) => auth()->user()->can('manage submissions') && $r->isOpen())
                        ->form([Forms\Components\Textarea::make('note')->label('Catatan')->required()])
                        ->action(fn (Submission $r, array $data) => app(SubmissionService::class)->requestRevisions($r, $data['note'])),
                    Tables\Actions\Action::make('accept')
                        ->label('Terima')->icon('heroicon-o-check-circle')->color('success')
                        ->visible(fn (Submission $r) => auth()->user()->can('manage submissions') && $r->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (Submission $r) => app(SubmissionService::class)->accept($r)),
                    Tables\Actions\Action::make('publish')
                        ->label('Terbitkan')->icon('heroicon-o-rocket-launch')->color('success')
                        ->visible(fn (Submission $r) => auth()->user()->can('publish books') && $r->status === SubmissionStatus::Accepted)
                        ->requiresConfirmation()
                        ->action(fn (Submission $r) => app(SubmissionService::class)->publish($r)),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')->icon('heroicon-o-x-circle')->color('danger')
                        ->visible(fn (Submission $r) => auth()->user()->can('manage submissions') && $r->isOpen())
                        ->form([Forms\Components\Textarea::make('note')->label('Alasan')->required()])
                        ->action(fn (Submission $r, array $data) => app(SubmissionService::class)->reject($r, $data['note'])),
                    Tables\Actions\EditAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'create' => Pages\CreateSubmission::route('/create'),
            'edit' => Pages\EditSubmission::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['author', 'editor']);
        $user = auth()->user();

        // Authors only see their own submissions; reviewers see ones they're invited to.
        if ($user && ! $user->hasAnyRole(['Super Admin', 'Publisher Admin', 'Editor'])) {
            if ($user->hasRole('Reviewer')) {
                $query->whereHas('reviews', fn (Builder $q) => $q->where('reviewer_id', $user->id));
            } else {
                $query->where('author_id', $user->id);
            }
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view submissions') ?? false;
    }
}
