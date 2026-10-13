<x-guest-layout title="Verifikasi Dua Langkah">
    <div class="mb-7 text-center">
        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h1 class="font-serif text-2xl font-bold text-stone-900">Verifikasi Dua Langkah</h1>
        <p class="mt-1 text-sm text-stone-500">Masukkan kode 6 digit dari aplikasi authenticator Anda.</p>
    </div>

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <div>
            <x-input-label for="code" :value="__('Kode Verifikasi')" class="sr-only" />
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus required
                   placeholder="------" maxlength="6"
                   class="w-full rounded-lg border-stone-300 bg-white text-center text-2xl tracking-[0.5em] font-semibold text-stone-800 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <x-spinner-submit label="Verifikasi" loading="Memverifikasi…" />
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button class="text-sm text-stone-500 hover:text-brand-700">← Batalkan & kembali ke login</button>
    </form>
</x-guest-layout>
