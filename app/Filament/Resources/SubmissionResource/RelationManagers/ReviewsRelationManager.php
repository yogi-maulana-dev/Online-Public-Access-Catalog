<?php

namespace App\Filament\Resources\SubmissionResource\RelationManagers;

use App\Enums\ReviewRecommendation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Review';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('reviewer_id')->label('Reviewer')
                ->options(fn () => User::role('Reviewer')->pluck('name', 'id'))
                ->searchable()->required(),
            Forms\Components\TextInput::make('round')->label('Putaran')->numeric()->default(1),
            Forms\Components\Select::make('recommendation')->label('Rekomendasi')
                ->options(collect(ReviewRecommendation::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                ->native(false),
            Forms\Components\TextInput::make('rating')->label('Nilai (1-5)')->numeric()->minValue(1)->maxValue(5),
            Forms\Components\Textarea::make('comments_author')->label('Komentar untuk Penulis')->columnSpanFull(),
            Forms\Components\Textarea::make('comments_editor')->label('Komentar Rahasia (Editor)')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reviewer_id')
            ->columns([
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewer'),
                Tables\Columns\TextColumn::make('round')->label('Putaran')->badge(),
                Tables\Columns\TextColumn::make('recommendation')->label('Rekomendasi')->badge()
                    ->formatStateUsing(fn (?ReviewRecommendation $state) => $state?->label() ?? '—')
                    ->color(fn (?ReviewRecommendation $state) => $state?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('rating')->label('Nilai'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('completed_at')->label('Selesai')->dateTime('d M Y')->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tugaskan Reviewer'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
