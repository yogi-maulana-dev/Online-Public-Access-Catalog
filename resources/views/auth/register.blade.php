<x-guest-layout title="Daftar">
    <div class="mb-7">
        <h1 class="font-serif text-2xl font-bold text-stone-900">Buat Akun Baru</h1>
        <p class="mt-1 text-sm text-stone-500">Bergabung untuk membaca, mengunduh, dan menerbitkan karya.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <div class="relative">
                <x-text-input id="password" class="pr-10" ::type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <button type="button" @click="show=!show" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600">
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/></svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9.27-3.11-11-7a18.5 18.5 0 015.06-5.94"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-captcha />

        <x-spinner-submit label="Daftar Sekarang" loading="Mendaftarkan…" />
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">Masuk di sini</a>
    </p>
</x-guest-layout>
