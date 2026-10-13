<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Hero sliders ------------------------------------------------
        $sliders = [
            [
                'title' => 'Mencerdaskan Bangsa Melalui Karya dan Inovasi',
                'subtitle' => 'Penerbit universitas yang menghadirkan karya ilmiah dosen dan akademisi.',
                'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1920&q=80',
                'cta_label' => 'Jelajahi Buku', 'cta_url' => '/katalog',
                'cta2_label' => 'Tentang Kami', 'cta2_url' => '/#tentang',
            ],
            [
                'title' => 'Pustaka Digital untuk Generasi Unggul',
                'subtitle' => 'Akses ribuan monograf, buku ajar, dan jurnal ilmiah kapan saja, di mana saja.',
                'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1920&q=80',
                'cta_label' => 'Mulai Membaca', 'cta_url' => '/katalog',
                'cta2_label' => 'Daftar', 'cta2_url' => '/register',
            ],
            [
                'title' => 'Diseminasi Ilmu Tanpa Batas',
                'subtitle' => 'Terbitkan karya Anda dengan ISBN resmi dan jangkauan internasional.',
                'image' => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=1920&q=80',
                'cta_label' => 'Ajukan Naskah', 'cta_url' => '/register',
                'cta2_label' => 'Lihat Katalog', 'cta2_url' => '/katalog',
            ],
        ];
        foreach ($sliders as $i => $s) {
            Slider::create($s + ['sort_order' => $i, 'is_active' => true]);
        }

        // ---- Campus gallery ----------------------------------------------
        $gallery = [
            ['Gedung Rektorat', 'Gedung', 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=800&q=80'],
            ['Perpustakaan Pusat', 'Perpustakaan', 'https://images.unsplash.com/photo-1568667256549-094345857637?auto=format&fit=crop&w=800&q=80'],
            ['Laboratorium Komputer', 'Laboratorium', 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=800&q=80'],
            ['Aktivitas Mahasiswa', 'Mahasiswa', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=800&q=80'],
            ['Upacara Wisuda', 'Wisuda', 'https://images.unsplash.com/photo-1627556704302-624286467c65?auto=format&fit=crop&w=800&q=80'],
            ['Seminar Nasional', 'Seminar', 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=800&q=80'],
        ];
        foreach ($gallery as $i => [$title, $cat, $img]) {
            Gallery::create(['title' => $title, 'category' => $cat, 'image' => $img, 'sort_order' => $i, 'is_active' => true]);
        }

        // ---- Site settings (rector, contact, social, stats) --------------
        $settings = [
            'general' => [
                'site_name' => 'University Press',
                'site_tagline' => 'Digital University Press & Online Book Store',
                'stat_faculties' => '8',
                'support_credit' => 'Didukung oleh Tim IT dan Pegawai Perpustakaan Universitas Muhammadiyah Lampung',
                'developer_name' => 'yogi-maulana-dev',
                'developer_url' => 'https://github.com/yogi-maulana-dev',
                'developer_email' => '',
            ],
            'rector' => [
                'rector_name' => 'Prof. Dr. H. Ahmad Subhan, M.A.',
                'rector_title' => 'Rektor Universitas',
                'rector_position' => 'Rektor Periode 2024–2028',
                'rector_photo' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?auto=format&fit=crop&w=600&q=80',
                'rector_quote' => 'University Press hadir sebagai wujud komitmen kami dalam mendiseminasikan ilmu pengetahuan. Melalui penerbitan karya akademik yang berkualitas, kami berharap dapat memberikan kontribusi nyata bagi kemajuan bangsa dan peradaban.',
            ],
            'contact' => [
                'contact_address' => 'Jl. Pendidikan No. 1, Kota Akademika, Indonesia 35000',
                'contact_email' => 'press@univ.ac.id',
                'contact_phone' => '+62 721 123 456',
                'social_facebook' => 'https://facebook.com',
                'social_instagram' => 'https://instagram.com',
                'social_youtube' => 'https://youtube.com',
                'social_twitter' => 'https://twitter.com',
            ],
        ];
        foreach ($settings as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }

        // ---- Give books store attributes (price + rating + varied formats) ----
        // Digital books (PDF/E-Book) can be open access; print books are catalog
        // entries (cover only) and always carry a price.
        Book::query()->get()->each(function (Book $book, $i) {
            $format = ['PDF', 'E-Book', 'Paperback', 'PDF', 'Hardcover'][$i % 5];
            $isDigital = in_array($format, ['PDF', 'E-Book']);
            $isFree = $isDigital && $i % 3 === 0;

            $book->forceFill([
                'format' => $format,
                'price' => $isFree ? 0 : rand(45, 185) * 1000,
                'is_open_access' => $isFree,
                'rating' => round(mt_rand(40, 50) / 10, 1),
                'rating_count' => rand(8, 240),
            ])->save();
        });
    }
}
