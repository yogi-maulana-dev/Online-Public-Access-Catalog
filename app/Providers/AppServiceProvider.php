<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compatible with older MySQL/MyISAM index limits (max key length).
        Schema::defaultStringLength(191);

        // Super Admin bypasses every permission check.
        Gate::before(fn ($user) => $user->hasRole('Super Admin') ? true : null);

        // Apply admin-configured mail settings (from DB) over .env defaults.
        $this->applyMailSettingsFromDatabase();

        // Indonesian password-reset email.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', ['token' => $token], false))
                .'?email='.urlencode($notifiable->getEmailForPasswordReset());

            $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi — '.config('app.name'))
                ->greeting('Halo!')
                ->line('Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun Anda.')
                ->action('Atur Ulang Kata Sandi', $url)
                ->line("Tautan ini akan kedaluwarsa dalam {$expire} menit.")
                ->line('Jika Anda tidak meminta atur ulang kata sandi, abaikan email ini.')
                ->salutation('Salam, '.config('app.name'));
        });

        // Indonesian email-verification message.
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifikasi Alamat Email — '.config('app.name'))
                ->greeting('Halo, '.$notifiable->name.'!')
                ->line('Terima kasih telah mendaftar. Silakan verifikasi alamat email Anda dengan menekan tombol di bawah ini.')
                ->action('Verifikasi Email', $url)
                ->line('Jika Anda tidak membuat akun, abaikan email ini.')
                ->salutation('Salam, '.config('app.name'));
        });
    }

    /**
     * Override mail config with values managed by the admin in the settings table.
     */
    private function applyMailSettingsFromDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        try {
            $host = \App\Models\Setting::get('mail_host');
            if (blank($host)) {
                return; // not configured via admin; keep .env values
            }

            config([
                'mail.default' => \App\Models\Setting::get('mail_mailer', config('mail.default')),
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) \App\Models\Setting::get('mail_port', 2525),
                'mail.mailers.smtp.username' => \App\Models\Setting::get('mail_username'),
                'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password'),
                'mail.mailers.smtp.scheme' => \App\Models\Setting::get('mail_encryption') === 'ssl' ? 'smtps' : null,
                'mail.from.address' => \App\Models\Setting::get('mail_from_address') ?: config('mail.from.address'),
                'mail.from.name' => \App\Models\Setting::get('mail_from_name') ?: config('mail.from.name'),
            ]);
        } catch (\Throwable $e) {
            // Never let mail-config issues break the whole app.
        }
    }
}
