<section>
    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-stone-900">{{ __('Autentikasi Dua Faktor (2FA)') }}</h2>
            <p class="mt-1 text-sm text-stone-500">{{ __('Tambahkan keamanan ekstra menggunakan aplikasi authenticator (Google Authenticator, Authy, dll).') }}</p>
        </div>
        @if($user->twoFactorEnabled())
            <span class="shrink-0 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-200">Aktif</span>
        @else
            <span class="shrink-0 rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-500">Nonaktif</span>
        @endif
    </header>

    @if($user->twoFactorEnabled())
        {{-- Enabled: allow disabling --}}
        <div class="mt-6 rounded-xl bg-brand-50/60 p-4 ring-1 ring-brand-100">
            <p class="text-sm text-stone-600">{{ __('2FA aktif. Anda akan diminta kode dari aplikasi authenticator setiap kali masuk.') }}</p>
        </div>
        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-5 space-y-4">
            @csrf
            @method('delete')
            <div class="max-w-sm">
                <x-input-label for="tf_password" :value="__('Konfirmasi Kata Sandi untuk Menonaktifkan')" />
                <x-text-input id="tf_password" name="password" type="password" class="mt-1" autocomplete="current-password" />
                <x-input-error :messages="$errors->twoFactorDisable->get('password')" class="mt-2" />
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 transition">
                Nonaktifkan 2FA
            </button>
        </form>

    @elseif($user->two_factor_secret)
        {{-- Secret generated, awaiting confirmation: show QR --}}
        @php $qr = app(\PragmaRX\Google2FAQRCode\Google2FA::class)->getQRCodeInline(config('app.name'), $user->email, $user->two_factor_secret); @endphp
        <div class="mt-6 grid gap-6 sm:grid-cols-[auto_1fr] items-start">
            <div class="rounded-xl border border-stone-200 bg-white p-3">
                <img src="{{ $qr }}" alt="QR Code 2FA" class="h-44 w-44">
            </div>
            <div>
                <ol class="space-y-2 text-sm text-stone-600 list-decimal list-inside">
                    <li>Buka aplikasi authenticator di ponsel Anda.</li>
                    <li>Pindai QR di samping, atau masukkan kunci manual berikut:</li>
                </ol>
                <code class="mt-2 inline-block select-all rounded-lg bg-stone-100 px-3 py-1.5 text-sm font-mono tracking-wider text-stone-800">{{ trim(chunk_split($user->two_factor_secret, 4, ' ')) }}</code>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-5 flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <x-input-label for="tf_code" :value="__('Kode dari Aplikasi')" />
                        <x-text-input id="tf_code" name="code" type="text" inputmode="numeric" maxlength="6" class="mt-1 w-40 tracking-widest" placeholder="123456" autofocus />
                        <x-input-error :messages="$errors->twoFactor->get('code')" class="mt-2" />
                    </div>
                    <x-primary-button class="!w-auto">{{ __('Aktifkan') }}</x-primary-button>
                </form>
            </div>
        </div>

    @else
        {{-- Not set up --}}
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6">
            @csrf
            <x-primary-button class="!w-auto">{{ __('Aktifkan 2FA') }}</x-primary-button>
        </form>
    @endif

    @if (session('status') === 'two-factor-enabled')
        <p class="mt-4 text-sm font-medium text-brand-600">✓ 2FA berhasil diaktifkan.</p>
    @elseif (session('status') === 'two-factor-disabled')
        <p class="mt-4 text-sm font-medium text-stone-600">2FA telah dinonaktifkan.</p>
    @endif
</section>
