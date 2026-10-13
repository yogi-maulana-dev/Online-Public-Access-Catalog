<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Pengguna';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Akun')->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(191),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(191),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Biarkan kosong jika tidak ingin mengubah.'),
                Forms\Components\Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Profil Akademik')->schema([
                Forms\Components\TextInput::make('title')->label('Gelar')->maxLength(191),
                Forms\Components\TextInput::make('affiliation')->label('Afiliasi')->maxLength(191),
                Forms\Components\TextInput::make('orcid')->label('ORCID')->maxLength(40),
                Forms\Components\FileUpload::make('avatar')->label('Foto')->image()->disk('public')->directory('avatars')->avatar()
                    ->imageEditor()->imageEditorAspectRatios(['1:1'])
                    ->helperText('Foto persegi (1:1). Disarankan 400×400 px. Maks 2MB.'),
                Forms\Components\Textarea::make('bio')->label('Biografi')->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')->label('')->disk('public')->circular()
                    ->defaultImageUrl(fn (User $r) => $r->avatar_url),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()
                    ->description(fn (User $r) => $r->email),
                Tables\Columns\TextColumn::make('roles.name')->label('Peran')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('affiliation')->label('Afiliasi')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Bergabung')->date('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')->relationship('roles', 'name')->label('Peran'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }
}
