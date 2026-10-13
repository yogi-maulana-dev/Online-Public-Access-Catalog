# University Press — Digital Academic Book Publishing Platform

Platform penerbitan buku akademik universitas modern (gaya Open Monograph Press, namun lebih
cepat dan elegan). Dibangun dengan **Laravel 13**, **Filament 3**, **Tailwind CSS**, **Alpine.js**,
**Laravel Scout**, dan **Spatie Permission**.

---

## ✨ Fitur

| # | Fitur | Implementasi |
|---|-------|--------------|
| 1 | **Katalog Buku Digital** | Cover, judul, ISBN/e-ISBN, DOI, penerbit, tahun, kategori, abstrak, keyword, halaman, format |
| 2 | **Manajemen Penulis** | Profil + biodata akademik (gelar, afiliasi, ORCID, Scholar), foto, daftar publikasi |
| 3 | **Sistem Submission** | Alur Author → Editor → Reviewer → Revisi → Approved → Published (state machine) |
| 4 | **Pustaka PDF** | Penampil PDF daring (PDF.js, render kanvas), watermark identitas pembaca, proteksi unduhan, statistik |
| 5 | **Mesin Pencari** | Full-text MySQL (`MATCH … AGAINST`) dengan peringkat relevansi + filter kategori/tahun/penulis; siap Meilisearch |
| 6 | **Rekomendasi** | Content-based (kemiripan kategori/keyword/penulis) + rekomendasi dari histori pengguna |
| 7 | **Dashboard Analitik** | Statistik, grafik publikasi per tahun, buku terpopuler (Filament widgets) |
| 8 | **Landing Page Modern** | Hero, featured, terbaru, kategori, sorotan penulis |
| 9 | **Generator Sitasi** | APA 7, MLA, Chicago, BibTeX, RIS (salin & unduh) |
| 10 | **SEO & Indexing** | Sitemap XML, meta + OpenGraph, structured data `schema.org/Book`, endpoint **OAI-PMH** (Dublin Core) |

## 🏗️ Arsitektur

- **Service layer** — `app/Services` (`BookService`, `SubmissionService`, `SearchService`, `RecommendationService`, `CitationService`, `AnalyticsService`, `FileService`).
- **Repository pattern** — `app/Repositories` (`BookRepositoryInterface` → `EloquentBookRepository`), di-bind via `RepositoryServiceProvider`.
- **Enums** — `app/Enums` untuk status & gaya sitasi (type-safe).
- **RBAC** — 6 peran (Super Admin, Publisher Admin, Editor, Reviewer, Author, Reader) + permission granular (Spatie).

## 🗄️ Skema Database

`users` (+profil), `roles`/`permissions` (Spatie), `publishers`, `categories` (hierarkis),
`authors`, `books` (+FULLTEXT), `keywords`, `book_author`, `book_keyword`, `submissions`,
`reviews`, `files` (polimorfik), `downloads`, `book_views`, `citations`, `activity_logs`.

---

## 🚀 Instalasi

```bash
# 1. Dependencies
composer install
npm install && npm run build

# 2. Environment
php artisan key:generate

# 3. Database (MySQL) — sesuaikan kredensial di .env (DB_DATABASE=university_press)
php artisan migrate:fresh --seed

# 4. Storage symlink (cover, berkas)
php artisan storage:link

# 5. Jalankan
php artisan serve        # http://127.0.0.1:8000
npm run dev              # hot-reload aset saat pengembangan
```

> **PHP 8.3+ wajib.** Pada Windows/WAMP, prepend PHP 8.3 ke PATH lebih dulu:
> `export PATH="/c/wamp64/bin/php/php8.3.28:$PATH"`

### Pencarian cepat (opsional)

Default memakai driver Scout `database` (full-text MySQL) sehingga langsung jalan tanpa servis tambahan.
Untuk pencarian super cepat + toleran typo, jalankan Meilisearch lalu set di `.env`:

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
```

lalu `php artisan scout:import "App\Models\Book"`.

### Antrian (proses berat)

`QUEUE_CONNECTION=database` — jalankan `php artisan queue:work` untuk memproses indexing/notifikasi.

---

## 👤 Akun Demo

Semua kata sandi: **`password`**

| Peran | Email |
|-------|-------|
| Super Admin | `admin@press.test` |
| Publisher Admin | `publisher@press.test` |
| Editor | `editor@press.test` |
| Reviewer | `reviewer@press.test` |
| Author/Dosen | `author@press.test` |
| Reader/Mahasiswa | `reader@press.test` |

- **Panel Admin:** `/admin`
- **Ajukan Naskah** (Author): `/admin/submit-book`

---

## 🔌 Endpoint Penting

| URL | Keterangan |
|-----|------------|
| `/` | Landing page |
| `/katalog?q=…` | Pencarian + filter |
| `/buku/{slug}` | Detail buku + sitasi + rekomendasi |
| `/buku/{slug}/baca` | Penampil PDF (auth, watermark) |
| `/buku/{slug}/sitasi/{apa\|mla\|chicago\|bibtex\|ris}` | Sitasi (JSON / unduh) |
| `/penulis/{slug}` | Profil penulis |
| `/sitemap.xml` | Sitemap |
| `/oai?verb=Identify` | OAI-PMH (Dublin Core) |

## 🧪 Testing

```bash
# Membutuhkan database MySQL `university_press_test`
php artisan test
```

Mencakup alur editorial penuh (submission → publish), kontrol akses panel,
pencarian full-text, dan generator sitasi.

---

Dibangun untuk diseminasi ilmu pengetahuan yang terbuka.
