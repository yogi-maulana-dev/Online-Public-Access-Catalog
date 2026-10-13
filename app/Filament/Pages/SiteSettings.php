<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Konten Situs';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    /** Keys + defaults shown in the form (mail defaults pulled from current config). */
    private function defaults(): array
    {
        return [
            'site_name' => 'University Press',
            'site_tagline' => 'Digital University Press & Online Book Store',
            'site_logo' => null,
            'stat_faculties' => '8',
            'rector_name' => '', 'rector_title' => '', 'rector_position' => '', 'rector_quote' => '', 'rector_photo' => null,
            'contact_address' => '', 'contact_email' => '', 'contact_phone' => '',
            'social_facebook' => '', 'social_instagram' => '', 'social_youtube' => '', 'social_twitter' => '',
            'support_credit' => 'Didukung oleh Tim IT dan Pegawai Perpustakaan Universitas Muhammadiyah Lampung',
            'developer_name' => 'yogi-maulana-dev',
            'developer_url' => 'https://github.com/yogi-maulana-dev',
            'developer_email' => '',
            // Email / SMTP
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => (string) config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.scheme') === 'smtps' ? 'ssl' : '',
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage settings') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('manage settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(
            collect($this->defaults())->mapWithKeys(fn ($default, $k) => [$k => Setting::get($k, $default)])->all()
        );
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Umum')->schema([
                Forms\Components\TextInput::make('site_name')->label('Nama Kampus / Situs'),
                Forms\Components\TextInput::make('site_tagline')->label('Tagline'),
                Forms\Components\TextInput::make('stat_faculties')->label('Jumlah Fakultas')->numeric(),
                Forms\Components\FileUpload::make('site_logo')->label('Logo Kampus')->image()->disk('public')->directory('branding')
                    ->imageEditor()->columnSpanFull()
                    ->helperText('PNG transparan, persegi. Disarankan 512×512 px (maks 1MB). Tampil di navbar & footer.'),
            ])->columns(3),

            Forms\Components\Section::make('Email / SMTP')
                ->description('Atur server email pengiriman (verifikasi, reset kata sandi, notifikasi). Berlaku langsung tanpa mengubah .env.')
                ->icon('heroicon-o-envelope')
                ->schema([
                    Forms\Components\Select::make('mail_mailer')->label('Driver')
                        ->options(['smtp' => 'SMTP', 'log' => 'Log (uji coba, tidak terkirim)', 'sendmail' => 'Sendmail'])
                        ->default('smtp')->native(false),
                    Forms\Components\TextInput::make('mail_host')->label('Host')->placeholder('smtp.gmail.com'),
                    Forms\Components\TextInput::make('mail_port')->label('Port')->numeric()->placeholder('2525 / 465 / 587'),
                    Forms\Components\Select::make('mail_encryption')->label('Enkripsi')
                        ->options(['' => 'Tidak ada', 'tls' => 'TLS', 'ssl' => 'SSL'])->native(false),
                    Forms\Components\TextInput::make('mail_username')->label('Username')->autocomplete('off'),
                    Forms\Components\TextInput::make('mail_password')->label('Password')->password()->revealable()->autocomplete('new-password')
                        ->helperText('Kosongkan jika tidak ingin mengubah.'),
                    Forms\Components\TextInput::make('mail_from_address')->label('Email Pengirim')->email()->placeholder('no-reply@kampus.ac.id'),
                    Forms\Components\TextInput::make('mail_from_name')->label('Nama Pengirim')->placeholder('University Press'),
                ])->columns(2),

            Forms\Components\Section::make('Sambutan Rektor')->schema([
                Forms\Components\TextInput::make('rector_name')->label('Nama Rektor'),
                Forms\Components\TextInput::make('rector_title')->label('Gelar / Jabatan'),
                Forms\Components\TextInput::make('rector_position')->label('Periode / Posisi'),
                Forms\Components\FileUpload::make('rector_photo')->label('Foto Rektor')->image()->disk('public')->directory('rector')
                    ->imageEditor()->imageEditorAspectRatios(['3:4', '1:1'])
                    ->helperText('Rasio 3:4 (potret). Disarankan 600×800 px. Maks 2MB.'),
                Forms\Components\Textarea::make('rector_quote')->label('Kutipan Sambutan')->rows(4)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Kontak & Sosial Media')->schema([
                Forms\Components\Textarea::make('contact_address')->label('Alamat')->rows(2)->columnSpanFull(),
                Forms\Components\TextInput::make('contact_email')->label('Email')->email(),
                Forms\Components\TextInput::make('contact_phone')->label('Telepon'),
                Forms\Components\TextInput::make('social_facebook')->label('Facebook')->url(),
                Forms\Components\TextInput::make('social_instagram')->label('Instagram')->url(),
                Forms\Components\TextInput::make('social_youtube')->label('YouTube')->url(),
                Forms\Components\TextInput::make('social_twitter')->label('Twitter / X')->url(),
            ])->columns(2),

            Forms\Components\Section::make('Kredit Footer')->schema([
                Forms\Components\TextInput::make('support_credit')->label('Teks Pendukung')->columnSpanFull()
                    ->helperText('Mis. "Didukung oleh Tim IT dan Pegawai Perpustakaan ...".'),
                Forms\Components\TextInput::make('developer_name')->label('Nama Pengembang'),
                Forms\Components\TextInput::make('developer_url')->label('Link Pengembang (GitHub/Web)')->url(),
                Forms\Components\TextInput::make('developer_email')->label('Email Pengembang')->email(),
            ])->columns(2),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Kirim Email Uji')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('to')->label('Kirim ke')->email()->required()
                        ->default(auth()->user()->email),
                ])
                ->action(function (array $data) {
                    $this->applyMailConfig($this->form->getState());
                    try {
                        Mail::raw('Ini adalah email uji dari panel '.config('app.name').'. Jika Anda menerima email ini, konfigurasi SMTP Anda sudah benar.',
                            fn ($m) => $m->to($data['to'])->subject('Email Uji — '.config('app.name')));
                        Notification::make()->title('Email uji terkirim ke '.$data['to'])->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Gagal mengirim email')->body($e->getMessage())->danger()->persistent()->send();
                    }
                }),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            // Don't overwrite the saved SMTP password with a blank value.
            if ($key === 'mail_password' && blank($value)) {
                continue;
            }

            $group = match (true) {
                str_starts_with($key, 'rector_') => 'rector',
                str_starts_with($key, 'contact_'), str_starts_with($key, 'social_') => 'contact',
                str_starts_with($key, 'mail_') => 'mail',
                default => 'general',
            };

            Setting::set($key, $value, $group);
        }

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }

    /**
     * Apply mail settings from a data array to the runtime config (used by the test action).
     *
     * @param  array<string, mixed>  $data
     */
    private function applyMailConfig(array $data): void
    {
        config([
            'mail.default' => $data['mail_mailer'] ?? 'smtp',
            'mail.mailers.smtp.host' => $data['mail_host'] ?? null,
            'mail.mailers.smtp.port' => (int) ($data['mail_port'] ?? 2525),
            'mail.mailers.smtp.username' => $data['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $data['mail_password'] ?? null,
            'mail.mailers.smtp.scheme' => ($data['mail_encryption'] ?? '') === 'ssl' ? 'smtps' : null,
            'mail.from.address' => $data['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $data['mail_from_name'] ?? config('mail.from.name'),
        ]);
    }
}
