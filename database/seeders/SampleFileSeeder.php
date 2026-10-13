<?php

namespace Database\Seeders;

use App\Models\Book;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Generates one shared sample PDF and attaches it (as a protected file) to every
 * seeded book, so the online reader, watermark, and download flow are demonstrable.
 */
class SampleFileSeeder extends Seeder
{
    public function run(): void
    {
        $disk = 'local';
        $path = 'protected/books/sample-monograph.pdf';

        if (! Storage::disk($disk)->exists($path)) {
            $pdf = Pdf::loadHtml($this->html())->setPaper('a4');
            Storage::disk($disk)->put($path, $pdf->output());
        }

        $size = Storage::disk($disk)->size($path);

        // Only digital-format books get a PDF; print books remain catalog-only (cover).
        Book::query()->where('status', 'published')->whereIn('format', ['PDF', 'E-Book'])->get()->each(function (Book $book) use ($disk, $path, $size) {
            if ($book->files()->where('type', 'pdf')->exists()) {
                return;
            }
            $book->files()->create([
                'type' => 'pdf',
                'disk' => $disk,
                'path' => $path,
                'original_name' => \Illuminate\Support\Str::slug($book->title).'.pdf',
                'mime_type' => 'application/pdf',
                'size' => $size,
                'is_public' => $book->is_open_access,
                'watermark' => true,
            ]);
        });
    }

    private function html(): string
    {
        return <<<'HTML'
        <html><head><style>
            body{font-family:Georgia,serif;color:#1e3a5f;padding:40px;line-height:1.6}
            h1{font-size:26px;border-bottom:3px solid #c8a45c;padding-bottom:8px}
            h2{font-size:18px;margin-top:28px;color:#2c4061}
            p{font-size:13px;color:#333;text-align:justify}
            .meta{color:#888;font-size:11px;margin-top:6px}
            .page-break{page-break-after:always}
        </style></head><body>
            <div class="page-break">
                <h1>University Press — Naskah Contoh</h1>
                <p class="meta">Dokumen demonstrasi penampil PDF dengan watermark dan proteksi unduhan.</p>
                <h2>Abstrak</h2>
                <p>Dokumen ini merupakan contoh berkas yang dilampirkan pada katalog University Press.
                Pada lingkungan produksi, berkas ini akan diganti dengan naskah asli buku yang telah
                melalui proses peer-review dan penyuntingan.</p>
                <h2>Bab 1 — Pendahuluan</h2>
                <p>Bagian ini memaparkan latar belakang, rumusan masalah, serta tujuan penulisan.
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Penampil daring merender
                setiap halaman ke kanvas sehingga teks tidak mudah disalin secara massal, sembari
                menampilkan watermark identitas pembaca untuk mencegah penyebaran tanpa izin.</p>
            </div>
            <div>
                <h2>Bab 2 — Pembahasan</h2>
                <p>Halaman kedua memperlihatkan bahwa penampil mendukung dokumen multi-halaman.
                Setiap halaman dirender berurutan dengan kontrol perbesaran. Sed do eiusmod tempor
                incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                <h2>Penutup</h2>
                <p>Terima kasih telah membaca melalui platform University Press.</p>
            </div>
        </body></html>
        HTML;
    }
}
