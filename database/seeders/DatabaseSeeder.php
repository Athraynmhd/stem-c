<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Chrome\Setting;
use App\Models\Content\Document;
use App\Models\Content\Event;
use App\Models\Content\Innovation;
use App\Models\Content\MediaItem;
use App\Models\Content\News;
use App\Models\Content\Page;
use App\Models\Content\Partnership;
use App\Models\Content\Publication;
use App\Models\Content\Researcher;
use App\Models\Content\ResearchGroup;
use App\Models\Content\ResearchProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'DatabaseSeeder tidak boleh dijalankan di produksi: berisi akun dan konten contoh.',
            );
        }

        foreach (['super_admin', 'admin_konten', 'editor'] as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@stemc.usk.ac.id'],
            [
                'name' => 'Super Admin STEM-C',
                'password' => Hash::make('password'),
            ],
        );
        $admin->syncRoles(['super_admin']);

        $defaults = [
            'site_name' => 'Pusat Riset STEM-C USK',
            'site_tagline' => 'Science, Technology, Engineering, Mathematics - Character',
            'email' => 'stemc@usk.ac.id',
            'phone' => '',
            'address' => 'Lab Terpadu, Universitas Syiah Kuala, Banda Aceh, Aceh, Indonesia',
            'whatsapp' => '',
            'instagram' => '',
            'youtube' => '',
            'facebook' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }

        $this->call(SiteContentSeeder::class);

        $pages = [
            [
                'title' => 'Sejarah & Latar Belakang',
                'slug' => 'sejarah',
                'section' => 'sejarah',
                'content' => '<p>Pusat Riset STEM-C (Science, Technology, Engineering, Mathematics - Character) Universitas Syiah Kuala merupakan pusat riset yang mengintegrasikan pendekatan STEM dengan pembentukan karakter.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => 'Visi & Misi',
                'slug' => 'visi-misi',
                'section' => 'visi-misi',
                'content' => '<p><strong>Visi</strong></p><p>Menjadi pusat riset STEM berkarakter yang unggul dan berdaya saing global.</p><p><strong>Misi</strong></p><ul><li>Mengembangkan riset STEM terintegrasi karakter.</li><li>Menghasilkan publikasi, produk, dan HKI yang berdampak.</li><li>Memperluas kolaborasi nasional dan internasional.</li></ul>',
                'sort_order' => 2,
            ],
            [
                'title' => 'Struktur Organisasi',
                'slug' => 'struktur-organisasi',
                'section' => 'struktur',
                'content' => '<p>Struktur organisasi Pusat Riset STEM-C USK akan diperbarui oleh admin melalui panel pengelolaan konten.</p>',
                'sort_order' => 3,
            ],
            [
                'title' => 'Kerja Sama',
                'slug' => 'kerja-sama',
                'section' => 'kerja sama',
                'content' => '<p>Pusat Riset STEM-C USK menjalin kemitraan dengan institusi pemerintah, perguruan tinggi, dan lembaga internasional untuk riset bersama, pertukaran periset, serta pengembangan program STEM berkarakter.</p>',
                'sort_order' => 4,
            ],
            [
                'title' => 'Instrumen',
                'slug' => 'instrumen',
                'section' => 'instrumen',
                'content' => '<p>Instrumen penilaian dan perangkat ukur yang dikembangkan Pusat Riset STEM-C USK untuk mendukung riset pendidikan STEM dan asesmen karakter.</p>',
                'sort_order' => 5,
            ],
            [
                'title' => 'Logo STEM-C',
                'slug' => 'logo',
                'section' => 'identitas',
                'content' => '<p>Panduan penggunaan logo dan identitas visual Pusat Riset STEM-C USK, termasuk ketentuan warna, ruang kosong, dan variasi penggunaan pada latar terang maupun gelap.</p>',
                'sort_order' => 6,
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['is_published' => true]),
            );
        }

        $newsItems = [
            [
                'title' => 'Seminar dan workshop riset STEM-C USK',
                'category' => 'Kegiatan',
                'excerpt' => 'Kegiatan seminar dan workshop untuk penguatan kapasitas peneliti STEM-C.',
            ],
            [
                'title' => 'Publikasi terbaru peneliti STEM-C USK',
                'category' => 'Riset',
                'excerpt' => 'Ringkasan capaian publikasi ilmiah anggota pusat riset.',
            ],
            [
                'title' => 'Kerja sama riset dengan mitra internasional',
                'category' => 'Kolaborasi',
                'excerpt' => 'Penguatan kemitraan riset STEM-C dengan institusi mitra.',
            ],
            [
                'title' => 'Hibah riset kompetitif untuk peneliti muda STEM-C',
                'category' => 'Pengumuman',
                'excerpt' => 'Pembukaan pendanaan riset internal bagi dosen dan mahasiswa pascasarjana di lingkungan pusat riset.',
            ],
            [
                'title' => 'Diseminasi hasil riset modul ajar berkarakter',
                'category' => 'Riset',
                'excerpt' => 'Pemaparan luaran riset pengembangan modul ajar STEM terintegrasi nilai karakter kepada guru mitra.',
            ],
            [
                'title' => 'Kunjungan delegasi universitas mitra ke Lab Terpadu',
                'category' => 'Kolaborasi',
                'excerpt' => 'Penjajakan program riset bersama dan pertukaran periset antarinstitusi di Laboratorium Terpadu USK.',
            ],
        ];

        foreach ($newsItems as $index => $item) {
            News::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'excerpt' => $item['excerpt'],
                    'body' => '<p>'.$item['excerpt'].'</p><p>Konten lengkap dapat diperbarui melalui panel admin.</p>',
                    'status' => 'published',
                    // Selisih hari agar urutan "terbaru" stabil dan tidak seragam.
                    'published_at' => now()->subDays($index),
                    'author_id' => $admin->id,
                ],
            );
        }

        $this->seedResearchGroups();
        $this->seedEvents();
        $this->seedPublications();
        $this->seedMediaItems();
        $this->seedDocuments();
        $this->seedResearchers();
        $this->seedResearchProjects();
        $this->seedInnovations();
        $this->seedPartnerships();
    }

    private function seedResearchGroups(): void
    {
        foreach ([
            ['name' => 'RnD', 'code' => 'rnd', 'slug' => 'rnd', 'focus' => 'Riset dan pengembangan perangkat serta metode STEM.'],
            ['name' => 'Manufacturing', 'code' => 'manufacturing', 'slug' => 'manufacturing', 'focus' => 'Rekayasa dan produksi prototipe pembelajaran.'],
            ['name' => 'Pedagogical', 'code' => 'pedagogical', 'slug' => 'pedagogical', 'focus' => 'Pendidikan STEM terintegrasi karakter.'],
            ['name' => 'Marketing & Collaboration', 'code' => 'marketing', 'slug' => 'marketing', 'focus' => 'Kemitraan, diseminasi, dan kolaborasi institusi.'],
        ] as $index => $group) {
            ResearchGroup::query()->updateOrCreate(
                ['slug' => $group['slug']],
                [...$group, 'sort_order' => $index, 'is_published' => true],
            );
        }
    }

    private function seedEvents(): void
    {
        $events = [
            [
                'title' => 'Seminar Nasional STEM-C 2026',
                'category' => 'seminar',
                'excerpt' => 'Seminar nasional yang mempertemukan peneliti, guru, dan praktisi untuk membahas integrasi STEM dengan pendidikan karakter.',
                'location' => 'Aula Lab Terpadu USK, Banda Aceh',
                'cover_image' => 'assets/images/blog/blog_img_15.jpg',
                'starts_at' => now()->addWeeks(3)->setTime(8, 30),
                'ends_at' => now()->addWeeks(3)->setTime(16, 0),
            ],
            [
                'title' => 'Workshop Penulisan Artikel Jurnal Bereputasi',
                'category' => 'workshop',
                'excerpt' => 'Pelatihan intensif penulisan dan submisi artikel ke jurnal terindeks Scopus bagi dosen dan mahasiswa pascasarjana.',
                'location' => 'Ruang Rapat Pusat Riset STEM-C',
                'cover_image' => 'assets/images/blog/blog_img_16.jpg',
                'starts_at' => now()->addWeeks(6)->setTime(9, 0),
                'ends_at' => now()->addWeeks(6)->setTime(15, 0),
            ],
            [
                'title' => 'Pelatihan Pengembangan Modul Ajar Berbasis STEM',
                'category' => 'pelatihan',
                'excerpt' => 'Pendampingan guru SMA se-Aceh dalam menyusun modul ajar berbasis STEM yang terintegrasi nilai karakter.',
                'location' => 'Laboratorium Pendidikan USK',
                'cover_image' => 'assets/images/blog/blog_img_17.jpg',
                'starts_at' => now()->subMonths(2)->setTime(8, 0),
                'ends_at' => now()->subMonths(2)->setTime(16, 30),
            ],
            [
                'title' => 'Kunjungan Industri dan Penjajakan Kerja Sama Riset',
                'category' => 'kunjungan',
                'excerpt' => 'Penjajakan kolaborasi riset terapan bersama mitra industri manufaktur di kawasan Aceh Besar.',
                'location' => 'Aceh Besar',
                'cover_image' => 'assets/images/blog/blog_img_18.jpg',
                'starts_at' => now()->subMonths(4)->setTime(10, 0),
                'ends_at' => now()->subMonths(4)->setTime(14, 0),
            ],
            [
                'title' => 'Kompetisi Inovasi STEM Pelajar Aceh',
                'category' => 'kompetisi',
                'excerpt' => 'Ajang unjuk karya inovasi pelajar tingkat provinsi dengan pendampingan langsung dari peneliti STEM-C.',
                'location' => 'Gedung AAC Dayan Dawood USK',
                'cover_image' => 'assets/images/blog/blog_img_19.jpg',
                'starts_at' => now()->subMonths(7)->setTime(8, 0),
                'ends_at' => now()->subMonths(7)->setTime(17, 0),
            ],
            [
                'title' => 'Call for Paper Seminar Nasional STEM-C 2026',
                'category' => 'call-for-paper',
                'excerpt' => 'Undangan pengiriman naskah ilmiah untuk seminar nasional STEM berkarakter.',
                'location' => 'Aula Lab Terpadu USK, Banda Aceh',
                'cover_image' => 'assets/images/blog/blog_img_15.jpg',
                'starts_at' => now()->addWeeks(2)->setTime(8, 0),
                'ends_at' => now()->addWeeks(8)->setTime(16, 0),
            ],
        ];

        foreach ($events as $event) {
            Event::query()->updateOrCreate(
                ['slug' => Str::slug($event['title'])],
                array_merge($event, [
                    'body' => '<p>'.$event['excerpt'].'</p><p>Rincian agenda, narasumber, dan tata cara pendaftaran dapat diperbarui melalui panel admin.</p>',
                    'status' => 'published',
                ]),
            );
        }
    }

    private function seedPublications(): void
    {
        $publications = [
            [
                'title' => 'Integrasi Pendidikan Karakter dalam Pembelajaran STEM di Sekolah Menengah',
                'authors' => 'Rahmawati, S., Fauzan, M., Yusuf, A.',
                'type' => 'jurnal',
                'venue' => 'Jurnal Pendidikan IPA Indonesia',
                'year' => 2026,
                'doi' => '10.15294/jpii.v15i1.0001',
                'is_featured' => true,
            ],
            [
                'title' => 'Pengembangan Modul Ajar Berbasis Proyek untuk Penguatan Literasi Sains',
                'authors' => 'Fauzan, M., Nurhaliza, C.',
                'type' => 'jurnal',
                'venue' => 'Indonesian Journal of Science Education',
                'year' => 2025,
                'doi' => '10.20885/ijse.v9i2.0042',
            ],
            [
                'title' => 'Rancang Bangun Purwarupa Sensor Kualitas Air Berbasis IoT untuk Tambak Rakyat',
                'authors' => 'Yusuf, A., Maulana, R., Siregar, D.',
                'type' => 'prosiding',
                'venue' => 'Prosiding Seminar Nasional Teknik Elektro dan Informatika',
                'year' => 2025,
            ],
            [
                'title' => 'Model Pembelajaran STEM-C: Konsep, Praktik, dan Evaluasi',
                'authors' => 'Tim Pusat Riset STEM-C USK',
                'type' => 'buku',
                'venue' => 'Syiah Kuala University Press',
                'year' => 2024,
            ],
            [
                'title' => 'Perangkat Lunak Asesmen Karakter Terintegrasi Pembelajaran STEM',
                'authors' => 'Rahmawati, S., Maulana, R.',
                'type' => 'hki',
                'venue' => 'Direktorat Jenderal Kekayaan Intelektual',
                'year' => 2024,
            ],
            [
                'title' => 'Alat Peraga Konversi Energi Terbarukan Skala Laboratorium',
                'authors' => 'Siregar, D., Yusuf, A.',
                'type' => 'paten',
                'venue' => 'Paten Sederhana, Direktorat Jenderal Kekayaan Intelektual',
                'year' => 2023,
            ],
            [
                'title' => 'Analisis Kesiapan Guru dalam Menerapkan Pendekatan STEM di Wilayah Pesisir',
                'authors' => 'Nurhaliza, C., Rahmawati, S.',
                'type' => 'jurnal',
                'venue' => 'Jurnal Ilmu Pendidikan dan Pembelajaran',
                'year' => 2023,
            ],
        ];

        foreach ($publications as $publication) {
            Publication::query()->updateOrCreate(
                ['title' => $publication['title']],
                array_merge($publication, ['is_published' => true]),
            );
        }
    }

    private function seedMediaItems(): void
    {
        $albums = [
            'Seminar Nasional 2026' => ['blog_img_15.jpg', 'blog_img_16.jpg', 'blog_img_17.jpg', 'blog_img_18.jpg'],
            'Workshop Penulisan Artikel' => ['blog_img_19.jpg', 'blog_img_20.jpg', 'blog_img_21.jpg'],
            'Laboratorium & Fasilitas' => ['blog_img_22.jpg', 'blog_img_23.jpg', 'blog_img_24.jpg'],
        ];

        $order = 0;

        foreach ($albums as $album => $images) {
            foreach ($images as $index => $image) {
                MediaItem::query()->updateOrCreate(
                    ['title' => $album.' - Dokumentasi '.($index + 1)],
                    [
                        'type' => 'photo',
                        'album' => $album,
                        // Berkas contoh dari aset publik; unggahan admin
                        // nanti tersimpan di storage dan menimpa nilai ini.
                        'image' => 'assets/images/blog/'.$image,
                        'captured_at' => now()->subMonths($order + 1),
                        'sort_order' => $order++,
                        'is_published' => true,
                    ],
                );
            }
        }
    }

    private function seedDocuments(): void
    {
        // Semua contoh menunjuk ke satu PDF placeholder supaya tombol unduh
        // bisa diuji; admin menggantinya dengan berkas asli lewat panel.
        $contoh = 'assets/docs/contoh-dokumen.pdf';
        $ukuran = is_file(public_path($contoh)) ? filesize(public_path($contoh)) : null;

        $documents = [
            [
                'title' => 'SK Pendirian Pusat Riset STEM-C USK',
                'category' => 'regulasi',
                'description' => 'Surat keputusan rektor tentang pembentukan Pusat Riset STEM-C di lingkungan Universitas Syiah Kuala.',
            ],
            [
                'title' => 'Panduan Pengajuan Proposal Riset Internal',
                'category' => 'panduan',
                'description' => 'Ketentuan, tahapan, dan format penulisan proposal untuk skema hibah riset internal pusat riset.',
            ],
            [
                'title' => 'Formulir Pengajuan Kerja Sama Mitra',
                'category' => 'formulir',
                'description' => 'Formulir yang diisi calon mitra institusi sebelum penandatanganan nota kesepahaman.',
            ],
            [
                'title' => 'Laporan Tahunan Pusat Riset STEM-C 2025',
                'category' => 'laporan',
                'description' => 'Rekapitulasi kegiatan, luaran riset, dan capaian kerja sama sepanjang tahun 2025.',
            ],
            [
                'title' => 'Materi Workshop Penulisan Artikel Bereputasi',
                'category' => 'materi',
                'description' => 'Salindia pemaparan narasumber pada workshop penulisan artikel jurnal internasional bereputasi.',
            ],
        ];

        foreach ($documents as $index => $document) {
            Document::query()->updateOrCreate(
                ['title' => $document['title']],
                array_merge($document, [
                    'file' => $contoh,
                    'file_size' => $ukuran,
                    'published_on' => now()->subMonths($index + 1)->startOfMonth(),
                    'sort_order' => $index,
                    'is_published' => true,
                ]),
            );
        }
    }

    private function seedResearchers(): void
    {
        $groups = ResearchGroup::query()->pluck('id', 'code');

        foreach ([
            ['name' => 'Dr. Siti Rahmawati, M.Pd.', 'slug' => 'siti-rahmawati', 'expertise' => 'Pendidikan STEM dan karakter', 'research_group' => 'pedagogical'],
            ['name' => 'Muhammad Fauzan, M.Eng.', 'slug' => 'muhammad-fauzan', 'expertise' => 'Sensor dan Internet of Things', 'research_group' => 'rnd'],
            ['name' => 'Cut Nurhaliza, M.Pd.', 'slug' => 'cut-nurhaliza', 'expertise' => 'Literasi sains dan modul ajar', 'research_group' => 'pedagogical'],
        ] as $index => $item) {
            Researcher::query()->updateOrCreate(['slug' => $item['slug']], [
                'name' => $item['name'],
                'expertise' => $item['expertise'],
                'research_group' => $item['research_group'],
                'research_group_id' => $groups[$item['research_group']] ?? null,
                'degree' => null,
                'sort_order' => $index,
                'is_published' => true,
            ]);
        }
    }

    private function seedResearchProjects(): void
    {
        foreach ([
            ['title' => 'Model Pembelajaran STEM Berbasis Karakter', 'year' => 2026, 'leader' => 'Dr. Siti Rahmawati', 'status' => 'berjalan', 'funding' => 'Hibah USK'],
            ['title' => 'Sensor Kualitas Air untuk Tambak Rakyat', 'year' => 2025, 'leader' => 'Muhammad Fauzan, M.Eng.', 'status' => 'selesai', 'funding' => 'Kedaireka'],
        ] as $item) {
            ResearchProject::query()->updateOrCreate(['slug' => Str::slug($item['title'])], [...$item, 'abstract' => 'Riset unggulan Pusat Riset STEM-C USK.', 'is_published' => true]);
        }
    }

    private function seedInnovations(): void
    {
        foreach ([
            ['title' => 'Kit Pembelajaran Energi Terbarukan', 'type' => 'produk', 'year' => 2026],
            ['title' => 'Aplikasi Asesmen Karakter STEM', 'type' => 'hak_cipta', 'year' => 2025, 'registration_number' => 'EC00202500001'],
            ['title' => 'Sensor Kualitas Air Portabel', 'type' => 'paten', 'year' => 2025, 'registration_number' => 'P00202500001'],
        ] as $item) {
            Innovation::query()->updateOrCreate(['slug' => Str::slug($item['title'])], [...$item, 'description' => 'Luaran inovasi peneliti STEM-C USK.', 'is_published' => true]);
        }
    }

    private function seedPartnerships(): void
    {
        foreach ([
            ['title' => 'Kolaborasi Riset dan Pendidikan STEM', 'partner_name' => 'Universiti Kebangsaan Malaysia', 'type' => 'mou'],
            ['title' => 'Implementasi Program Sekolah Mitra', 'partner_name' => 'Dinas Pendidikan Aceh', 'type' => 'moa'],
        ] as $item) {
            Partnership::query()->updateOrCreate(['title' => $item['title']], [...$item, 'scope' => 'Riset bersama, pelatihan, dan pertukaran pengetahuan.', 'started_on' => now()->startOfYear(), 'is_active' => true, 'is_published' => true]);
        }
    }
}
