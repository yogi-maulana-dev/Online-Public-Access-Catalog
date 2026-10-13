@php
    // Generate a simple math challenge and store the answer in the session.
    $captchaA = random_int(2, 9);
    $captchaB = random_int(1, 9);
    session(['captcha_answer' => $captchaA + $captchaB]);
@endphp

<div x-data="{ reload() { window.location.reload(); } }">
    <x-input-label for="captcha" value="Verifikasi Keamanan" />
    <div class="mt-1 flex items-center gap-3">
        <div class="flex h-[44px] shrink-0 select-none items-center gap-2 rounded-lg bg-stone-100 px-4 font-mono text-lg font-bold tracking-widest text-brand-800 ring-1 ring-stone-200">
            <span>{{ $captchaA }}</span>
            <span class="text-stone-400">+</span>
            <span>{{ $captchaB }}</span>
            <span class="text-stone-400">=</span>
            <span class="text-stone-400">?</span>
        </div>
        <x-text-input id="captcha" name="captcha" type="number" inputmode="numeric" class="flex-1" placeholder="Hasil" required autocomplete="off" />
    </div>
    <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
</div>
