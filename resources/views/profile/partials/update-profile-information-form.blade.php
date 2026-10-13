<section>
    <header>
        <h2 class="text-lg font-semibold text-stone-900">{{ __('Informasi Profil') }}</h2>
        <p class="mt-1 text-sm text-stone-500">{{ __('Perbarui foto, nama, dan alamat email akun Anda.') }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Avatar --}}
        <div x-data="{ preview: '{{ $user->avatar_url }}' }" class="flex items-center gap-5">
            <img :src="preview" alt="Foto profil" class="h-20 w-20 rounded-full object-cover ring-2 ring-stone-200">
            <div>
                <x-input-label for="avatar" :value="__('Foto Profil')" />
                <input id="avatar" name="avatar" type="file" accept="image/*"
                       @change="const f=$event.target.files[0]; if(f) preview=URL.createObjectURL(f)"
                       class="mt-1 block w-full text-sm text-stone-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-1 text-xs text-stone-400">Foto persegi (1:1), disarankan 400×400 px. JPG/PNG/WEBP, maks 2MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-stone-700">
                        {{ __('Alamat email Anda belum terverifikasi.') }}
                        <button form="send-verification" class="underline text-sm text-stone-600 hover:text-stone-900">
                            {{ __('Klik di sini untuk mengirim ulang verifikasi.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-brand-600">{{ __('Tautan verifikasi baru telah dikirim.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="affiliation" :value="__('Afiliasi / Instansi')" />
            <x-text-input id="affiliation" name="affiliation" type="text" class="mt-1" :value="old('affiliation', $user->affiliation)" autocomplete="organization" placeholder="Mis. Fakultas Teknik" />
            <x-input-error class="mt-2" :messages="$errors->get('affiliation')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="!w-auto">{{ __('Simpan') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm text-brand-600 font-medium">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
