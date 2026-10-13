@php
    $social = [
        'social_facebook' => 'M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6v1.9h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z',
        'social_instagram' => 'M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.3 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .3-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.3-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.3 2.2-.4 1.3-.1 1.7-.1 4.9-.1zM12 7.4a4.6 4.6 0 100 9.2 4.6 4.6 0 000-9.2zm0 7.6a3 3 0 110-6 3 3 0 010 6zm5.9-7.8a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0z',
        'social_youtube' => 'M23 8.5a3 3 0 00-2.1-2.1C19 6 12 6 12 6s-7 0-8.9.4A3 3 0 001 8.5 31 31 0 001 12a31 31 0 00.1 3.5 3 3 0 002.1 2.1C5 18 12 18 12 18s7 0 8.9-.4a3 3 0 002.1-2.1A31 31 0 0023 12a31 31 0 00-.1-3.5zM10 15V9l5 3-5 3z',
        'social_twitter' => 'M18.9 1.2h3.7l-8 9.2L24 22.8h-7.4l-5.8-7.6-6.6 7.6H.5l8.6-9.8L0 1.2h7.6l5.2 6.9 6.1-6.9zm-1.3 19.4h2L6.5 3.3h-2L17.6 20.6z',
    ];
@endphp
<footer id="kontak" class="bg-brand-950 text-brand-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 grid gap-10 md:grid-cols-2 lg:grid-cols-5">
        {{-- About --}}
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-white rounded-full p-1">
                    <x-site-logo imgClass="h-11 w-11 object-contain" fallbackClass="h-11 w-11 text-lg" />
                </div>
                <div>
                    <div class="font-serif text-lg font-bold text-white">{{ \App\Models\Setting::get('site_name', 'University Press') }}</div>
                    <div class="text-[11px] uppercase tracking-widest text-gold-400">Digital Press &amp; Book Store</div>
                </div>
            </div>
            <p class="text-sm text-brand-200 leading-relaxed max-w-md">
                {{ \App\Models\Setting::get('site_tagline', 'Penerbit universitas modern yang menghadirkan karya ilmiah dosen dan akademisi dengan standar penerbitan internasional.') }}
            </p>
            <div class="mt-5 flex gap-2">
                @foreach($social as $key => $path)
                    @if($url = \App\Models\Setting::get($key))
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 hover:bg-gold-500 hover:text-brand-950 transition">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Links --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Jelajah</h3>
            <ul class="space-y-2.5 text-sm text-brand-200">
                <li><a href="{{ route('catalog') }}" class="hover:text-gold-400">Katalog Buku</a></li>
                <li><a href="{{ route('authors.index') }}" class="hover:text-gold-400">Penulis</a></li>
                <li><a href="{{ route('catalog', ['sort' => 'popular']) }}" class="hover:text-gold-400">Terpopuler</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-gold-400">Ajukan Naskah</a></li>
                <li><a href="{{ url('/oai?verb=Identify') }}" class="hover:text-gold-400">OAI-PMH</a></li>
            </ul>
        </div>

        {{-- Contact --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Kontak</h3>
            <ul class="space-y-3 text-sm text-brand-200">
                <li class="flex gap-2"><span>📍</span><span>{{ \App\Models\Setting::get('contact_address', 'Indonesia') }}</span></li>
                <li class="flex gap-2"><span>✉</span><a href="mailto:{{ \App\Models\Setting::get('contact_email') }}" class="hover:text-gold-400">{{ \App\Models\Setting::get('contact_email', 'press@univ.ac.id') }}</a></li>
                <li class="flex gap-2"><span>☎</span><span>{{ \App\Models\Setting::get('contact_phone', '+62 721 123 456') }}</span></li>
            </ul>
        </div>

        {{-- Newsletter --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Newsletter</h3>
            <p class="text-sm text-brand-200 mb-3">Dapatkan info terbitan terbaru.</p>
            <form x-data="{ sent:false }" @submit.prevent="sent=true" class="space-y-2">
                <input type="email" required placeholder="Email Anda"
                       class="w-full rounded-lg border-0 bg-white/10 text-sm text-white placeholder-brand-300 focus:ring-2 focus:ring-gold-400">
                <button class="w-full rounded-lg bg-gold-500 px-4 py-2 text-sm font-semibold text-brand-950 hover:bg-gold-400 transition">
                    <span x-show="!sent">Berlangganan</span>
                    <span x-show="sent" x-cloak>✓ Terima kasih!</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Credit --}}
    @php
        $devName = \App\Models\Setting::get('developer_name', 'yogi-maulana-dev');
        $devUrl = \App\Models\Setting::get('developer_url', 'https://github.com/yogi-maulana-dev');
        $devEmail = \App\Models\Setting::get('developer_email');
    @endphp
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 text-center text-xs text-brand-300 space-y-1.5">
            <p>{{ \App\Models\Setting::get('support_credit', 'Didukung oleh Tim IT dan Pegawai Perpustakaan Universitas Muhammadiyah Lampung') }}</p>
            <p class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
                <span>Dikembangkan oleh</span>
                <a href="{{ $devUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 font-semibold text-gold-400 hover:text-gold-300">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .5A12 12 0 000 12.6c0 5.3 3.4 9.8 8.2 11.4.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.3-1.8-1.3-1.8-1.1-.7 0-.7 0-.7 1.2.1 1.9 1.3 1.9 1.3 1.1 1.9 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.4-5.5-6a4.7 4.7 0 011.3-3.3 4.4 4.4 0 01.1-3.2s1-.3 3.4 1.3a11.7 11.7 0 016.2 0C18 4.6 19 4.9 19 4.9a4.4 4.4 0 01.1 3.2 4.7 4.7 0 011.3 3.3c0 4.6-2.8 5.7-5.5 6 .4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0024 12.6 12 12 0 0012 .5z"/></svg>
                    {{ $devName }}
                </a>
                @if($devEmail)
                    <span class="text-brand-500">·</span>
                    <a href="mailto:{{ $devEmail }}" class="inline-flex items-center gap-1 hover:text-gold-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $devEmail }}
                    </a>
                @endif
            </p>
        </div>
    </div>

    {{-- Payment + copyright --}}
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-xs text-brand-300">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'University Press') }}. Seluruh hak cipta dilindungi.</span>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-brand-300 mr-1">Pembayaran:</span>
                @foreach(['VISA', 'MasterCard', 'BCA', 'Mandiri', 'GoPay', 'OVO'] as $pay)
                    <span class="rounded bg-white/90 px-2 py-1 text-[10px] font-bold text-brand-900">{{ $pay }}</span>
                @endforeach
            </div>
        </div>
    </div>
</footer>
