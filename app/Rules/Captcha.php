<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Captcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skipped during automated tests (forms aren't rendered, so no session challenge exists).
        if (app()->runningUnitTests()) {
            return;
        }

        if (! is_numeric($value) || (int) $value !== (int) session('captcha_answer')) {
            $fail('Jawaban verifikasi keamanan salah.');
        }
    }
}
