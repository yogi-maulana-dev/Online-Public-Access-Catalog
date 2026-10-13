<x-guest-layout title="Lupa Kata Sandi">
    <div class="mb-7">
        <div class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-brand-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.74 5.74L9 19l-2 2H5v-2l4.26-4.26A6 6 0 1121 9z"/></svg>
        </div>
        <h1 class="font-serif text-2xl font-bold text-stone-900">Lupa Kata Sandi?</h1>
        <p class="mt-1 text-sm text-stone-500">Masukkan email Anda dan kami akan mengirim tautan untuk mengatur ulang kata sandi.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" x-bind:readonly="submitting" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-captcha />

        <x-spinner-submit label="Kirim Tautan Reset" loading="Mengirim tautan…" />
        <p x-show="submitting" x-cloak class="text-center text-xs text-stone-400">Mohon tunggu, email sedang dikirim…</p>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">← Kembali ke halaman masuk</a>
    </p>
</x-guest-layout>
