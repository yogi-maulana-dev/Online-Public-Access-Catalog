<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private Google2FA $google2fa)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('auth.2fa.user_id');
        abort_unless($userId, 419);

        $key = 'two-factor:'.$userId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $user = User::findOrFail($userId);

        if (! $this->google2fa->verifyKey($user->two_factor_secret, $request->code, 2)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['code' => 'Kode verifikasi salah.']);
        }

        RateLimiter::clear($key);

        $remember = (bool) $request->session()->pull('auth.2fa.remember', false);
        $request->session()->forget('auth.2fa.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
