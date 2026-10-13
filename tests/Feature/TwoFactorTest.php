<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function google2fa(): Google2FA
    {
        return app(Google2FA::class);
    }

    public function test_user_can_enable_and_confirm_two_factor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'))->assertRedirect();
        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->twoFactorEnabled());

        $otp = $this->google2fa()->getCurrentOtp($user->two_factor_secret);
        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $otp])
            ->assertSessionHas('status', 'two-factor-enabled');

        $this->assertTrue($user->refresh()->twoFactorEnabled());
    }

    public function test_login_with_two_factor_requires_challenge(): void
    {
        $secret = $this->google2fa()->generateSecretKey();
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Login is intercepted by the 2FA challenge (user not yet authenticated).
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        // Submitting the correct OTP completes authentication.
        $otp = $this->google2fa()->getCurrentOtp($secret);
        $this->post(route('two-factor.login'), ['code' => $otp])->assertRedirect();
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_wrong_otp_is_rejected(): void
    {
        $secret = $this->google2fa()->generateSecretKey();
        $user = User::factory()->create();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_user_can_disable_two_factor(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $this->google2fa()->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($user)->delete(route('two-factor.disable'), ['password' => 'password'])
            ->assertRedirect();

        $this->assertFalse($user->refresh()->twoFactorEnabled());
        $this->assertNull($user->two_factor_secret);
    }
}
