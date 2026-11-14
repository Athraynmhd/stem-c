<?php

namespace Database\Seeders;

use App\Models\Chrome\Partner;
use App\Models\Chrome\Setting;
use App\Models\Chrome\SiteSection;
use App\Models\Chrome\SiteSlide;
use Illuminate\Database\Seeder;

/**
 * Cutover CMS: mengisi section/slide/mitra awal bila belum ada.
 *
 * Sengaja non-destruktif. Pengelola mengganti gambar hero dari halaman
 * publik; `delete()` + `create()` akan mengganti ID dan mengembalikan aset
 * bawaan, sehingga unggahan tampak hilang.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedSections();
        $this->seedSlides();
        $this->seedPartners();
    }

    private function seedSettings(): void
    {
        $extras = [
            'logo_light' => 'assets/images/logo/logo-stemc-light.svg',
            'logo_dark' => 'assets/images/logo/logo-stemc-dark.svg',
            'meta_description' => 'Pusat Riset STEM-C Universitas Syiah Kuala — riset Science, Technology, Engineering, Mathematics, dan Character.',
            'header_cta_label' => 'Ajukan Kerja Sama',
            'header_cta_url' => '/kerja-sama/ajukan',
        ];

        foreach ($extras as $key => $value) {
            if (Setting::query()->where('key', $key)->exists()) {
                continue;
            }

            Setting::set($key, $value, match (true) {
                str_starts_with($key, 'logo_') => 'brand',
                str_starts_with($key, 'meta_') => 'seo',
                str_starts_with($key, 'header_') => 'chrome',
                default => 'general',
            });
        }
    }

    private function seedSections(): void
    {
        $sections = [
            'home.about' => [
                'eyebrow' => 'Tentang Kami',
                'heading' => 'Pusat riset',
                'heading_highlight' => 'STEM-C USK',
                'body' => 'yang mengintegrasikan sains, teknologi, dan pembentukan karakter.',
                'cta_label' => 'Selengkapnya',
                'cta_url' => '/profil',
                'meta' => [
                    'stats' => [
                        ['title' => "Peneliti\n& Anggota", 'value' => '30', 'suffix' => '+', 'label' => 'Peneliti aktif'],
                        ['title' => "Publikasi\nIlmiah", 'value' => '100', 'suffix' => '+', 'label' => 'Jurnal & prosiding'],
                        ['title' => "Riset\nAktif", 'value' => '15', 'suffix' => '+', 'label' => 'Proyek berjalan'],
                        ['title' => "HKI &\nPaten", 'value' => '20', 'suffix' => '+', 'label' => 'Kekayaan intelektual'],
                    ],
                ],
            ],
            'home.feature' => [
                'eyebrow' => 'Sorotan Riset',
                'heading' => 'Riset & produk',
                'heading_highlight' => 'unggulan',
                'body' => 'terbaru',
                'cta_label' => 'Lihat Semua',
                'cta_url' => '/berita',
            ],
            'home.clusters' => [
                'eyebrow' => 'Kelompok Riset',
                'heading' => 'Empat kluster',
                'heading_highlight' => 'riset STEM-C',
            ],
            'home.events' => [
                'eyebrow' => 'Kegiatan',
                'heading' => 'Agenda',
                'heading_highlight' => 'terdekat',
                'cta_label' => 'Lihat Semua',
                'cta_url' => '/kegiatan',
            ],
            'home.imagy' => [
                'eyebrow' => 'produk unggulan',
                'heading' => 'IMAGy',
                'heading_highlight' => 'Produk Riset',
                'body' => 'Inovasi STEM-C',
                'cta_url' => '/berita',
                'meta' => [
                    'tags' => ['Inovasi', 'HKI'],
                ],
            ],
            'home.publications' => [
                'eyebrow' => 'Publikasi',
                'heading' => 'Luaran',
                'heading_highlight' => 'riset terbaru',
                'cta_label' => 'Lihat Semua',
                'cta_url' => '/publikasi',
            ],
            'home.news' => [
                'eyebrow' => 'Rilis Berita',
                'heading' => 'Berita',
                'heading_highlight' => 'terbaru',
                'cta_label' => 'Lihat Semua',
                'cta_url' => '/berita',
            ],
            'home.gallery' => [
                'eyebrow' => 'Dokumentasi',
                'heading' => 'Jejak',
                'heading_highlight' => 'kegiatan kami',
                'cta_label' => 'Lihat Semua',
                'cta_url' => '/dokumentasi',
            ],
            'home.partners' => [
                'eyebrow' => 'Kerja Sama',
                'heading' => 'Didukung oleh',
                'heading_highlight' => 'mitra tepercaya',
                'body' => 'Mitra institusi nasional & internasional',
            ],
            'footer.cta' => [
                'eyebrow' => 'Kolaborasi Riset',
                'heading' => 'Tertarik Berkolaborasi? Mari Bicara.',
                'body' => 'Kami terbuka untuk riset bersama, kemitraan industri, dan program pengembangan STEM-C dengan institusi nasional maupun internasional.',
                'cta_label' => 'Ajukan Kerja Sama',
                'cta_url' => '/kontak',
            ],
            'berita.banner' => [
                'heading' => 'Berita',
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'berita.intro' => [
                'eyebrow' => 'Rilis & Liputan',
                'body' => 'Berita terkini seputar riset, kolaborasi, dan capaian Pusat Riset STEM-C USK.',
            ],
            'kegiatan.banner' => [
                'heading' => 'Kegiatan',
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'kegiatan.intro' => [
                'eyebrow' => 'Agenda Mendatang',
                'body' => 'Seminar, workshop, dan pelatihan yang akan diselenggarakan Pusat Riset STEM-C USK.',
            ],
            'kegiatan.past' => [
                'eyebrow' => 'Telah Terlaksana',
                'body' => 'Rekam jejak kegiatan yang sudah dilaksanakan bersama mitra dan sivitas akademika.',
            ],
            'publikasi.banner' => [
                'heading' => 'Publikasi',
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'publikasi.intro' => [
                'eyebrow' => 'Luaran Riset',
                'body' => 'Artikel jurnal, prosiding, buku, serta kekayaan intelektual yang dihasilkan peneliti Pusat Riset STEM-C USK.',
            ],
            'dokumentasi.banner' => [
                'heading' => 'Dokumentasi',
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'dokumentasi.intro' => [
                'eyebrow' => 'Galeri',
                'body' => 'Dokumentasi foto dan video kegiatan, laboratorium, serta kolaborasi Pusat Riset STEM-C USK.',
            ],
            'dokumen.banner' => [
                'heading' => 'Dokumen',
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'dokumen.intro' => [
                'eyebrow' => 'Unduhan',
                'body' => 'Regulasi, panduan, formulir, dan materi kegiatan Pusat Riset STEM-C USK yang dapat diunduh secara bebas.',
            ],
            'kontak.banner' => [
                'heading' => 'Kontak',
                'image' => 'assets/images/media/img_94.jpg',
            ],
            'kontak.intro' => [
                'heading' => 'Ada pertanyaan?',
                'heading_highlight' => 'Mari bicara.',
                'body' => 'Tim STEM-C siap membantu pertanyaan seputar riset, kegiatan, dan kerja sama.',
            ],
            // Judul banner tiga halaman berikut diambil dari record, jadi hanya
            // gambarnya yang disimpan di sini.
            'profil.banner' => [
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'berita_detail.banner' => [
                'image' => 'assets/images/media/img_87.jpg',
            ],
            'kegiatan_detail.banner' => [
                'image' => 'assets/images/media/img_87.jpg',
            ],
        ];

        foreach ($sections as $key => $data) {
            SiteSection::query()->firstOrCreate(
                ['key' => $key],
                [...$data, 'is_published' => true],
            );
        }
    }

    private function seedSlides(): void
    {
        $heroes = [
            [
                'highlight' => 'Pusat Riset',
                'title' => 'STEM-C USK',
                'body' => 'Mendorong riset Science, Technology, Engineering, Mathematics, dan Character di Universitas Syiah Kuala.',
                'tags' => ['Science', 'Technology'],
                'image' => 'assets/images/media/img_01.jpg',
                'sort_order' => 1,
            ],
            [
                'highlight' => 'Riset & Inovasi',
                'title' => 'Berkarakter',
                'body' => 'Menghasilkan publikasi ilmiah, produk riset, dan kekayaan intelektual yang berdampak.',
                'tags' => ['Engineering', 'Mathematics'],
                'image' => 'assets/images/media/img_02.jpg',
                'sort_order' => 2,
            ],
            [
                'highlight' => 'Kolaborasi',
                'title' => 'Global',
                'body' => 'Bermitra dengan institusi nasional dan internasional untuk kemajuan riset STEM-C.',
                'tags' => ['Character', 'Collaboration'],
                'image' => 'assets/images/media/img_03.jpg',
                'sort_order' => 3,
            ],
        ];

        $this->pastikanSlide(SiteSlide::PLACEMENT_HERO, $heroes);

        $features = [
            ['title' => 'RnD', 'image' => 'assets/images/media/img_04.jpg', 'sort_order' => 1],
            ['title' => 'Manufacturing', 'image' => 'assets/images/media/img_05.jpg', 'sort_order' => 2],
            ['title' => 'Pedagogical', 'image' => 'assets/images/media/img_06.jpg', 'sort_order' => 3],
            ['title' => 'Marketing', 'image' => 'assets/images/media/img_07.jpg', 'sort_order' => 4],
            ['title' => 'Collaboration', 'image' => 'assets/images/media/img_08.jpg', 'sort_order' => 5],
        ];

        $this->pastikanSlide(SiteSlide::PLACEMENT_FEATURE, $features, [
            'body' => 'Riset dan produk inovatif STEM-C yang berdampak bagi masyarakat.',
            'link_url' => '/berita',
        ]);

        $clusters = [
            [
                'title' => 'RnD',
                'body' => 'Riset dan pengembangan sains serta teknologi sebagai fondasi inovasi STEM-C.',
                'image' => 'assets/images/media/img_09.jpg',
                'sort_order' => 1,
            ],
            [
                'title' => 'Manufacturing',
                'body' => 'Pengembangan produk dan prototipe hasil riset menuju hilirisasi dan komersialisasi.',
                'image' => 'assets/images/media/img_09.jpg',
                'sort_order' => 2,
            ],
            [
                'title' => 'Pedagogical',
                'body' => 'Riset pendidikan STEM dan pengembangan modul serta bahan ajar berkarakter.',
                'image' => 'assets/images/media/img_09.jpg',
                'sort_order' => 3,
            ],
            [
                'title' => 'Marketing & Collaboration',
                'body' => 'Diseminasi, kerja sama, dan kemitraan riset dengan institusi nasional dan internasional.',
                'image' => 'assets/images/media/img_09.jpg',
                'sort_order' => 4,
            ],
        ];

        $this->pastikanSlide(SiteSlide::PLACEMENT_CLUSTER, $clusters);
    }

    /**
     * Jangan hapus slide yang sudah ada. Pengelola sering mengganti gambar
     * hero lewat editor inline; `delete()` + `create()` membuat ID baru dan
     * mengembalikan aset bawaan, sehingga unggahan tampak “tidak tersimpan”.
     *
     * @param  array<int, array<string, mixed>>  $slides
     * @param  array<string, mixed>  $tambahan
     */
    private function pastikanSlide(string $placement, array $slides, array $tambahan = []): void
    {
        foreach ($slides as $slide) {
            SiteSlide::query()->firstOrCreate(
                [
                    'placement' => $placement,
                    'sort_order' => $slide['sort_order'],
                ],
                [...$slide, ...$tambahan, 'placement' => $placement, 'is_published' => true],
            );
        }
    }

    private function seedPartners(): void
    {
        $mitra = [
            ['name' => 'Universitas Syiah Kuala', 'logo' => 'assets/images/logo/partner/usk.png', 'sort_order' => 1],
            ['name' => 'USAID', 'logo' => 'assets/images/logo/partner/usaid.png', 'sort_order' => 2],
            ['name' => 'Pemerintah Aceh', 'logo' => 'assets/images/logo/partner/pemerintah-aceh.png', 'sort_order' => 3],
            ['name' => 'Pemerintah Kota Banda Aceh', 'logo' => 'assets/images/logo/partner/kota-banda-aceh.png', 'sort_order' => 4],
            ['name' => 'Kementerian Pendidikan', 'logo' => 'assets/images/logo/partner/kemendikbud.png', 'sort_order' => 5],
            ['name' => 'Rutgers, The State University of New Jersey', 'logo' => 'assets/images/logo/partner/rutgers.jpg', 'sort_order' => 6],
        ];

        foreach ($mitra as $item) {
            Partner::query()->firstOrCreate(
                ['name' => $item['name']],
                [...$item, 'is_published' => true],
            );
        }
    }
}
