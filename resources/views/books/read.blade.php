<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Baca: {{ $book->title }} — University Press</title>
    @vite(['resources/css/app.css'])
    <style>
        [x-cloak]{display:none!important}
        .wm-page{position:relative}
        .wm-page::after{
            content:attr(data-wm);
            position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
            font:600 28px/1.4 system-ui;color:rgba(30,58,95,.10);
            transform:rotate(-30deg);pointer-events:none;white-space:pre;text-align:center;
            background:repeating-linear-gradient(transparent,transparent 120px,transparent 121px);
        }
        canvas{display:block;margin:0 auto;box-shadow:0 4px 16px rgba(0,0,0,.12)}
    </style>
</head>
<body class="h-full bg-stone-200 select-none" oncontextmenu="return false">
<div x-data="reader()" x-init="init()" class="flex h-full flex-col">
    {{-- Toolbar --}}
    <header class="flex items-center justify-between gap-4 bg-brand-900 px-4 py-2.5 text-white shadow">
        <a href="{{ route('books.show', $book->slug) }}" class="flex items-center gap-2 text-sm hover:text-gold-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="hidden sm:block truncate text-sm font-medium">{{ $book->title }}</h1>
        <div class="flex items-center gap-3 text-sm">
            <button @click="zoom(-0.15)" class="rounded p-1 hover:bg-white/10" title="Perkecil">−</button>
            <span x-text="Math.round(scale*100)+'%'" class="w-12 text-center tabular-nums"></span>
            <button @click="zoom(0.15)" class="rounded p-1 hover:bg-white/10" title="Perbesar">+</button>
            <span class="hidden sm:inline text-brand-300">|</span>
            <span class="hidden sm:inline text-brand-200" x-text="'Hal ' + rendered + '/' + total"></span>
        </div>
    </header>

    {{-- Pages --}}
    <main class="flex-1 overflow-auto py-6">
        <div id="pages" class="space-y-6 px-2"></div>
        <div x-show="loading" class="py-20 text-center text-stone-500">
            <svg class="mx-auto h-8 w-8 animate-spin text-brand-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
            <p class="mt-2 text-sm">Memuat dokumen…</p>
        </div>
        <div x-show="error" x-cloak class="py-20 text-center text-rose-600 text-sm" x-text="error"></div>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    function reader() {
        return {
            scale: 1.2, total: 0, rendered: 0, loading: true, error: '',
            pdf: null,
            watermark: @json(trim((auth()->user()->name ?? 'Tamu').'  ·  University Press')),
            async init() {
                if (!window.pdfjsLib) { this.error = 'Gagal memuat penampil PDF.'; this.loading = false; return; }
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                try {
                    this.pdf = await pdfjsLib.getDocument({ url: @json($streamUrl), withCredentials: true }).promise;
                    this.total = this.pdf.numPages;
                    this.loading = false;
                    await this.renderAll();
                } catch (e) {
                    this.error = 'Tidak dapat memuat berkas PDF.'; this.loading = false;
                }
            },
            async renderAll() {
                const container = document.getElementById('pages');
                container.innerHTML = '';
                this.rendered = 0;
                for (let n = 1; n <= this.total; n++) {
                    const page = await this.pdf.getPage(n);
                    const viewport = page.getViewport({ scale: this.scale });
                    const wrap = document.createElement('div');
                    wrap.className = 'wm-page mx-auto'; wrap.style.width = viewport.width + 'px';
                    wrap.setAttribute('data-wm', this.watermark);
                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width; canvas.height = viewport.height;
                    wrap.appendChild(canvas); container.appendChild(wrap);
                    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                    this.rendered = n;
                }
            },
            zoom(delta) {
                this.scale = Math.min(2.5, Math.max(0.6, this.scale + delta));
                this.renderAll();
            },
        };
    }
</script>
@vite(['resources/js/app.js'])
</body>
</html>
