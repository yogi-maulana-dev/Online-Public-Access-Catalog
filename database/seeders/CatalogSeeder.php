<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $publishers = $this->seedPublishers();
        $authors = $this->seedAuthors();

        foreach ($this->books() as $data) {
            $book = Book::create([
                'publisher_id' => $publishers[$data['publisher']]->id,
                'category_id' => $categories[$data['category']]->id,
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'isbn' => $data['isbn'],
                'doi' => '10.50121/upress.'.$data['isbn'],
                'abstract' => $data['abstract'],
                'description' => $data['abstract']."\n\n".'Buku ini ditujukan bagi mahasiswa, dosen, dan praktisi yang ingin mendalami topik secara komprehensif dengan studi kasus kontekstual Indonesia.',
                'year' => $data['year'],
                'publication_date' => Carbon::create($data['year'], rand(1, 12), rand(1, 28)),
                'edition' => $data['edition'] ?? 'Edisi 1',
                'language' => 'Indonesia',
                'pages' => $data['pages'],
                'format' => $data['format'] ?? 'PDF',
                'license' => 'CC BY-NC 4.0',
                'is_open_access' => true,
                'status' => 'published',
                'is_featured' => $data['featured'] ?? false,
                'view_count' => rand(120, 8000),
                'download_count' => rand(40, 2500),
                'published_at' => Carbon::create($data['year'], rand(1, 12), rand(1, 28)),
            ]);

            // Authors with order/role.
            foreach ($data['authors'] as $i => $authorKey) {
                $book->authors()->attach($authors[$authorKey]->id, [
                    'role' => 'author',
                    'sequence' => $i + 1,
                ]);
            }

            // Keywords.
            $keywordIds = collect($data['keywords'])
                ->map(fn ($k) => Keyword::findOrCreateByName($k)->id)
                ->all();
            $book->keywords()->sync($keywordIds);
        }

        // Make published books searchable (no-op on the database Scout driver).
        Book::query()->where('status', 'published')->get()->each->searchable();
    }

    /** @return array<string, Category> */
    private function seedCategories(): array
    {
        $names = [
            'kecerdasan-buatan' => ['name' => 'Kecerdasan Buatan', 'icon' => '🤖', 'color' => '#6366f1'],
            'pendidikan' => ['name' => 'Pendidikan', 'icon' => '🎓', 'color' => '#0ea5e9'],
            'teknik-informatika' => ['name' => 'Teknik Informatika', 'icon' => '💻', 'color' => '#10b981'],
            'ekonomi-bisnis' => ['name' => 'Ekonomi & Bisnis', 'icon' => '📈', 'color' => '#f59e0b'],
            'kesehatan' => ['name' => 'Kesehatan', 'icon' => '🩺', 'color' => '#ef4444'],
            'hukum' => ['name' => 'Hukum', 'icon' => '⚖️', 'color' => '#8b5cf6'],
            'sains' => ['name' => 'Sains & Teknologi', 'icon' => '🔬', 'color' => '#14b8a6'],
            'sosial-humaniora' => ['name' => 'Sosial & Humaniora', 'icon' => '🌏', 'color' => '#ec4899'],
        ];

        $out = [];
        $order = 0;
        foreach ($names as $slug => $attr) {
            $out[$slug] = Category::create([
                'name' => $attr['name'],
                'slug' => $slug,
                'icon' => $attr['icon'],
                'color' => $attr['color'],
                'description' => 'Koleksi monograf dan buku akademik bidang '.$attr['name'].'.',
                'sort_order' => $order++,
            ]);
        }

        return $out;
    }

    /** @return array<string, Publisher> */
    private function seedPublishers(): array
    {
        $data = [
            'up-press' => ['name' => 'University Press', 'city' => 'Jakarta', 'founded' => 1998],
            'nusantara' => ['name' => 'Nusantara Akademika', 'city' => 'Yogyakarta', 'founded' => 2005],
            'cendekia' => ['name' => 'Cendekia Pustaka Ilmu', 'city' => 'Bandung', 'founded' => 2011],
        ];

        $out = [];
        foreach ($data as $slug => $attr) {
            $out[$slug] = Publisher::create([
                'name' => $attr['name'],
                'slug' => $slug,
                'city' => $attr['city'],
                'country' => 'Indonesia',
                'founded_year' => $attr['founded'],
                'email' => 'press@'.$slug.'.ac.id',
                'website' => 'https://'.$slug.'.ac.id',
                'description' => 'Penerbit akademik yang berfokus pada diseminasi karya ilmiah dosen dan peneliti.',
            ]);
        }

        return $out;
    }

    /** @return array<string, Author> */
    private function seedAuthors(): array
    {
        $data = [
            'rahmawati' => ['Prof. Dr. Sri Rahmawati, M.Kom.', 'Teknik Informatika', 'Kecerdasan buatan, pembelajaran mesin, dan sistem cerdas untuk pendidikan.'],
            'pratama' => ['Dr. Adi Pratama, M.T.', 'Teknik Informatika', 'Rekayasa perangkat lunak dan data science.'],
            'wijaya' => ['Dr. Bayu Wijaya, M.Pd.', 'Ilmu Pendidikan', 'Teknologi pendidikan dan pembelajaran digital.'],
            'lestari' => ['Dr. Indah Lestari, M.Si.', 'Ekonomi Pembangunan', 'Ekonomi digital dan kewirausahaan.'],
            'hartono' => ['Prof. Dr. Budi Hartono, S.H., M.H.', 'Ilmu Hukum', 'Hukum siber dan perlindungan data pribadi.'],
            'nugroho' => ['Dr. dr. Fajar Nugroho, Sp.PD.', 'Kedokteran', 'Informatika kesehatan dan kesehatan masyarakat.'],
            'maharani' => ['Dr. Citra Maharani, M.Sc.', 'Sains Data', 'Statistika terapan dan visualisasi data.'],
            'saputra' => ['Eko Saputra, M.Kom.', 'Sistem Informasi', 'Pengembangan aplikasi web dan basis data.'],
            'putri' => ['Dr. Maya Putri, M.Hum.', 'Sosial Humaniora', 'Kajian budaya digital dan masyarakat.'],
            'gunawan' => ['Prof. Dr. Hendra Gunawan, M.Eng.', 'Teknik Elektro', 'Internet of Things dan sistem tertanam.'],
        ];

        $out = [];
        foreach ($data as $key => [$name, $dept, $bio]) {
            $clean = trim(preg_replace('/^(Prof\. Dr\.|Dr\. dr\.|Dr\.|Ir\.)\s*/', '', explode(',', $name)[0]));
            $title = trim(str_replace($clean, '', $name));
            $out[$key] = Author::create([
                'name' => $clean,
                'title' => $title,
                'affiliation' => 'University Press Academic Network',
                'faculty' => 'Fakultas '.$dept,
                'department' => $dept,
                'email' => \Illuminate\Support\Str::slug($clean, '.').'@univpress.ac.id',
                'bio' => $bio,
                'orcid' => '0000-0002-'.rand(1000, 9999).'-'.rand(1000, 9999),
            ]);
        }

        return $out;
    }

    /** @return array<int, array<string, mixed>> */
    private function books(): array
    {
        return [
            [
                'title' => 'Kecerdasan Buatan untuk Pendidikan',
                'subtitle' => 'Konsep, Metode, dan Implementasi di Ruang Kelas Digital',
                'category' => 'kecerdasan-buatan', 'publisher' => 'up-press',
                'authors' => ['rahmawati', 'wijaya'], 'year' => 2024, 'pages' => 320, 'isbn' => '9786230001', 'featured' => true,
                'keywords' => ['kecerdasan buatan', 'pendidikan', 'pembelajaran adaptif', 'machine learning', 'edutech'],
                'abstract' => 'Buku ini membahas penerapan kecerdasan buatan dalam dunia pendidikan, mulai dari sistem tutor cerdas, pembelajaran adaptif, hingga analitik pembelajaran. Pembaca diajak memahami bagaimana machine learning dapat mempersonalisasi pengalaman belajar mahasiswa.',
            ],
            [
                'title' => 'Pembelajaran Mesin dengan Python',
                'subtitle' => 'Dari Regresi hingga Deep Learning',
                'category' => 'kecerdasan-buatan', 'publisher' => 'nusantara',
                'authors' => ['pratama', 'rahmawati'], 'year' => 2023, 'pages' => 410, 'isbn' => '9786230002', 'featured' => true,
                'keywords' => ['machine learning', 'python', 'deep learning', 'data science', 'kecerdasan buatan'],
                'abstract' => 'Panduan praktis pembelajaran mesin menggunakan Python. Setiap bab dilengkapi studi kasus dan kode siap pakai untuk membangun model prediktif, klasifikasi, hingga jaringan saraf tiruan.',
            ],
            [
                'title' => 'Transformasi Digital Pendidikan Tinggi',
                'category' => 'pendidikan', 'publisher' => 'up-press',
                'authors' => ['wijaya'], 'year' => 2024, 'pages' => 280, 'isbn' => '9786230003', 'featured' => true,
                'keywords' => ['transformasi digital', 'pendidikan tinggi', 'e-learning', 'lms', 'edutech'],
                'abstract' => 'Mengulas strategi transformasi digital di perguruan tinggi, mencakup tata kelola, sistem manajemen pembelajaran, dan budaya akademik digital yang berkelanjutan.',
            ],
            [
                'title' => 'Rekayasa Perangkat Lunak Modern',
                'subtitle' => 'Praktik Agile dan DevOps',
                'category' => 'teknik-informatika', 'publisher' => 'cendekia',
                'authors' => ['pratama', 'saputra'], 'year' => 2023, 'pages' => 360, 'isbn' => '9786230004',
                'keywords' => ['rekayasa perangkat lunak', 'agile', 'devops', 'software engineering'],
                'abstract' => 'Membahas metodologi rekayasa perangkat lunak terkini dengan pendekatan Agile dan DevOps, termasuk integrasi berkelanjutan, pengujian otomatis, dan arsitektur layanan mikro.',
            ],
            [
                'title' => 'Basis Data Terdistribusi',
                'category' => 'teknik-informatika', 'publisher' => 'nusantara',
                'authors' => ['saputra'], 'year' => 2022, 'pages' => 300, 'isbn' => '9786230005',
                'keywords' => ['basis data', 'sistem terdistribusi', 'nosql', 'big data'],
                'abstract' => 'Buku ajar yang menjelaskan konsep basis data terdistribusi, replikasi, konsistensi, dan penerapannya pada sistem berskala besar.',
            ],
            [
                'title' => 'Ekonomi Digital dan Kewirausahaan',
                'category' => 'ekonomi-bisnis', 'publisher' => 'up-press',
                'authors' => ['lestari'], 'year' => 2024, 'pages' => 250, 'isbn' => '9786230006', 'featured' => true,
                'keywords' => ['ekonomi digital', 'kewirausahaan', 'startup', 'e-commerce', 'bisnis'],
                'abstract' => 'Menelaah lanskap ekonomi digital Indonesia, model bisnis platform, dan strategi membangun usaha rintisan berbasis teknologi.',
            ],
            [
                'title' => 'Manajemen Strategis di Era Disrupsi',
                'category' => 'ekonomi-bisnis', 'publisher' => 'cendekia',
                'authors' => ['lestari', 'putri'], 'year' => 2022, 'pages' => 290, 'isbn' => '9786230007',
                'keywords' => ['manajemen', 'strategi', 'disrupsi', 'inovasi', 'bisnis'],
                'abstract' => 'Kerangka manajemen strategis untuk menghadapi disrupsi teknologi, dilengkapi studi kasus perusahaan nasional dan global.',
            ],
            [
                'title' => 'Informatika Kesehatan',
                'subtitle' => 'Rekam Medis Elektronik dan Telemedicine',
                'category' => 'kesehatan', 'publisher' => 'nusantara',
                'authors' => ['nugroho', 'pratama'], 'year' => 2023, 'pages' => 340, 'isbn' => '9786230008',
                'keywords' => ['informatika kesehatan', 'rekam medis elektronik', 'telemedicine', 'kesehatan digital'],
                'abstract' => 'Mengintegrasikan ilmu komputer dan kesehatan, membahas sistem rekam medis elektronik, telemedicine, serta etika data pasien.',
            ],
            [
                'title' => 'Kesehatan Masyarakat Berbasis Data',
                'category' => 'kesehatan', 'publisher' => 'up-press',
                'authors' => ['nugroho'], 'year' => 2021, 'pages' => 220, 'isbn' => '9786230009',
                'keywords' => ['kesehatan masyarakat', 'epidemiologi', 'analitik data', 'kesehatan'],
                'abstract' => 'Pendekatan berbasis data untuk pengambilan keputusan dalam kesehatan masyarakat dan surveilans epidemiologi.',
            ],
            [
                'title' => 'Hukum Siber dan Perlindungan Data Pribadi',
                'category' => 'hukum', 'publisher' => 'cendekia',
                'authors' => ['hartono'], 'year' => 2024, 'pages' => 310, 'isbn' => '9786230010', 'featured' => true,
                'keywords' => ['hukum siber', 'perlindungan data', 'privasi', 'keamanan informasi'],
                'abstract' => 'Analisis komprehensif regulasi perlindungan data pribadi di Indonesia, kejahatan siber, dan tanggung jawab hukum platform digital.',
            ],
            [
                'title' => 'Etika Kecerdasan Buatan',
                'subtitle' => 'Tata Kelola dan Tanggung Jawab Sosial',
                'category' => 'kecerdasan-buatan', 'publisher' => 'up-press',
                'authors' => ['rahmawati', 'hartono'], 'year' => 2024, 'pages' => 270, 'isbn' => '9786230011',
                'keywords' => ['etika', 'kecerdasan buatan', 'tata kelola ai', 'bias algoritma'],
                'abstract' => 'Membahas dimensi etis pengembangan kecerdasan buatan: keadilan algoritmik, transparansi, akuntabilitas, dan dampak sosialnya.',
            ],
            [
                'title' => 'Statistika Terapan untuk Penelitian',
                'category' => 'sains', 'publisher' => 'nusantara',
                'authors' => ['maharani'], 'year' => 2022, 'pages' => 330, 'isbn' => '9786230012',
                'keywords' => ['statistika', 'metode penelitian', 'analisis data', 'sains data'],
                'abstract' => 'Buku ajar statistika terapan yang menekankan pemahaman konsep dan penerapan pada penelitian kuantitatif lintas bidang.',
            ],
            [
                'title' => 'Internet of Things untuk Kota Cerdas',
                'category' => 'sains', 'publisher' => 'cendekia',
                'authors' => ['gunawan', 'saputra'], 'year' => 2023, 'pages' => 300, 'isbn' => '9786230013',
                'keywords' => ['internet of things', 'iot', 'smart city', 'sistem tertanam'],
                'abstract' => 'Mengulas arsitektur IoT, sensor, konektivitas, dan penerapannya untuk membangun kota cerdas yang berkelanjutan.',
            ],
            [
                'title' => 'Visualisasi Data dan Storytelling',
                'category' => 'teknik-informatika', 'publisher' => 'up-press',
                'authors' => ['maharani', 'pratama'], 'year' => 2024, 'pages' => 240, 'isbn' => '9786230014',
                'keywords' => ['visualisasi data', 'data storytelling', 'dashboard', 'analitik'],
                'abstract' => 'Teknik mengubah data menjadi narasi visual yang persuasif menggunakan prinsip desain dan alat analitik modern.',
            ],
            [
                'title' => 'Budaya Digital dan Masyarakat',
                'category' => 'sosial-humaniora', 'publisher' => 'nusantara',
                'authors' => ['putri'], 'year' => 2021, 'pages' => 210, 'isbn' => '9786230015',
                'keywords' => ['budaya digital', 'masyarakat', 'media sosial', 'sosiologi'],
                'abstract' => 'Kajian tentang bagaimana teknologi digital membentuk pola interaksi, identitas, dan budaya masyarakat kontemporer.',
            ],
            [
                'title' => 'Pemrosesan Bahasa Alami Bahasa Indonesia',
                'category' => 'kecerdasan-buatan', 'publisher' => 'cendekia',
                'authors' => ['rahmawati', 'maharani'], 'year' => 2023, 'pages' => 350, 'isbn' => '9786230016',
                'keywords' => ['natural language processing', 'nlp', 'bahasa indonesia', 'kecerdasan buatan', 'text mining'],
                'abstract' => 'Membahas teknik pemrosesan bahasa alami untuk bahasa Indonesia: tokenisasi, analisis sentimen, hingga model bahasa berbasis transformer.',
            ],
            [
                'title' => 'Desain Pembelajaran Berbasis Proyek',
                'category' => 'pendidikan', 'publisher' => 'up-press',
                'authors' => ['wijaya', 'putri'], 'year' => 2022, 'pages' => 230, 'isbn' => '9786230017',
                'keywords' => ['pembelajaran berbasis proyek', 'pendidikan', 'kurikulum', 'pedagogi'],
                'abstract' => 'Panduan merancang pembelajaran berbasis proyek yang menumbuhkan keterampilan abad 21 dan kolaborasi mahasiswa.',
            ],
            [
                'title' => 'Keamanan Jaringan Komputer',
                'category' => 'teknik-informatika', 'publisher' => 'nusantara',
                'authors' => ['gunawan'], 'year' => 2023, 'pages' => 320, 'isbn' => '9786230018',
                'keywords' => ['keamanan jaringan', 'cybersecurity', 'kriptografi', 'jaringan komputer'],
                'abstract' => 'Konsep dan praktik keamanan jaringan komputer: kriptografi, firewall, deteksi intrusi, dan manajemen risiko keamanan.',
            ],
        ];
    }
}
