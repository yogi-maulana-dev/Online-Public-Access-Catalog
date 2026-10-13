<x-guest-layout title="Verifikasi Email">
    <div class="mb-6 text-center">
        <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h1 class="font-serif text-2xl font-bold text-stone-900">Verifikasi Email Anda</h1>
        <p class="mt-2 text-sm text-stone-500">
            Terima kasih telah mendaftar! Kami telah mengirim tautan verifikasi ke email Anda.
            Silakan klik tautan tersebut untuk mengaktifkan akun. Belum menerima email? Kirim ulang di bawah.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg bg-brand-50 px-4 py-3 text-sm font-medium text-brand-700 ring-1 ring-brand-100">
            ✓ Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-3"
          x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <x-spinner-submit label="Kirim Ulang Email Verifikasi" loading="Mengirim…" />
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-stone-500 hover:text-brand-700">Keluar</button>
    </form>
</x-guest-layout>
