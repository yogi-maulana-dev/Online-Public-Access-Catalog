<x-guest-layout title="Masuk">
    <div class="mb-7">
        <h1 class="font-serif text-2xl font-bold text-stone-900">Selamat Datang Kembali</h1>
        <p class="mt-1 text-sm text-stone-500">Masuk untuk mengakses pustaka digital Anda.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-2.5 text-stone-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <x-text-input id="email" class="pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-2.5 text-stone-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <x-text-input id="password" class="pl-10 pr-10" ::type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" @click="show=!show" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600">
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9.27-3.11-11-7a18.5 18.5 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c5 0 9.27 3.11 11 7a18.5 18.5 0 01-2.16 3.19M3 3l18 18"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-brand-700 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Ingat saya') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-brand-700 hover:text-brand-800" href="{{ route('password.request') }}">
                    Lupa sandi?
                </a>
            @endif
        </div>

        <x-captcha />

        <x-spinner-submit label="Masuk" loading="Memproses…" />
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:text-brand-800">Daftar gratis</a>
    </p>
</x-guest-layout>
