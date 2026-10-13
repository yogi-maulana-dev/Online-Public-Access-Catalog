<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct(private Google2FA $google2fa)
    {
    }

    /**
     * Generate a secret (unconfirmed) and reveal the QR for the user to scan.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->two_factor_secret || $user->twoFactorEnabled()) {
            $user->forceFill([
                'two_factor_secret' => $this->google2fa->generateSecretKey(),
                'two_factor_confirmed_at' => null,
            ])->save();
        }

        return back()->with('status', 'two-factor-setup');
    }

    /**
     * Verify the first OTP and activate 2FA.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();

        abort_unless($user->two_factor_secret, 404);

        if (! $this->google2fa->verifyKey($user->two_factor_secret, $request->code, 2)) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi salah. Coba lagi.',
            ])->errorBag('twoFactor');
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('status', 'two-factor-enabled');
    }

    /**
     * Disable 2FA (requires the current password).
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validateWithBag('twoFactorDisable', [
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->disableTwoFactor();

        return back()->with('status', 'two-factor-disabled');
    }

    /**
     * Inline QR code (data URI) for the current unconfirmed secret.
     */
    public function qrCode(Request $request): string
    {
        $user = $request->user();

        return $this->google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
        );
    }
}
